<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255', 'unique:users,login'],
            'codigo' => ['required', 'string', 'max:255', 'unique:users,codigo'],
            'senha' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'login' => $validated['login'],
            'codigo' => $validated['codigo'],
            'senha' => Hash::make($validated['senha']),
        ]);

        return $this->authenticatedResponse(
            user: $user,
            message: 'Usuário cadastrado com sucesso.',
            status: 201
        );
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['nullable', 'string'],
            'codigo' => ['nullable', 'string'],
            'senha' => ['required', 'string'],
        ]);

        $identifier = $validated['login'] ?? $validated['codigo'] ?? null;

        if (! $identifier) {
            throw ValidationException::withMessages([
                'login' => 'Informe login ou codigo para autenticar.',
            ]);
        }

        $user = User::query()
            ->when(
                $request->filled('login'),
                fn ($query) => $query->where('login', $validated['login'])
            )
            ->when(
                ! $request->filled('login') && $request->filled('codigo'),
                fn ($query) => $query->where('codigo', $validated['codigo'])
            )
            ->first();

        if (! $user || ! Hash::check($validated['senha'], $user->senha)) {
            throw ValidationException::withMessages([
                'login' => 'Credenciais inválidas.',
            ]);
        }

        return $this->authenticatedResponse(
            user: $user,
            message: 'Login realizado com sucesso.'
        );
    }

    private function authenticatedResponse(User $user, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'user' => $user,
            'token' => $user->createToken('api', ['*'], null)->plainTextToken,
            'token_type' => 'Bearer',
        ], $status);
    }
}