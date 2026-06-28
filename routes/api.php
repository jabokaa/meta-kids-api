<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GrupoController;
use App\Http\Controllers\Api\MembroController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\RegistroController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('grupos')->group(function () {
        Route::get('/',          [GrupoController::class, 'index']);
        Route::post('/',         [GrupoController::class, 'store']);
        Route::put('/{grupo}',   [GrupoController::class, 'update']);
        Route::post('/entrar',   [GrupoController::class, 'entrar']);

        // Membros de um grupo
        Route::get('/{grupo}/membros',  [MembroController::class, 'index']);
        Route::post('/{grupo}/membros', [MembroController::class, 'store']);
    });

    // Operações em membro individual
    Route::prefix('membros')->group(function () {
        Route::put('/{membro}',    [MembroController::class, 'update']);
        Route::delete('/{membro}', [MembroController::class, 'destroy']);

        // Metas de um membro
        Route::get('/{membro}/metas',  [MetaController::class, 'index']);
        Route::post('/{membro}/metas', [MetaController::class, 'store']);
    });

    // Operações em meta individual
    Route::prefix('metas')->group(function () {
        Route::put('/{meta}',    [MetaController::class, 'update']);
        Route::delete('/{meta}', [MetaController::class, 'destroy']);

        // Registros de uma meta
        Route::get('/{meta}/registros',  [RegistroController::class, 'index']);
        Route::post('/{meta}/registros', [RegistroController::class, 'store']);
    });

    // Operações em registro individual
    Route::prefix('registros')->group(function () {
        Route::delete('/{registro}', [RegistroController::class, 'destroy']);
    });
});
