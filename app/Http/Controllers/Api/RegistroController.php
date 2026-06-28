<?php

namespace App\Http\Controllers\Api;

use App\Models\Meta;
use App\Models\Registro;
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

        return response()->json(['registro' => $this->formatRegistro($registro)], 201);
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
}
