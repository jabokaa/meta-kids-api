<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class GrupoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $grupos = $request->user()
            ->grupos()
            ->get()
            ->map(fn($g) => $this->formatGrupo($g));

        return response()->json(['grupos' => $grupos]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome'   => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:100'],
            'cor'    => ['nullable', 'string', 'max:20'],
        ]);

        $plainSenha = $this->generateSenha();

        $grupo = Grupo::create([
            'nome'   => $validated['nome'],
            'avatar' => $validated['avatar'] ?? null,
            'cor'    => $validated['cor'] ?? null,
            'codigo' => $this->generateCodigo(),
            'senha'  => $plainSenha,
        ]);

        $request->user()->grupos()->attach($grupo->id);

        return response()->json([
            'grupo' => $this->formatGrupo($grupo, $plainSenha),
        ], 201);
    }

    public function update(Request $request, Grupo $grupo): JsonResponse
    {
        if (! $request->user()->grupos()->where('grupos.id', $grupo->id)->exists()) {
            abort(403, 'Você não é membro deste grupo.');
        }

        $validated = $request->validate([
            'nome'   => ['sometimes', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:100'],
            'cor'    => ['nullable', 'string', 'max:20'],
        ]);

        $grupo->update($validated);

        return response()->json(['grupo' => $this->formatGrupo($grupo)]);
    }

    public function entrar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string'],
        ]);

        $grupo = Grupo::where('codigo', strtoupper(trim($validated['codigo'])))->first();

        if (! $grupo) {
            throw ValidationException::withMessages([
                'codigo' => 'Código de convite inválido.',
            ]);
        }

        $request->user()->grupos()->syncWithoutDetaching([$grupo->id]);

        return response()->json([
            'grupo' => $this->formatGrupo($grupo),
        ]);
    }

    private function formatGrupo(Grupo $grupo, ?string $plainSenha = null): array
    {
        return [
            'id'     => $grupo->id,
            'nome'   => $grupo->nome,
            'avatar' => $grupo->avatar,
            'cor'    => $grupo->cor,
            'codigo' => $grupo->codigo,
            'senha'  => $plainSenha,
        ];
    }

    private function generateCodigo(): string
    {
        $letras = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $nums   = '23456789';
        do {
            $codigo = $letras[random_int(0, strlen($letras) - 1)]
                . $letras[random_int(0, strlen($letras) - 1)]
                . $nums[random_int(0, strlen($nums) - 1)]
                . '-'
                . $letras[random_int(0, strlen($letras) - 1)]
                . $nums[random_int(0, strlen($nums) - 1)]
                . $letras[random_int(0, strlen($letras) - 1)];
        } while (Grupo::where('codigo', $codigo)->exists());

        return $codigo;
    }

    private function generateSenha(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $senha = '';
        for ($i = 0; $i < 5; $i++) {
            $senha .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $senha;
    }
}
