<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Estudiante;
use App\Models\Institucion;
use App\Models\Area;
use App\Models\Categoria;
use App\Models\Feria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ProyectoController extends Controller
{
    /**
     * GET /api/proyectos?institucion_id=...
     * Lista proyectos (filtra por institución del usuario si no mandan ID).
     */
    public function index(Request $request)
    {
        $institucionId = $request->get('institucion_id') ?? optional($request->user())->institucion_id;

        // 2. LÓGICA CORREGIDA Y OPTIMIZADA
        if (! $request->user()->hasAnyRole(['admin', 'comite_institucional'])) {
             $institucionId = $institucionId ?? $request->user()->institucion_id;
        }

        $q = Proyecto::with([
                'area', 
                'categoria', 
                'modalidad',
                'institucion:id,nombre',
                'estudiantes:id,cedula,nombre,apellidos,nivel,seccion',
                'asignacionesJuez.juez:id,nombre' 
            ])
            ->when($institucionId, fn($qq) => $qq->where('institucion_id', $institucionId))
            ->when($request->filled('buscar'), function ($qq) use ($request) {
                $b = $request->buscar;
                $qq->where(function ($w) use ($b) {
                    $w->where('titulo', 'like', "%{$b}%")
                      ->orWhere('resumen', 'like', "%{$b}%");
                });
            })
            ->when($request->filled('exclude_juez_id'), function($qq) use ($request) {
                 $juezId = $request->integer('exclude_juez_id');
                 $qq->whereDoesntHave('asignacionesJuez', fn($q) => $q->where('juez_id', $juezId));
            })
            ->orderByDesc('id');

        return $q->paginate($request->integer('per_page', 10));
    }
    /**
     * GET /api/proyectos/{proyecto}
     * Devuelve un proyecto con sus relaciones.
     */
    public function show(Proyecto $proyecto)
    {
        $proyecto->load(['area','categoria','feria','estudiantes:id,cedula,nombre,apellidos']);
        return response()->json($proyecto);
    }

    /**
     * POST /api/proyectos
     * Crea proyecto y liga estudiantes (opcional).
     */
    public function store(Request $request)
    {
        // 1. FORZAR INSTITUCIÓN DEL USUARIO (Si no es admin)
        if (!$request->user()->hasRole('admin')) {
             $request->merge(['institucion_id' => $request->user()->institucion_id]);
        }

        // 2. Validación (ahora institucion_id es required porque ya lo inyectamos)
        $data = $request->validate([
            'titulo'         => 'required|string|max:255',
            'resumen'        => 'nullable|string',
            'area_id'        => ['required', Rule::exists('areas', 'id')],
            'modalidad_id'   => ['required', Rule::exists('modalidades', 'id')],
            'categoria_id'   => ['required', Rule::exists('categorias', 'id')],
            'feria_id'       => ['required', Rule::exists('ferias', 'id')],
            'institucion_id' => ['required', Rule::exists('instituciones', 'id')],
            'aula'           => 'nullable|string|max:50',
        ]);

        // 2. Asignar Institución
        $data['institucion_id'] = $data['institucion_id'] ?? optional($request->user())->institucion_id;
        
        if (!$data['institucion_id']) {
            return response()->json(['message' => 'La institución es obligatoria.'], 422);
        }

        DB::beginTransaction();
        try {
            $attrs = [
                'titulo'         => $data['titulo'],
                'resumen'        => $data['resumen'] ?? null,
                'area_id'        => $data['area_id'],
                'modalidad_id'   => $data['modalidad_id'],
                'categoria_id'   => $data['categoria_id'],
                'institucion_id' => $data['institucion_id'],
                'feria_id'       => $data['feria_id'],
                'aula'           => $data['aula'] ?? null,
                'estado'         => 'inscrito',
                // 'etapa_id' => 1, // Descomentar si usas etapas y tienes un valor default
            ];

            $proyecto = Proyecto::create($attrs);

            if (!empty($data['estudiantes'])) {
                $proyecto->estudiantes()->sync($data['estudiantes']);
            }

            DB::commit();

            return response()->json($proyecto->load(['area','categoria']), 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error creando proyecto: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al crear el proyecto'], 500);
        }
    }


    /**
     * GET /api/proyectos/form-data?institucion_id=...
     * Devuelve catálogos y estudiantes para poblar el formulario.
     */
    public function formData(Request $request)
    {
        $institucionId = $request->get('institucion_id') ?? optional($request->user())->institucion_id;

        // 1. Catálogos básicos
        $areas       = Area::orderBy('nombre')->get(['id', 'nombre']);
        $categorias  = Categoria::orderBy('nombre')->get(['id', 'nombre', 'nivel']);
        $modalidades = \App\Models\Modalidad::orderBy('nombre')->get(['id', 'nombre']);
        $instituciones = \App\Models\Institucion::select('id', 'nombre')->orderBy('nombre')->get();

        // 2. Ferias (Lógica simplificada para evitar errores)
        // Traemos las ferias que son institucionales de TU institución, O las regionales/circuitales
        $ferias = Feria::query()
            ->where(function($q) use ($institucionId) {
                $q->where('institucion_id', $institucionId)
                  ->orWhereNull('institucion_id'); 
            })
            ->orderByDesc('id')
            ->get(); 
            // Si quieres optimizar columnas: ->get(['id', 'nombre', 'tipo']) pero asegúrate que existan

        // 3. Estudiantes
        $est = Estudiante::query()
            ->when($institucionId, fn($q)=>$q->where('institucion_id',$institucionId))
            ->select(['id','cedula','nombre','apellidos'])
            ->orderBy('apellidos')
            ->get();

        return response()->json([
            'areas'         => $areas,
            'categorias'    => $categorias,
            'ferias'        => $ferias, // <--- Ahora es seguro
            'estudiantes'   => $est,
            'modalidades'   => $modalidades,
            'instituciones' => $instituciones,
        ]);
    }
    /**
     * Utilidad: formatea/loguea excepciones con detalles SQL cuando aplica.
     */
    private function formatAndLogException(\Throwable $e, array $context = []): array
    {
        $base = [
            'type'    => class_basename($e),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ];

        if ($e instanceof QueryException) {
            $base['sql']      = $e->getSql();
            $base['bindings'] = $e->getBindings();
        }

        Log::error('[PROYECTOS][STORE] Excepción', array_merge($base, $context));

        // Respuesta detallada SOLO para depuración
        return array_merge(['message' => 'Error al crear proyecto'], $base);
    }

    // PUT /api/proyectos/{proyecto}
    public function update(Request $request, Proyecto $proyecto)
    {
        // 1. Validamos los mismos campos que en store
        $data = $request->validate([
            'titulo'         => 'required|string|max:255',
            'resumen'        => 'nullable|string',
            'area_id'        => ['required', Rule::exists('areas', 'id')],
            'modalidad_id'   => ['required', Rule::exists('modalidades', 'id')],
            'categoria_id'   => ['required', Rule::exists('categorias', 'id')],
            'institucion_id' => ['nullable', Rule::exists('instituciones', 'id')],
            'feria_id'       => ['required', Rule::exists('ferias', 'id')],
            'estudiantes'    => 'nullable|array',
            'estudiantes.*'  => ['integer', Rule::exists('estudiantes', 'id')],
        ]);

        DB::beginTransaction();
        try {
            // 2. Actualizamos el proyecto
            $proyecto->update([
                'titulo'         => $data['titulo'],
                'resumen'        => $data['resumen'] ?? null,
                'area_id'        => $data['area_id'],
                'modalidad_id'   => $data['modalidad_id'],
                'categoria_id'   => $data['categoria_id'],
                'institucion_id' => $data['institucion_id'] ?? $proyecto->institucion_id, // Mantiene la anterior si viene nula
                'feria_id'       => $data['feria_id'],
            ]);

            // 3. Sincronizamos estudiantes (si se enviaron)
            // Si quieres que al editar vacío se borren los estudiantes, usa sync($data['estudiantes'] ?? [])
            if (isset($data['estudiantes'])) {
                $proyecto->estudiantes()->sync($data['estudiantes']);
            }

            DB::commit();

            return response()->json($proyecto->load(['area','categoria','modalidad','institucion']), 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error actualizando proyecto: " . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar el proyecto'], 500);
        }
    }

    // DELETE /api/proyectos/{proyecto}
    public function destroy(Proyecto $proyecto)
    {
        try {
            // Desvincular relaciones para evitar error de FK
            $proyecto->estudiantes()->detach(); 
            $proyecto->tutores()->detach(); // Si usas tutores
            
            // Si tienes asignaciones de jueces, verificar primero o borrarlas:
            if ($proyecto->asignacionesJuez()->exists()) {
                 // Opción A: Impedir borrar si ya tiene jueces
                 // return response()->json(['message' => 'No se puede eliminar, ya tiene jueces asignados.'], 422);
                 
                 // Opción B: Borrar asignaciones (Cuidado con esto)
                 $proyecto->asignacionesJuez()->delete(); 
            }

            $proyecto->delete();
            return response()->json(['message' => 'Proyecto eliminado correctamente']);

        } catch (\Throwable $e) {
            Log::error("Error eliminando proyecto: " . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar el proyecto'], 500);
        }
    }

    /**
     * GET /api/proyectos/debug-schema
     * Inspección rápida de columnas.
     */
    public function debugSchema()
    {
        return response()->json([
            'proyectos_columns'  => \Schema::getColumnListing('proyectos'),
            'ferias_columns'     => \Schema::getColumnListing('ferias'),
            'areas_columns'      => \Schema::getColumnListing('areas'),
            'categorias_columns' => \Schema::getColumnListing('categorias'),
        ]);
    }
}
