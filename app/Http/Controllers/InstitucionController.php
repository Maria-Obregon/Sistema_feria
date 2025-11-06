<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\Circuito;
use App\Models\Regional;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstitucionController extends Controller
{
    /**
     * GET /api/instituciones
     */
    public function index(Request $request)
    {
        $q = Institucion::with(['circuito.regional', 'regional']);

        if ($request->filled('buscar')) {
            $buscar = trim(preg_replace('/\s+/', ' ', $request->get('buscar')));

            $q->where(function ($qq) use ($buscar) {
                $qq->where('nombre', 'like', "%{$buscar}%")
                   ->orWhere('codigo_presupuestario', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('tipo'))        $q->where('tipo', $request->get('tipo'));
        if ($request->filled('circuito_id')) $q->where('circuito_id', $request->get('circuito_id'));
        if ($request->has('activo') && $request->get('activo') !== null) {
            $q->where('activo', filter_var($request->get('activo'), FILTER_VALIDATE_BOOLEAN));
        }

        $data = $q->orderBy('nombre')->paginate($request->integer('per_page', 15));
        return response()->json($data);
    }

    /**
     * Normaliza el código (trim + colapsa espacios)
     */
    private function normalizaCodigo(?string $codigo): ?string
    {
        if ($codigo === null) return null;
        return trim(preg_replace('/\s+/', ' ', $codigo));
    }

    /**
     * POST /api/instituciones
     */
    public function store(Request $request)
    {
        // Normalización del código (evita falsos duplicados por espacios)
        if ($request->has('codigo_presupuestario') || $request->has('codigo')) {
            $codigo = $request->input('codigo_presupuestario', $request->input('codigo'));
            $request->merge(['codigo_presupuestario' => $this->normalizaCodigo($codigo)]);
        }

        $datos = $request->validate([
            'nombre'                => 'required|string|max:200',
            'codigo_presupuestario' => 'required|string|max:20|unique:instituciones,codigo_presupuestario',
            'regional_id'           => 'required|exists:regionales,id',
            'circuito_id'           => 'required|exists:circuitos,id',
            'modalidad'             => ['required', Rule::in(['Primaria','Secundaria','Técnica'])],
            'tipo'                  => ['required', Rule::in(['publica','privada','subvencionada'])],
            'telefono'              => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:100',
            'direccion'             => 'nullable|string',
            'limite_proyectos'      => 'nullable|integer|min:1|max:50',
            'limite_estudiantes'    => 'nullable|integer|min:1|max:200',
            'activo'                => 'nullable|boolean',

            'emitir_credenciales'   => 'sometimes|boolean',
            'responsable_nombre'    => 'nullable|string|max:255',
            'responsable_email'     => 'nullable|email|max:120|unique:usuarios,email',
            'responsable_telefono'  => 'nullable|string|max:20',
        ]);

        // Coherencia circuito ↔ regional
        $circuito = Circuito::select('id','regional_id')->findOrFail($datos['circuito_id']);
        if ((int)$circuito->regional_id !== (int)$datos['regional_id']) {
            return response()->json(['message' => 'El circuito seleccionado no pertenece a la Dirección Regional indicada.'], 422);
        }

        // Defaults
        $datos['limite_proyectos']   = $datos['limite_proyectos']   ?? 50;
        $datos['limite_estudiantes'] = $datos['limite_estudiantes'] ?? 200;
        $datos['activo']             = array_key_exists('activo', $datos) ? (bool)$datos['activo'] : true;

        DB::beginTransaction();
        try {
            $institucion = Institucion::create([
                'nombre'                => $datos['nombre'],
                'codigo_presupuestario' => $datos['codigo_presupuestario'],
                'regional_id'           => $datos['regional_id'],
                'circuito_id'           => $datos['circuito_id'],
                'modalidad'             => $datos['modalidad'],
                'tipo'                  => $datos['tipo'],
                'telefono'              => $datos['telefono'] ?? null,
                'email'                 => $datos['email'] ?? null,
                'direccion'             => $datos['direccion'] ?? null,
                'limite_proyectos'      => $datos['limite_proyectos'],
                'limite_estudiantes'    => $datos['limite_estudiantes'],
                'activo'                => $datos['activo'],
            ]);

            $institucion->load(['circuito.regional', 'regional']);

            $resp = [
                'message'     => 'Institución creada exitosamente',
                'institucion' => $institucion,
            ];

            // Credenciales (opcional)
            if ($request->boolean('emitir_credenciales') && $request->filled('responsable_email')) {
                $plain = Str::upper(Str::random(10));

                $usuario = Usuario::create([
                    'nombre'         => $datos['responsable_nombre'] ?? ($institucion->nombre . ' - Responsable'),
                    'email'          => $datos['responsable_email'],
                    'password'       => $plain,   // se hashea por cast en Usuario
                    'activo'         => true,
                    'institucion_id' => $institucion->id,
                    'telefono'       => $datos['responsable_telefono'] ?? null,
                ]);

                $usuario->assignRole('comite_institucional');

                $resp['credenciales'] = [
                    'usuario'  => $usuario->email,
                    'password' => $plain,
                ];
            }

            DB::commit();
            return response()->json($resp, 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear institución',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/instituciones/{institucion}
     */
    public function show(Institucion $institucion)
    {
        $institucion->load(['circuito.regional', 'regional', 'usuarios', 'proyectos', 'estudiantes']);

        $estadisticas = [
            'total_usuarios'      => $institucion->usuarios()->count(),
            'total_proyectos'     => $institucion->proyectos()->count(),
            'total_estudiantes'   => $institucion->estudiantes()->count(),
            'proyectos_por_etapa' => $institucion->proyectos()
                ->selectRaw('etapa_actual, COUNT(*) AS total')
                ->groupBy('etapa_actual')
                ->pluck('total', 'etapa_actual'),
            'proyectos_por_estado'=> $institucion->proyectos()
                ->selectRaw('estado, COUNT(*) AS total')
                ->groupBy('estado')
                ->pluck('total', 'estado'),
        ];

        return response()->json([
            'institucion' => $institucion,
            'estadisticas'=> $estadisticas,
        ]);
    }

    /**
     * PUT /api/instituciones/{institucion}
     */
    public function update(Request $request, Institucion $institucion)
    {
        // Normaliza el código entrante
        if ($request->has('codigo_presupuestario') || $request->has('codigo')) {
            $codigo = $request->input('codigo_presupuestario', $request->input('codigo'));
            $request->merge(['codigo_presupuestario' => $this->normalizaCodigo($codigo)]);
        }

        $datos = $request->validate([
            'nombre' => 'required|string|max:200',
            'codigo_presupuestario' => [
                'required','string','max:20',
                Rule::unique('instituciones','codigo_presupuestario')->ignore($institucion->id),
            ],
            'regional_id'         => 'required|exists:regionales,id',
            'circuito_id'         => 'required|exists:circuitos,id',
            'modalidad'           => ['required', Rule::in(['Primaria','Secundaria','Técnica'])],
            'tipo'                => ['required', Rule::in(['publica','privada','subvencionada'])],
            'telefono'            => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:100',
            'direccion'           => 'nullable|string',
            'limite_proyectos'    => 'nullable|integer|min:1|max:50',
            'limite_estudiantes'  => 'nullable|integer|min:1|max:200',
            'activo'              => 'nullable|boolean',
        ]);

        // Coherencia circuito ↔ regional
        $circuito = Circuito::select('id','regional_id')->findOrFail($datos['circuito_id']);
        if ((int)$circuito->regional_id !== (int)$datos['regional_id']) {
            return response()->json(['message' => 'El circuito seleccionado no pertenece a la Dirección Regional indicada.'], 422);
        }

        $institucion->update([
            'nombre'                => $datos['nombre'],
            'codigo_presupuestario' => $datos['codigo_presupuestario'],
            'regional_id'           => $datos['regional_id'],
            'circuito_id'           => $datos['circuito_id'],
            'modalidad'             => $datos['modalidad'],
            'tipo'                  => $datos['tipo'],
            'telefono'              => $datos['telefono'] ?? null,
            'email'                 => $datos['email'] ?? null,
            'direccion'             => $datos['direccion'] ?? null,
            'limite_proyectos'      => $datos['limite_proyectos'] ?? $institucion->limite_proyectos,
            'limite_estudiantes'    => $datos['limite_estudiantes'] ?? $institucion->limite_estudiantes,
            'activo'                => array_key_exists('activo', $datos) ? (bool)$datos['activo'] : $institucion->activo,
        ]);

        $institucion->load(['circuito.regional', 'regional']);

        return response()->json([
            'message'     => 'Institución actualizada exitosamente',
            'institucion' => $institucion,
        ]);
    }

    /**
     * DELETE /api/instituciones/{institucion}
     */
    public function destroy(Institucion $institucion)
    {
        if ($institucion->proyectos()->exists() || $institucion->usuarios()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: tiene proyectos o usuarios asociados.'
            ], 422);
        }

        $institucion->delete();
        return response()->json(['message' => 'Institución eliminada exitosamente']);
    }

    /**
     * PATCH /api/instituciones/{institucion}/toggle
     */
    public function toggleActivo(Institucion $institucion)
    {
        $institucion->update(['activo' => !$institucion->activo]);

        return response()->json([
            'message' => $institucion->activo ? 'Institución activada' : 'Institución desactivada',
            'activo'  => $institucion->activo,
        ]);
    }

    /**
     * Catálogos
     */
    public function catalogoRegionales()
    {
        return Regional::select('id','nombre')->orderBy('nombre')->get();
    }

    public function catalogoCircuitos(Request $request)
    {
        $q = Circuito::select('id','nombre','regional_id')->orderBy('nombre');
        if ($request->filled('regional_id')) {
            $q->where('regional_id', $request->get('regional_id'));
        }
        return $q->get();
    }

    public function getCircuitos()
    {
        return Circuito::with('regional')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }
}
