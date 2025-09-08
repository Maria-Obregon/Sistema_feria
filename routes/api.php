<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\InstitucionController;

Route::post('/register', [AuthController::class,'register']);
Route::post('/login',    [AuthController::class,'login']);

// Rutas 2FA
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/2fa/verify', [AuthController::class, 'verify2FA']);
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable']);
    Route::post('/2fa/confirm', [TwoFactorController::class, 'confirm']);
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);
    Route::get('/2fa/status', [TwoFactorController::class, 'status']);
    Route::post('/2fa/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn (\Illuminate\Http\Request $r) => $r->user()->load('rol'));
    Route::post('/logout', [AuthController::class,'logout']);

    // Rutas protegidas por rol
    // CRUD de usuarios (requiere permisos específicos)
    Route::apiResource('users', UserController::class);
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    
    // Admin
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Dashboard de Admin']);
        });
        
        // Gestión de instituciones
        Route::apiResource('instituciones', InstitucionController::class);
        Route::get('/circuitos', [InstitucionController::class, 'getCircuitos']);
        Route::post('/instituciones/{institucion}/toggle-activo', [InstitucionController::class, 'toggleActivo']);
    });

    // Coordinador Regional
    Route::middleware(['role:coordinador_regional'])->prefix('coordinador-regional')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Dashboard de Coordinador Regional']);
        });
    });

    // Coordinador de Circuito
    Route::middleware(['role:coordinador_circuito'])->prefix('coordinador-circuito')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Dashboard de Coordinador de Circuito']);
        });
    });

    // Comité Institucional
    Route::middleware(['role:comite_institucional'])->prefix('comite')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Dashboard de Comité Institucional']);
        });
    });

    // Juez
    Route::middleware(['role:juez'])->prefix('juez')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Dashboard de Juez']);
        });
    });

    // Estudiante
    Route::middleware(['role:estudiante'])->prefix('estudiante')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Dashboard de Estudiante']);
        });
    });
});
