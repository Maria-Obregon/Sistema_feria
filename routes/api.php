<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\AdminController;

Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {

    // Perfil
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // -----------------------------
    // Utilidades institucionales (rutas fijas primero)
    // -----------------------------
    Route::get('/instituciones/catalogos', [InstitucionController::class, 'getCatalogos']);
    Route::get('/circuitos',               [InstitucionController::class, 'getCircuitos']);

    // -----------------------------
    // Instituciones (con permisos finos)
    // -----------------------------
    Route::get   ('/instituciones',               [InstitucionController::class, 'index'])->middleware('permission:instituciones.ver');
    Route::post  ('/instituciones',               [InstitucionController::class, 'store'])->middleware('permission:instituciones.crear');
    Route::get   ('/instituciones/{institucion}', [InstitucionController::class, 'show'])->middleware('permission:instituciones.ver');
    Route::put   ('/instituciones/{institucion}', [InstitucionController::class, 'update'])->middleware('permission:instituciones.editar');
    Route::delete('/instituciones/{institucion}', [InstitucionController::class, 'destroy'])->middleware('permission:instituciones.eliminar');

    // -----------------------------
    // Usuarios (CRUD principal)
    // -----------------------------
    Route::apiResource('usuarios', UserController::class)->middleware('role:admin');

    // -----------------------------
    // Admin
    // -----------------------------
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Dashboard / util
        Route::get('/stats', [AdminController::class, 'stats']);

        // Roles
        Route::get('/roles', [UserController::class, 'rolesDisponibles']);

        // Usuarios extras
        Route::put ('/usuarios/{usuario}/password', [UserController::class, 'resetPassword']);
        Route::post('/usuarios/{usuario}/roles',    [UserController::class, 'actualizarRoles']);
        Route::post('/usuarios',                    [UserController::class, 'store']);

        // Catálogos
        Route::get   ('/modalidades',              [AdminController::class, 'listModalidades']);
        Route::post  ('/modalidades',              [AdminController::class, 'createModalidad']);
        Route::put   ('/modalidades/{modalidad}',  [AdminController::class, 'updateModalidad']);
        Route::delete('/modalidades/{modalidad}',  [AdminController::class, 'destroyModalidad']);

        Route::get   ('/areas',                    [AdminController::class, 'listAreas']);
        Route::post  ('/areas',                    [AdminController::class, 'createArea']);
        Route::put   ('/areas/{area}',             [AdminController::class, 'updateArea']);
        Route::delete('/areas/{area}',             [AdminController::class, 'destroyArea']);

        Route::get   ('/categorias',               [AdminController::class, 'listCategorias']);
        Route::post  ('/categorias',               [AdminController::class, 'createCategoria']);
        Route::put   ('/categorias/{categoria}',   [AdminController::class, 'updateCategoria']);
        Route::delete('/categorias/{categoria}',   [AdminController::class, 'destroyCategoria']);

        Route::get   ('/tipos-institucion',        [AdminController::class, 'listTiposInstitucion']);
        Route::post  ('/tipos-institucion',        [AdminController::class, 'createTipoInstitucion']);
        Route::put   ('/tipos-institucion/{tipo}', [AdminController::class, 'updateTipoInstitucion']);
        Route::delete('/tipos-institucion/{tipo}', [AdminController::class, 'destroyTipoInstitucion']);

        Route::get   ('/niveles',                  [AdminController::class, 'listNiveles']);
        Route::post  ('/niveles',                  [AdminController::class, 'createNivel']);
        Route::put   ('/niveles/{nivel}',          [AdminController::class, 'updateNivel']);
        Route::delete('/niveles/{nivel}',          [AdminController::class, 'destroyNivel']);
    });
});
