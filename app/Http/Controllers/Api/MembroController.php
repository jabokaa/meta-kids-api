<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\LogroSemanal;
use App\Models\Membro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembroController extends Controller
{
    /**
     * Lista todos os membros de um grupo.
     * O usuário deve pertencer ao grupo para listar.
     */
    public function index(Request $request, Grupo $grupo): JsonResponse
    {
        $this->authorizeGrupo($request, $grupo);

        $membros = $grupo->membros()
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => $this->formatMembro($m));

        return response()->json(['membros' => $membros]);
    }

    /**
     * Cria um novo membro no grupo.
     */
    public function store(Request $request, Grupo $grupo): JsonResponse
    {
        $this->authorizeGrupo($request, $grupo);

        $validated = $request->validate([
            'nome'   => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'string', 'max:100'],
            'cor'    => ['nullable', 'string', 'max:20'],
        ]);

        $membro = $grupo->membros()->create($validated);

        return response()->json(['membro' => $this->formatMembro($membro)], 201);
    }

    /**
     * Atualiza um membro existente.
     */
    public function update(Request $request, Membro $membro): JsonResponse
    {
        $this->authorizeGrupo($request, $membro->grupo);

        $validated = $request->validate([
            'nome'   => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'string', 'max:100'],
            'cor'    => ['nullable', 'string', 'max:20'],
        ]);

        $membro->update($validated);

        return response()->json(['membro' => $this->formatMembro($membro)]);
    }

    /**
     * Remove um membro do grupo.
     */
    public function destroy(Request $request, Membro $membro): JsonResponse
    {
        $this->authorizeGrupo($request, $membro->grupo);

        $membro->delete();

        return response()->json(['message' => 'Membro removido.']);
    }

    /**
     * Lista os logros semanais (estrelas) de um membro, com filtro opcional por meta.
     * GET /membros/{membro}/logros-semanais?meta_id=X
     */
    public function logrosSemanais(Request $request, Membro $membro): JsonResponse
    {
        $this->authorizeGrupo($request, $membro->grupo);

        $query = LogroSemanal::where('membro_id', $membro->id)
            ->orderBy('semana_inicio', 'desc');

        if ($request->filled('meta_id')) {
            $query->where('meta_id', $request->integer('meta_id'));
        }

        $logros = $query->get()->map(fn($l) => [
            'id'            => $l->id,
            'membro_id'     => $l->membro_id,
            'meta_id'       => $l->meta_id,
            'semana_inicio' => $l->getRawOriginal('semana_inicio'),
        ]);

        return response()->json(['logros_semanais' => $logros]);
    }

    private function authorizeGrupo(Request $request, Grupo $grupo): void
    {
        if (! $request->user()->grupos()->where('grupos.id', $grupo->id)->exists()) {
            abort(403, 'Você não é membro deste grupo.');
        }
    }

    private function formatMembro(Membro $membro): array
    {
        return [
            'id'     => $membro->id,
            'nome'   => $membro->nome,
            'avatar' => $membro->avatar,
            'cor'    => $membro->cor,
        ];
    }
}
