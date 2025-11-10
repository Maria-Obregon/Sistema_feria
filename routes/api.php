<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\PublicRegistroController;
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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoriaController;

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

    // -----------------------------
    // Perfil y sesión
    // -----------------------------
    Route::get ('/me',     [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Categorías elegibles por modalidad (para poblar el select del front)
Route::get(
    '/categorias/por-modalidad/{modalidad}',
    [CategoriaController::class, 'porModalidad']
)->whereNumber('modalidad'); // opcional: ->middleware('permission:proyectos.ver')

    // -----------------------------
    // Descargas / Documentos
    // -----------------------------
    Route::get('/docs/pronafecyt', [DocsController::class, 'pronafecyt']); // DOCX

    // -----------------------------
    // Catálogos comunes para selects
    // -----------------------------
    Route::get('/catalogo/regionales', [InstitucionController::class, 'catalogoRegionales']);
    Route::get('/catalogo/circuitos',  [InstitucionController::class, 'catalogoCircuitos']);
    Route::get('/circuitos',           [InstitucionController::class, 'getCircuitos']);
    Route::get('/instituciones/catalogos', [InstitucionController::class, 'getCatalogos']);

    /*
    |--------------------------------------------------------------------------
    | INSTITUCIONES
    |--------------------------------------------------------------------------
    */
    Route::get   ('/instituciones',                   [InstitucionController::class, 'index'])
        ->middleware('permission:instituciones.ver');

    Route::post  ('/instituciones',                   [InstitucionController::class, 'store'])
        ->middleware('permission:instituciones.crear');

    Route::get   ('/instituciones/{institucion}',     [InstitucionController::class, 'show'])
        ->middleware('permission:instituciones.ver')
        ->whereNumber('institucion');

    Route::put   ('/instituciones/{institucion}',     [InstitucionController::class, 'update'])
        ->middleware('permission:instituciones.editar')
        ->whereNumber('institucion');

    Route::delete('/instituciones/{institucion}',     [InstitucionController::class, 'destroy'])
        ->middleware('permission:instituciones.eliminar')
        ->whereNumber('institucion');

    Route::patch ('/instituciones/{institucion}/toggle', [InstitucionController::class, 'toggleActivo'])
        ->middleware('permission:instituciones.editar')
        ->whereNumber('institucion');

    /*
    |--------------------------------------------------------------------------
    | ESTUDIANTES
    |--------------------------------------------------------------------------
    */
    Route::get ('/estudiantes', [EstudianteController::class, 'index'])
        ->middleware('permission:estudiantes.ver');

    Route::post('/estudiantes', [EstudianteController::class, 'store'])
        ->middleware('permission:estudiantes.crear');

    // Credencial (descarga)
    Route::get('/estudiantes/{estudiante}/credencial', [EstudianteController::class, 'credencial'])
        ->middleware('permission:estudiantes.ver')
        ->whereNumber('estudiante');

    // Ligar / desligar proyecto
    Route::post  ('/estudiantes/{estudiante}/vincular-proyecto', [EstudianteController::class, 'vincularProyecto'])
        ->middleware('permission:proyectos.editar')
        ->whereNumber('estudiante');

    Route::delete('/estudiantes/{estudiante}/desvincular-proyecto/{proyecto}', [EstudianteController::class, 'desvincularProyecto'])
        ->middleware('permission:proyectos.editar')
        ->whereNumber('estudiante')
        ->whereNumber('proyecto');

    /*
    |--------------------------------------------------------------------------
    | USUARIOS (solo admin)
    |--------------------------------------------------------------------------
    */
    Route::apiResource('usuarios', UserController::class)
        ->middleware('role:admin');

    /*
    |--------------------------------------------------------------------------
    | PROYECTOS
    |--------------------------------------------------------------------------
    | IMPORTANTE: rutas “planas” primero, luego el apiResource, y restringir {proyecto}
    */
    Route::get('/proyectos/form-data', [ProyectoController::class, 'formData'])
        ->name('proyectos.formData');

    Route::get('/proyectos/debug-schema', [ProyectoController::class, 'debugSchema'])
        ->name('proyectos.debug');

    Route::apiResource('proyectos', ProyectoController::class)
        ->whereNumber('proyecto');

    /*
    |--------------------------------------------------------------------------
    | CALIFICACIONES
    |--------------------------------------------------------------------------
    */
    Route::get ('/calificaciones',            [CalificacionController::class, 'index'])
        ->middleware('permission:calificaciones.ver');

    Route::post('/calificaciones',            [CalificacionController::class, 'store'])
        ->middleware('permission:calificaciones.crear');

    Route::post('/calificaciones/consolidar', [CalificacionController::class, 'consolidar'])
        ->middleware('permission:calificaciones.consolidar');

    /*
    |--------------------------------------------------------------------------
    | FERIAS
    |--------------------------------------------------------------------------
    */
    Route::get   ('/ferias',         [FeriaController::class, 'index'])->middleware('permission:ferias.ver');
    Route::post  ('/ferias',         [FeriaController::class, 'store'])->middleware('permission:ferias.crear');
    Route::put   ('/ferias/{feria}', [FeriaController::class, 'update'])->middleware('permission:ferias.editar')->whereNumber('feria');
    Route::delete('/ferias/{feria}', [FeriaController::class, 'destroy'])->middleware('permission:ferias.eliminar')->whereNumber('feria');

    /*
    |--------------------------------------------------------------------------
    | JUECES
    |--------------------------------------------------------------------------
    */
    Route::get   ('/jueces',        [JuezController::class, 'index'])->middleware('permission:jueces.ver');
    Route::post  ('/jueces',        [JuezController::class, 'store'])->middleware('permission:jueces.crear');
    Route::put   ('/jueces/{juez}', [JuezController::class, 'update'])->middleware('permission:jueces.editar')->whereNumber('juez');
    Route::delete('/jueces/{juez}', [JuezController::class, 'destroy'])->middleware('permission:jueces.eliminar')->whereNumber('juez');

    // --- ASIGNACIONES DE JUECES A PROYECTO ---
    Route::get   ('/proyectos/{proyecto}/asignaciones',    [AsignacionJuezController::class, 'listByProyecto'])
        ->middleware('permission:proyectos.ver')
        ->whereNumber('proyecto');

    Route::post  ('/proyectos/{proyecto}/asignar-jueces',  [AsignacionJuezController::class, 'assign'])
        ->middleware('permission:jueces.asignar')
        ->whereNumber('proyecto');

    Route::delete('/asignaciones-jueces/{id}',             [AsignacionJuezController::class, 'unassign'])
        ->middleware('permission:jueces.asignar')
        ->whereNumber('id');

    /*
    |--------------------------------------------------------------------------
    | ADMIN (dashboard, roles, catálogos)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Dashboard / util
        Route::get('/stats', [AdminController::class, 'stats']);

        // Roles
        Route::get('/roles', [UserController::class, 'rolesDisponibles']);

        // Usuarios extras bajo /admin
        Route::put ('/usuarios/{usuario}/password', [UserController::class, 'resetPassword'])->whereNumber('usuario');
        Route::post('/usuarios/{usuario}/roles',    [UserController::class, 'actualizarRoles'])->whereNumber('usuario');
        Route::post('/usuarios',                    [UserController::class, 'store']);

         // --- Regionales ---
    Route::get   ('/regionales',               [AdminController::class, 'listRegionales']);
    Route::post  ('/regionales',               [AdminController::class, 'createRegional']);
    Route::put   ('/regionales/{regional}',    [AdminController::class, 'updateRegional'])->whereNumber('regional');
    Route::delete('/regionales/{regional}',    [AdminController::class, 'destroyRegional'])->whereNumber('regional');

    // --- Circuitos ---
    Route::get   ('/circuitos',                [AdminController::class, 'listCircuitos']);
    Route::post  ('/circuitos',                [AdminController::class, 'createCircuito']);
    Route::put   ('/circuitos/{circuito}',     [AdminController::class, 'updateCircuito'])->whereNumber('circuito');
    Route::delete('/circuitos/{circuito}',     [AdminController::class, 'destroyCircuito'])->whereNumber('circuito');

        // Catálogos: Modalidades
        Route::get   ('/modalidades',              [AdminController::class, 'listModalidades']);
        Route::post  ('/modalidades',              [AdminController::class, 'createModalidad']);
        Route::put   ('/modalidades/{modalidad}',  [AdminController::class, 'updateModalidad'])->whereNumber('modalidad');
        Route::delete('/modalidades/{modalidad}',  [AdminController::class, 'destroyModalidad'])->whereNumber('modalidad');

        // Catálogos: Áreas
        Route::get   ('/areas',                    [AdminController::class, 'listAreas']);
        Route::post  ('/areas',                    [AdminController::class, 'createArea']);
        Route::put   ('/areas/{area}',             [AdminController::class, 'updateArea'])->whereNumber('area');
        Route::delete('/areas/{area}',             [AdminController::class, 'destroyArea'])->whereNumber('area');

        // Catálogos: Categorías
        Route::get   ('/categorias',               [AdminController::class, 'listCategorias']);
        Route::post  ('/categorias',               [AdminController::class, 'createCategoria']);
        Route::put   ('/categorias/{categoria}',   [AdminController::class, 'updateCategoria'])->whereNumber('categoria');
        Route::delete('/categorias/{categoria}',   [AdminController::class, 'destroyCategoria'])->whereNumber('categoria');

        // Catálogos: Tipos de Institución
        Route::get   ('/tipos-institucion',        [AdminController::class, 'listTiposInstitucion']);
        Route::post  ('/tipos-institucion',        [AdminController::class, 'createTipoInstitucion']);
        Route::put   ('/tipos-institucion/{tipo}', [AdminController::class, 'updateTipoInstitucion'])->whereNumber('tipo');
        Route::delete('/tipos-institucion/{tipo}', [AdminController::class, 'destroyTipoInstitucion'])->whereNumber('tipo');

        // Catálogos: Niveles
        Route::get   ('/niveles',                  [AdminController::class, 'listNiveles']);
        Route::post  ('/niveles',                  [AdminController::class, 'createNivel']);
        Route::put   ('/niveles/{nivel}',          [AdminController::class, 'updateNivel'])->whereNumber('nivel');
        Route::delete('/niveles/{nivel}',          [AdminController::class, 'destroyNivel'])->whereNumber('nivel');
    });
});
