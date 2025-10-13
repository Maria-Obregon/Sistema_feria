<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\JudgeAssignmentController;
use App\Http\Controllers\RubricaController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']); // opcional, si permites registro

/*
|--------------------------------------------------------------------------
| Rutas autenticadas (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Perfil y sesión
    Route::get('/me',     [AuthController::class, 'me']);
    Route::post('/logout',[AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | INSTITUCIONES (por permisos)
    |--------------------------------------------------------------------------
    | Los permisos se crearon en tu seeder:
    |   instituciones.ver / crear / editar / eliminar
    */
    Route::get   ('/instituciones',                   [InstitucionController::class, 'index'])
        ->middleware('permission:instituciones.ver');

    Route::post  ('/instituciones',                   [InstitucionController::class, 'store'])
        ->middleware('permission:instituciones.crear');

    Route::get   ('/instituciones/{institucion}',     [InstitucionController::class, 'show'])
        ->middleware('permission:instituciones.ver');

    Route::put   ('/instituciones/{institucion}',     [InstitucionController::class, 'update'])
        ->middleware('permission:instituciones.editar');

    Route::delete('/instituciones/{institucion}',     [InstitucionController::class, 'destroy'])
        ->middleware('permission:instituciones.eliminar');

    Route::patch ('/instituciones/{institucion}/toggle', [InstitucionController::class, 'toggleActivo'])
        ->middleware('permission:instituciones.editar');

    // Circuitos para selects (deja libre a cualquier autenticado; si quieres, protégelo con instituciones.ver)
    Route::get('/circuitos', [InstitucionController::class, 'getCircuitos']);

    /*
    |--------------------------------------------------------------------------
    | USUARIOS (solo admin)
    |--------------------------------------------------------------------------
    */
    Route::apiResource('usuarios', UserController::class)
        ->middleware('role:admin');

    /*
    |--------------------------------------------------------------------------
    | PROYECTOS (por permisos)
    |--------------------------------------------------------------------------
    | Permisos del seeder:
    |   proyectos.ver / crear / editar / eliminar / calificar (si aplica)
    */
    Route::get   ('/proyectos',               [ProyectoController::class, 'index'])
        ->middleware('permission:proyectos.ver');

    Route::post  ('/proyectos',               [ProyectoController::class, 'store'])
        ->middleware('permission:proyectos.crear');

    Route::get   ('/proyectos/{proyecto}',    [ProyectoController::class, 'show'])
        ->middleware('permission:proyectos.ver');

    Route::put   ('/proyectos/{proyecto}',    [ProyectoController::class, 'update'])
        ->middleware('permission:proyectos.editar');

    Route::delete('/proyectos/{proyecto}',    [ProyectoController::class, 'destroy'])
        ->middleware('permission:proyectos.eliminar');

    /*
    |--------------------------------------------------------------------------
    | CALIFICACIONES (por permisos)
    |--------------------------------------------------------------------------
    | Permisos del seeder:
    |   calificaciones.ver / crear / consolidar
    */
    Route::get ('/calificaciones',           [CalificacionController::class, 'index'])
        ->middleware('permission:calificaciones.ver');

    Route::post('/calificaciones',           [CalificacionController::class, 'store'])
        ->middleware('permission:calificaciones.crear');

    Route::post('/calificaciones/consolidar',[CalificacionController::class, 'consolidar'])
        ->middleware('permission:calificaciones.consolidar');

    /*
    |--------------------------------------------------------------------------
    | RÚBRICAS Y CRITERIOS
    |--------------------------------------------------------------------------
    | Acceso a criterios de evaluación para construir formularios
    */
    Route::get('/rubricas/{tipo_eval}/criterios', [RubricaController::class, 'criteriosPorTipoEval']);

    /*
    |--------------------------------------------------------------------------
    | JUECES - API de solo lectura
    |--------------------------------------------------------------------------
    | Rutas protegidas para jueces autenticados
    | Permisos: proyectos.ver, calificaciones.ver
    */
    Route::middleware(['role:juez'])->group(function () {
        Route::get('/juez/asignaciones', [JudgeAssignmentController::class, 'index'])
            ->middleware('permission:proyectos.ver');
        
        Route::get('/juez/proyectos/{id}', [JudgeAssignmentController::class, 'showProject'])
            ->middleware('permission:proyectos.ver');
    });
});
