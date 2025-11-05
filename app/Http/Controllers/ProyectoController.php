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

        $q = Proyecto::with(['area', 'categoria', 'estudiantes:id,cedula,nombre,apellidos'])
            ->when($institucionId, fn($qq) => $qq->where('institucion_id', $institucionId))
            ->when($request->filled('buscar'), function ($qq) use ($request) {
                $b = $request->buscar;
                $qq->where(function ($w) use ($b) {
                    $w->where('titulo', 'like', "%{$b}%")
                      ->orWhere('resumen', 'like', "%{$b}%");
                });
            })
            ->orderByDesc('id');

        return $q->paginate($request->integer('per_page', 10));
    }

    /**
     * POST /api/proyectos
     * Crea proyecto y liga estudiantes.
     */
    public function store(Request $request)
{
    
    $data = $request->validate([
        'titulo'         => 'required|string|max:255',
        'resumen'        => 'nullable|string',
        'area_id'        => ['required', Rule::exists('areas', 'id')],
        'categoria_id'   => ['required', Rule::exists('categorias', 'id')],
        'institucion_id' => ['nullable', Rule::exists('instituciones', 'id')],
        'feria_id'       => ['required', Rule::exists('ferias', 'id')],
        // estudiantes ya es opcional
        'estudiantes'    => 'nullable|array',
        'estudiantes.*'  => ['integer', Rule::exists('estudiantes', 'id')],
    ]);

    $data['institucion_id'] = $data['institucion_id'] ?? optional($request->user())->institucion_id;
    if (!$data['institucion_id']) {
        return response()->json(['message' => 'institucion_id es requerido'], 422);
    }

    // Fuerza enteros por si llegan como string
    $data['area_id']      = (int) $data['area_id'];
    $data['categoria_id'] = (int) $data['categoria_id'];
    $data['feria_id']     = (int) $data['feria_id'];

    try {
        // Contexto previo para logs
        \Log::info('[PROYECTOS][STORE] Payload recibido', [
            'request_all' => $request->all(),
            'validated'   => $data,
            'user_id'     => optional($request->user())->id,
        ]);

        // Reglas de negocio
        $inst = \App\Models\Institucion::findOrFail($data['institucion_id']);
        if (!$inst->puedeAgregarProyecto()) {
            return response()->json(['message' => 'Límite de proyectos alcanzado para la institución'], 422);
        }

        $feria = \App\Models\Feria::findOrFail($data['feria_id']);
        if (!empty($feria->institucion_id) && (int)$feria->institucion_id !== (int)$data['institucion_id']) {
            return response()->json(['message' => 'La feria no pertenece a la institución seleccionada'], 422);
        }

        if (!empty($data['estudiantes'])) {
            $mismatch = \App\Models\Estudiante::whereIn('id', $data['estudiantes'])
                ->where('institucion_id', '!=', $data['institucion_id'])
                ->exists();
            if ($mismatch) {
                return response()->json(['message' => 'Hay estudiantes que no pertenecen a esta institución'], 422);
            }
        }

        DB::beginTransaction();

        $proyecto = \App\Models\Proyecto::create([
            'titulo'         => $data['titulo'],
            'resumen'        => $data['resumen'] ?? null,
            'area_id'        => $data['area_id'],
            'categoria_id'   => $data['categoria_id'],
            'institucion_id' => $data['institucion_id'],
            'feria_id'       => $data['feria_id'],
            'etapa_actual'   => 1,
            'estado'         => 'inscrito',
        ]);

        if (!empty($data['estudiantes'])) {
            $proyecto->estudiantes()->sync($data['estudiantes']);
        }

        DB::commit();

        // \Log::debug('QueryLog', DB::getQueryLog()); // si habilitaste enableQueryLog()

        return response()->json(
            $proyecto->load(['area','categoria','estudiantes:id,cedula,nombre,apellidos']),
            201
        );
    } catch (\Throwable $e) {
        DB::rollBack();

        // Devuelve detalle *y* lo deja en laravel.log
        $context = [
            'request_all' => $request->all(),
            'validated'   => $data,
            'user_id'     => optional($request->user())->id,
        ];
        $payload = $this->formatAndLogException($e, $context);

        return response()->json($payload, 500);
    }
}

    /**
     * GET /api/proyectos/form-data?institucion_id=...
     * Devuelve catálogos y estudiantes para poblar el formulario.
     */
    public function formData(Request $request)
    {
        $institucionId = $request->get('institucion_id') ?? optional($request->user())->institucion_id;

        // helper: encuentra la primera columna existente
        $pick = function (string $table, array $candidates, $fallback = null) {
            foreach ($candidates as $c) {
                if (Schema::hasColumn($table, $c)) return $c;
            }
            return $fallback; // podría ser null si no hay candidatos
        };

        // ===== ÁREAS =====
        $areaTextCol = $pick('areas', ['nombre','name','titulo','descripcion']);
        $areas = Area::query()
            ->when($areaTextCol, fn($q)=>$q->addSelect('id', DB::raw("$areaTextCol as nombre")))
            ->when(!$areaTextCol, fn($q)=>$q->addSelect('id', DB::raw("'Área' as nombre")))
            ->orderBy('nombre')
            ->get();

        // ===== CATEGORÍAS =====
        $catTextCol = $pick('categorias', ['nombre','name','titulo','descripcion']);
        $categorias = Categoria::query()
            ->when($catTextCol, fn($q)=>$q->addSelect('id', DB::raw("$catTextCol as nombre")))
            ->when(!$catTextCol, fn($q)=>$q->addSelect('id', DB::raw("'Categoría' as nombre")))
            ->when(Schema::hasColumn('categorias','nivel'), fn($q)=>$q->addSelect('nivel'))
            ->orderBy('nombre')
            ->get();

        // ===== FERIAS =====
        $feriaTextCol = $pick('ferias', ['nombre','name','titulo','descripcion']);
        $hasTipo  = Schema::hasColumn('ferias','tipo');
        $hasNivel = Schema::hasColumn('ferias','nivel');

        if ($hasTipo) {
            // Caso A: existe columna `tipo`
            $ferias = Feria::query()
                ->where(function ($w) use ($institucionId) {
                    $w->where(function ($x) use ($institucionId) {
                        $x->where('tipo', 'institucional')->where('institucion_id', $institucionId);
                    })
                    ->orWhereIn('tipo', ['circuital', 'regional']);
                })
                ->select([
                    'id',
                    $feriaTextCol ? DB::raw("$feriaTextCol as nombre") : DB::raw("'Feria' as nombre"),
                    'institucion_id',
                    'tipo',
                ])
                ->orderByDesc('id')
                ->get();

        } elseif ($hasNivel) {
            // Caso B: existe `nivel` en lugar de `tipo`
            $ferias = Feria::query()
                ->where(function ($w) use ($institucionId) {
                    $w->where(function ($x) use ($institucionId) {
                        $x->where('nivel', 'institucional')->where('institucion_id', $institucionId);
                    })
                    ->orWhereIn('nivel', ['circuital', 'regional']);
                })
                ->select([
                    'id',
                    $feriaTextCol ? DB::raw("$feriaTextCol as nombre") : DB::raw("'Feria' as nombre"),
                    'institucion_id',
                    DB::raw('nivel as tipo'), // normalizamos a "tipo"
                ])
                ->orderByDesc('id')
                ->get();

        } else {
            // Caso C: no hay ni `tipo` ni `nivel`
            $ferias = Feria::query()
                ->where(function ($w) use ($institucionId) {
                    $w->where('institucion_id', $institucionId)
                      ->orWhereNull('institucion_id');
                      // si tu esquema usa 0 en vez de NULL, agrega: ->orWhere('institucion_id', 0);
                })
                ->select([
                    'id',
                    $feriaTextCol ? DB::raw("$feriaTextCol as nombre") : DB::raw("'Feria' as nombre"),
                    'institucion_id',
                    DB::raw("CASE WHEN institucion_id = ".(int)($institucionId ?: 0)." THEN 'institucional' ELSE 'general' END as tipo"),
                ])
                ->orderByDesc('id')
                ->get();
        }

        // ===== ESTUDIANTES =====
        $est = Estudiante::query()
            ->when($institucionId, fn($q)=>$q->where('institucion_id',$institucionId))
            ->select(['id','cedula',
                // intenta armar nombre/apellidos aunque las columnas varíen
                $pick('estudiantes',['nombre'],'nombre').' as nombre',
                $pick('estudiantes',['apellidos','apellido','apellido1'],'apellidos').' as apellidos',
            ])
            ->orderBy('apellidos')
            ->get();

        return response()->json([
            'areas'       => $areas,
            'categorias'  => $categorias,
            'ferias'      => $ferias,
            'estudiantes' => $est,
        ]);
    }
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

        // Respuesta detallada SOLO para depurar (no dejes esto en producción)
        return array_merge(['message' => 'Error al crear proyecto'], $base);
    }

    public function debugSchema()
    {
        return response()->json([
            'proyectos_columns' => \Schema::getColumnListing('proyectos'),
            'ferias_columns'    => \Schema::getColumnListing('ferias'),
            'areas_columns'     => \Schema::getColumnListing('areas'),
            'categorias_columns'=> \Schema::getColumnListing('categorias'),
        ]);
    }
}

