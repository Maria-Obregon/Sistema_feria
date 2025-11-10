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
    $data = $request->validate([
        'titulo'         => 'required|string|max:255',
        'resumen'        => 'nullable|string',
        'area_id'        => ['required', Rule::exists('areas', 'id')],

        // Viene del formulario
        'modalidad_id'   => ['required', Rule::exists('modalidades', 'id')],

        // La categoría debe existir y ser elegible para esa modalidad (pivot categoria_modalidad)
        'categoria_id'   => [
            'required',
            Rule::exists('categorias', 'id'),
            Rule::exists('categoria_modalidad', 'categoria_id')
                ->where(fn($q) => $q->where('modalidad_id', $request->input('modalidad_id')))
        ],

        'institucion_id' => ['nullable', Rule::exists('instituciones', 'id')],
        'feria_id'       => ['required', Rule::exists('ferias', 'id')],

        // estudiantes opcional
        'estudiantes'    => 'nullable|array',
        'estudiantes.*'  => ['integer', Rule::exists('estudiantes', 'id')],
    ]);

    // Hereda institución del usuario si no viene en payload
    $data['institucion_id'] = $data['institucion_id'] ?? optional($request->user())->institucion_id;
    if (!$data['institucion_id']) {
        return response()->json(['message' => 'institucion_id es requerido'], 422);
    }

    // Normaliza tipos
    $data['area_id']        = (int) $data['area_id'];
    $data['modalidad_id']   = (int) $data['modalidad_id'];
    $data['categoria_id']   = (int) $data['categoria_id'];
    $data['feria_id']       = (int) $data['feria_id'];

    try {
        Log::info('[PROYECTOS][STORE] Payload recibido', [
            'request_all' => $request->all(),
            'validated'   => $data,
            'user_id'     => optional($request->user())->id,
        ]);

        // ===== Reglas de negocio previas =====
        $inst = Institucion::findOrFail($data['institucion_id']);
        if (method_exists($inst, 'puedeAgregarProyecto') && !$inst->puedeAgregarProyecto()) {
            return response()->json(['message' => 'Límite de proyectos alcanzado para la institución'], 422);
        }

        $feria = Feria::findOrFail($data['feria_id']);

        // Si la feria es institucional, debe pertenecer a la misma institución
        if (!empty($feria->institucion_id) && (int)$feria->institucion_id !== (int)$data['institucion_id']) {
            return response()->json(['message' => 'La feria no pertenece a la institución seleccionada'], 422);
        }

        // Estudiantes deben pertenecer a la misma institución
        if (!empty($data['estudiantes'])) {
            $mismatch = Estudiante::whereIn('id', $data['estudiantes'])
                ->where('institucion_id', '!=', $data['institucion_id'])
                ->exists();
            if ($mismatch) {
                return response()->json(['message' => 'Hay estudiantes que no pertenecen a esta institución'], 422);
            }
        }

        // ===== Determinar ETAPA desde la feria =====
        // Preferimos ferias.etapa_id -> relación etapa(); si no, usamos tipo_feria/tipo
        $feria->loadMissing('etapa');
        $etapaSlug = null;
        $etapaId   = null;

        if (\Schema::hasColumn('ferias', 'etapa_id') && !is_null($feria->etapa_id)) {
            $etapaId   = (int) $feria->etapa_id;
            $etapaSlug = strtolower(optional($feria->etapa)->nombre ?? '');
        } else {
            $etapaSlug = strtolower($feria->tipo_feria ?? $feria->tipo ?? 'institucional');
        }

        // Normaliza valores admitidos
        $validSlugs = ['institucional', 'circuital', 'regional'];
        if (!in_array($etapaSlug, $validSlugs, true)) {
            return response()->json(['message' => "Etapa de la feria no reconocida: {$etapaSlug}"], 422);
        }

        // Si no tenemos etapa_id pero sí tabla etapas, buscamos el id por nombre
        if (is_null($etapaId) && \Schema::hasTable('etapas') && \Schema::hasColumn('proyectos', 'etapa_id')) {
            $etapaModel = \App\Models\Etapa::whereRaw('LOWER(nombre)=?', [$etapaSlug])->first();
            $etapaId    = $etapaModel?->id;
        }

        // ===== Validar que la modalidad pueda participar en esa etapa (pivot modalidad_etapa) =====
        if (\Schema::hasTable('modalidad_etapa')) {
            // Si no tenemos etapa_id, intentamos resolverlo por nombre (de nuevo, por seguridad)
            if (is_null($etapaId) && \Schema::hasTable('etapas')) {
                $etapaModel = \App\Models\Etapa::whereRaw('LOWER(nombre)=?', [$etapaSlug])->first();
                $etapaId    = $etapaModel?->id;
            }

            if (!is_null($etapaId)) {
                $permite = DB::table('modalidad_etapa')
                    ->where('modalidad_id', $data['modalidad_id'])
                    ->where('etapa_id', $etapaId)
                    ->exists();

                if (!$permite) {
                    return response()->json([
                        'message' => 'La modalidad seleccionada no puede participar en la etapa de esta feria.',
                        'detalle' => [
                            'modalidad_id' => $data['modalidad_id'],
                            'feria_id'     => $data['feria_id'],
                            'etapa_id'     => $etapaId,
                            'etapa'        => $etapaSlug,
                        ]
                    ], 422);
                }
            }
        }

        // ===== Crear proyecto =====
        DB::beginTransaction();

        // Prepara atributos comunes
        $attrs = [
            'titulo'         => $data['titulo'],
            'resumen'        => $data['resumen'] ?? null,
            'area_id'        => $data['area_id'],
            'modalidad_id'   => $data['modalidad_id'],
            'categoria_id'   => $data['categoria_id'],
            'institucion_id' => $data['institucion_id'],
            'feria_id'       => $data['feria_id'],
            'estado'         => 'inscrito',
        ];

        // Detecta qué columna de etapa usa tu tabla proyectos
        if (\Schema::hasColumn('proyectos', 'etapa_id') && !is_null($etapaId)) {
            $attrs['etapa_id'] = $etapaId;
        } elseif (\Schema::hasColumn('proyectos', 'etapa_actual')) {
            // ENUM
            $attrs['etapa_actual'] = $etapaSlug; // 'institucional' | 'circuital' | 'regional'
        }

        $proyecto = Proyecto::create($attrs);

        if (!empty($data['estudiantes'])) {
            $proyecto->estudiantes()->sync($data['estudiantes']);
        }

        DB::commit();

        // Devuelve con relaciones útiles (agregué feria y modalidad si querés mostrarlas)
        return response()->json(
            $proyecto->load([
                'area',
                'categoria',
                'feria',
                'estudiantes:id,cedula,nombre,apellidos',
                'modalidad:id,nombre',
                'etapa' // si tenés relación en Proyecto cuando uses etapa_id
            ]),
            201
        );
    } catch (\Throwable $e) {
        DB::rollBack();
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
            return $fallback; // puede ser null si no hay candidatos
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
