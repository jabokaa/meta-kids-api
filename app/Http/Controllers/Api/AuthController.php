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

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Usuário cadastrado com sucesso.',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
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

        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}