<?php

namespace App\Http\Controllers\Api;

use App\Models\LogroSemanal;
use App\Models\Meta;
use App\Models\Registro;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistroController
{
    private function grupoForMeta(Meta $meta)
    {
        return $meta->membro?->grupo ?? $meta->grupo;
    }

    public function index(Request $request, Meta $meta): JsonResponse
    {
        $grupo = $this->grupoForMeta($meta);

        if (!$grupo?->usuarios()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $registros = $meta->registros()->get();

        return response()->json([
            'registros' => $registros->map(fn($r) => $this->formatRegistro($r)),
        ]);
    }

    public function store(Request $request, Meta $meta): JsonResponse
    {
        $grupo = $this->grupoForMeta($meta);

        if (!$grupo?->usuarios()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $data = $request->validate([
            'data'     => 'required|date_format:Y-m-d',
            'hora'     => 'required|date_format:H:i:s',
            'emoticon' => 'nullable|string|max:50',
        ]);

        $registro = $meta->registros()->create($data);

        $logro = $this->tryGrantLogro($meta, $data['data']);

        $response = ['registro' => $this->formatRegistro($registro)];
        if ($logro) {
            $response['logro_semanal'] = $this->formatLogro($logro);
        }

        return response()->json($response, 201);
    }

    public function destroy(Request $request, Registro $registro): JsonResponse
    {
        $grupo = $this->grupoForMeta($registro->meta);

        if (!$grupo?->usuarios()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $registro->delete();

        return response()->json(['message' => 'Excluído com sucesso']);
    }

    /**
     * Verifica se o registro recém-criado completou a meta da semana e, caso sim,
     * cria (ou retorna o existente) um LogroSemanal. Usa a mesma lógica de
     * dia_inicio_semana do app Flutter (1=segunda, 7=domingo, ISO weekday).
     */
    private function tryGrantLogro(Meta $meta, string $registroData): ?LogroSemanal
    {
        $diaInicio = $meta->dia_inicio_semana ?? 1;
        $day = Carbon::parse($registroData);
        $diff = ($day->dayOfWeekIso - $diaInicio + 7) % 7;
        $weekStart = $day->copy()->subDays($diff)->startOfDay();
        $weekEnd   = $weekStart->copy()->addDays(7);

        $count = $meta->registros()
            ->where('data', '>=', $weekStart->toDateString())
            ->where('data', '<',  $weekEnd->toDateString())
            ->count();

        if ($count < $meta->vezes_por_semana) {
            return null;
        }

        $membroId = $meta->membro_id;
        if (!$membroId) {
            return null;
        }

        [$logro, $created] = [
            LogroSemanal::firstOrCreate(
                ['meta_id' => $meta->id, 'semana_inicio' => $weekStart->toDateString()],
                ['membro_id' => $membroId]
            ),
            false,
        ];

        // Só retorna o logro na resposta quando foi criado agora (semana acabou de ser completada)
        $wasJustCompleted = $count === $meta->vezes_por_semana;

        return $wasJustCompleted ? $logro : null;
    }

    private function formatRegistro(Registro $registro): array
    {
        return [
            'id'       => $registro->id,
            'data'     => $registro->getRawOriginal('data'),
            'hora'     => $registro->getRawOriginal('hora'),
            'emoticon' => $registro->emoticon,
            'meta_id'  => $registro->meta_id,
        ];
    }

    private function formatLogro(LogroSemanal $logro): array
    {
        return [
            'id'           => $logro->id,
            'membro_id'    => $logro->membro_id,
            'meta_id'      => $logro->meta_id,
            'semana_inicio' => $logro->getRawOriginal('semana_inicio'),
        ];
    }
}
