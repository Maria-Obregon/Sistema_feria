<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\PublicRegistroController; // <-- correcto
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\FeriaController;
use App\Http\Controllers\JuezController;
use App\Http\Controllers\AsignacionJuezController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']); // si lo permites

/*
|--------------------------------------------------------------------------
| Rutas autenticadas (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Perfil y sesión
    Route::get ('/me',      [AuthController::class, 'me']);
    Route::post('/logout',  [AuthController::class, 'logout']);

    // DESCARGAS
    Route::get('/docs/pronafecyt', [DocsController::class, 'pronafecyt']); // DOCX
    Route::get('/estudiantes/{estudiante}/credencial', [EstudianteController::class, 'credencial'])
        ->middleware('permission:estudiantes.ver');

    /*
    |----------------------------------------------------------------------
    | INSTITUCIONES
    |----------------------------------------------------------------------
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

    // Catálogos para selects
    Route::get('/catalogo/regionales', [InstitucionController::class, 'catalogoRegionales']);
    Route::get('/catalogo/circuitos',  [InstitucionController::class, 'catalogoCircuitos']);

    // (Compat) listado de circuitos
    Route::get('/circuitos', [InstitucionController::class, 'getCircuitos']);

    /*
    |----------------------------------------------------------------------
    | ESTUDIANTES
    |----------------------------------------------------------------------
    */
    Route::get ('/estudiantes', [EstudianteController::class, 'index'])
        ->middleware('permission:estudiantes.ver');

    Route::post('/estudiantes', [EstudianteController::class, 'store'])
        ->middleware('permission:estudiantes.crear');

    // Ligar / desligar proyecto
    Route::post  ('/estudiantes/{estudiante}/vincular-proyecto', [EstudianteController::class, 'vincularProyecto'])
        ->middleware('permission:proyectos.editar');

    Route::delete('/estudiantes/{estudiante}/desvincular-proyecto/{proyecto}', [EstudianteController::class, 'desvincularProyecto'])
        ->middleware('permission:proyectos.editar');

    /*
    |----------------------------------------------------------------------
    | USUARIOS (solo admin)
    |----------------------------------------------------------------------
    */
    Route::apiResource('usuarios', UserController::class)
        ->middleware('role:admin');

    /*
    |----------------------------------------------------------------------
    | PROYECTOS
    |----------------------------------------------------------------------
    */
    Route::get('/proyectos',  [ProyectoController::class, 'index'])->middleware('permission:proyectos.ver');
Route::post('/proyectos', [ProyectoController::class, 'store'])->middleware('permission:proyectos.crear');

Route::get('/proyectos/form-data', [ProyectoController::class, 'formData'])
    ->middleware('permission:proyectos.crear');

Route::get('/proyectos/{proyecto}', [ProyectoController::class, 'show'])->middleware('permission:proyectos.ver');
Route::put('/proyectos/{proyecto}', [ProyectoController::class, 'update'])->middleware('permission:proyectos.editar');
Route::delete('/proyectos/{proyecto}', [ProyectoController::class, 'destroy'])->middleware('permission:proyectos.eliminar');

    /*
    |----------------------------------------------------------------------
    | CALIFICACIONES
    |----------------------------------------------------------------------
    *
    Route::get ('/calificaciones',            [CalificacionController::class, 'index'])
        ->middleware('permission:calificaciones.ver');

    Route::post('/calificaciones',            [CalificacionController::class, 'store'])
        ->middleware('permission:calificaciones.crear');

    Route::post('/calificaciones/consolidar', [CalificacionController::class, 'consolidar'])
        ->middleware('permission:calificaciones.consolidar');

    /*
    |----------------------------------------------------------------------
    | FERIAS
    |----------------------------------------------------------------------
    */
    Route::get   ('/ferias',         [FeriaController::class, 'index'])->middleware('permission:ferias.ver');
    Route::post  ('/ferias',         [FeriaController::class, 'store'])->middleware('permission:ferias.crear');
    Route::put   ('/ferias/{feria}', [FeriaController::class, 'update'])->middleware('permission:ferias.editar');
    Route::delete('/ferias/{feria}', [FeriaController::class, 'destroy'])->middleware('permission:ferias.eliminar');

    // ===== Jueces =====
    Route::get   ('/jueces',        [JuezController::class, 'index'])->middleware('permission:jueces.ver');
    Route::post  ('/jueces',        [JuezController::class, 'store'])->middleware('permission:jueces.crear');
    Route::put   ('/jueces/{juez}', [JuezController::class, 'update'])->middleware('permission:jueces.editar');
    Route::delete('/jueces/{juez}', [JuezController::class, 'destroy'])->middleware('permission:jueces.eliminar');

    // ===== Asignación de jueces a proyectos =====
    Route::post('/proyectos/{proyecto}/asignar-jueces', [AsignacionJuezController::class, 'assign'])
        ->middleware('permission:jueces.asignar');

    Route::get('/proyectos/{proyecto}/asignaciones-jueces', [AsignacionJuezController::class, 'listByProyecto'])
        ->middleware('permission:jueces.asignar');

    Route::delete('/asignaciones-jueces/{id}', [AsignacionJuezController::class, 'unassign'])
        ->middleware('permission:jueces.asignar');

});
