<?php

namespace App\Http\Controllers\Api;

use App\Models\Membro;
use App\Models\Meta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetaController
{
    public function index(Request $request, Membro $membro): JsonResponse
    {
        $grupo = $membro->grupo;

        if (!$grupo->usuarios()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $metas = $membro->metas()->whereNull('deleted_at')->get();

        return response()->json([
            'metas' => $metas->map(fn($m) => $this->formatMeta($m)),
        ]);
    }

    public function store(Request $request, Membro $membro): JsonResponse
    {
        $grupo = $membro->grupo;

        if (!$grupo->usuarios()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $data = $request->validate([
            'nome'             => 'required|string|max:255',
            'vezes_por_semana' => 'nullable|integer|min:1|max:7',
            'dia_inicio_semana'=> 'nullable|integer|min:1|max:7',
            'descricao'        => 'nullable|string',
            'imagem'           => 'nullable|string',
        ]);

        $meta = $membro->metas()->create(array_merge($data, [
            'grupo_id' => $grupo->id,
        ]));

        return response()->json(['meta' => $this->formatMeta($meta)], 201);
    }

    public function update(Request $request, Meta $meta): JsonResponse
    {
        $grupo = $meta->membro?->grupo ?? $meta->grupo;

        if (!$grupo?->usuarios()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $data = $request->validate([
            'nome'             => 'sometimes|required|string|max:255',
            'vezes_por_semana' => 'nullable|integer|min:1|max:7',
            'dia_inicio_semana'=> 'nullable|integer|min:1|max:7',
            'descricao'        => 'nullable|string',
            'imagem'           => 'nullable|string',
        ]);

        $meta->update($data);

        return response()->json(['meta' => $this->formatMeta($meta)]);
    }

    public function destroy(Request $request, Meta $meta): JsonResponse
    {
        $grupo = $meta->membro?->grupo ?? $meta->grupo;

        if (!$grupo?->usuarios()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $meta->delete();

        return response()->json(['message' => 'Excluído com sucesso']);
    }

    private function formatMeta(Meta $meta): array
    {
        return [
            'id'               => $meta->id,
            'nome'             => $meta->nome,
            'vezes_por_semana' => $meta->vezes_por_semana,
            'dia_inicio_semana'=> $meta->dia_inicio_semana,
            'descricao'        => $meta->descricao,
            'imagem'           => $meta->imagem,
            'membro_id'        => $meta->membro_id,
            'grupo_id'         => $meta->grupo_id,
        ];
    }
}
