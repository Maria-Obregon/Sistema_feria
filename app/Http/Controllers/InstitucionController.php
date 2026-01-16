<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\Circuito;
use App\Models\Regional;
use App\Models\Usuario;
use App\Models\Modalidad;
use App\Models\TipoInstitucion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstitucionController extends Controller
{
    private function normaliza(?string $v): ?string
    {
        if ($v === null) return null;
        return trim(preg_replace('/\s+/', ' ', $v));
    }

    private function hidrataCatalogosEnTexto(array &$data): void
    {
        if (!empty($data['modalidad_id'])) {
            $data['modalidad'] = Modalidad::whereKey($data['modalidad_id'])->value('nombre');
        }
        if (!empty($data['tipo_institucion_id'])) {
            $data['tipo'] = TipoInstitucion::whereKey($data['tipo_institucion_id'])->value('nombre');
        }
    }

    private function requireModalidadTipo(array $data)
    {
        if (empty($data['modalidad'])) {
            abort(response()->json(['message' => 'La modalidad es requerida'], 422));
        }
        if (empty($data['tipo'])) {
            abort(response()->json(['message' => 'El tipo de institución es requerido'], 422));
        }
    }

    private function resolverRegional(array &$data): void
    {
        $circuito = Circuito::select('id', 'regional_id')->findOrFail($data['circuito_id']);

        if (!empty($data['regional_id']) && (int)$data['regional_id'] !== (int)$circuito->regional_id) {
            abort(response()->json(['message' => 'El circuito seleccionado no pertenece a la Dirección Regional indicada.'], 422));
        }

        $data['regional_id']     = $circuito->regional_id;
        $data['direccionreg_id'] = $circuito->regional_id;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $q = Institucion::with(['circuito.regional', 'regional'])
            ->when($request->filled('buscar'), function ($qq) use ($request) {
                $buscar = $this->normaliza($request->get('buscar'));
                $qq->where(function ($s) use ($buscar) {
                    $s->where('nombre', 'like', "%{$buscar}%")
                      ->orWhere('codigo_presupuestario', 'like', "%{$buscar}%");
                });
            })
            ->when($request->filled('tipo'), fn($qq) => $qq->where('tipo', $request->tipo))
            ->when($request->filled('circuito_id'), fn($qq) => $qq->where('circuito_id', $request->circuito_id))
            ->when($request->has('activo') && $request->get('activo') !== null, function ($qq) use ($request) {
                $qq->where('activo', filter_var($request->get('activo'), FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('nombre');

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('comite_institucional')) {
            $q->where('id', $user->institucion_id);
        }

        return response()->json($q->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request)
    {
        if ($request->user() && $request->user()->hasRole('comite_institucional')) {
            return response()->json(['message' => 'No tienes permisos para crear instituciones.'], 403);
        }

        if ($request->has('codigo_presupuestario') || $request->has('codigo')) {
            $codigo = $request->input('codigo_presupuestario', $request->input('codigo'));
            $request->merge(['codigo_presupuestario' => $this->normaliza($codigo)]);
        }

        $data = $request->validate([
            'nombre'                => 'required|string|max:200',
            'codigo_presupuestario' => 'required|string|max:20|unique:instituciones,codigo_presupuestario',
            'circuito_id'           => 'required|exists:circuitos,id',
            'regional_id'           => 'nullable|exists:regionales,id',

            'modalidad'             => 'nullable|string|max:100',
            'tipo'                  => 'nullable|string|max:100',
            'modalidad_id'          => 'nullable|exists:modalidades,id',
            'tipo_institucion_id'   => 'nullable|exists:tipos_institucion,id',

            'telefono'              => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:100',
            'direccion'             => 'nullable|string',
            'limite_proyectos'      => 'nullable|integer|min:1|max:50',
            'limite_estudiantes'    => 'nullable|integer|min:1|max:200',
            'activo'                => 'boolean',

            'emitir_credenciales'   => 'sometimes|boolean',
            'responsable_nombre'    => 'nullable|string|max:255',
            'responsable_email'     => 'nullable|email|max:120|unique:usuarios,email',
            'responsable_telefono'  => 'nullable|string|max:20',
        ]);

        $this->hidrataCatalogosEnTexto($data);
        $this->requireModalidadTipo($data);
        $this->resolverRegional($data);

        $data['limite_proyectos']   = $data['limite_proyectos']   ?? 50;
        $data['limite_estudiantes'] = $data['limite_estudiantes'] ?? 200;
        $data['activo']             = array_key_exists('activo', $data) ? (bool)$data['activo'] : true;

        DB::beginTransaction();
        try {
            $institucion = Institucion::create([
                'nombre'                => $data['nombre'],
                'codigo_presupuestario' => $data['codigo_presupuestario'],
                'regional_id'           => $data['regional_id'],
                'direccionreg_id'       => $data['direccionreg_id'],
                'circuito_id'           => $data['circuito_id'],
                'modalidad'             => $data['modalidad'],
                'tipo'                  => $data['tipo'],
                'telefono'              => $data['telefono'] ?? null,
                'email'                 => $data['email'] ?? null,
                'direccion'             => $data['direccion'] ?? null,
                'limite_proyectos'      => $data['limite_proyectos'],
                'limite_estudiantes'    => $data['limite_estudiantes'],
                'activo'                => $data['activo'],
            ]);

            $institucion->load(['circuito.regional', 'regional']);

            $resp = [
                'mensaje'     => 'Institución creada',
                'institucion' => $institucion,
            ];

            if ($request->boolean('emitir_credenciales') && $request->filled('responsable_email')) {
                $plain = Str::upper(Str::random(10));
                $usuario = Usuario::create([
                    'nombre'         => $data['responsable_nombre'] ?? ($institucion->nombre.' - Responsable'),
                    'email'          => $data['responsable_email'],
                    'password'       => $plain,
                    'activo'         => true,
                    'institucion_id' => $institucion->id,
                    'telefono'       => $data['responsable_telefono'] ?? null,
                ]);
                if (method_exists($usuario, 'assignRole')) {
                    $usuario->assignRole('comite_institucional');
                }
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

    public function show(Institucion $institucion)
    {
        $institucion->load(['circuito.regional', 'regional', 'usuarios', 'proyectos', 'estudiantes']);

        $estadisticas = [
            'total_usuarios'       => $institucion->usuarios()->count(),
            'total_proyectos'      => $institucion->proyectos()->count(),
            'total_estudiantes'    => $institucion->estudiantes()->count(),
            'proyectos_por_etapa'  => $institucion->proyectos()
                ->selectRaw('etapa_actual, COUNT(*) as total')
                ->groupBy('etapa_actual')
                ->pluck('total','etapa_actual'),
            'proyectos_por_estado' => $institucion->proyectos()
                ->selectRaw('estado, COUNT(*) as total')
                ->groupBy('estado')
                ->pluck('total','estado'),
        ];

        return response()->json([
            'institucion'  => $institucion,
            'estadisticas' => $estadisticas,
        ]);
    }

    public function update(Request $request, Institucion $institucion)
    {
        $user = $request->user();
        if ($user && $user->hasRole('comite_institucional') && $user->institucion_id !== $institucion->id) {
            return response()->json(['message' => 'No puedes editar otra institución.'], 403);
        }

        if ($request->has('codigo_presupuestario') || $request->has('codigo')) {
            $codigo = $request->input('codigo_presupuestario', $request->input('codigo'));
            $request->merge(['codigo_presupuestario' => $this->normaliza($codigo)]);
        }

        $data = $request->validate([
            'nombre'                => 'required|string|max:200',
            'codigo_presupuestario' => ['required','string','max:20', Rule::unique('instituciones','codigo_presupuestario')->ignore($institucion->id)],
            'circuito_id'           => 'required|exists:circuitos,id',
            'regional_id'           => 'nullable|exists:regionales,id',

            'modalidad'             => 'nullable|string|max:100',
            'tipo'                  => 'nullable|string|max:100',
            'modalidad_id'          => 'nullable|exists:modalidades,id',
            'tipo_institucion_id'   => 'nullable|exists:tipos_institucion,id',

            'telefono'              => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:100',
            'direccion'             => 'nullable|string',
            'limite_proyectos'      => 'nullable|integer|min:1|max:50',
            'limite_estudiantes'    => 'nullable|integer|min:1|max:200',
            'activo'                => 'boolean',
        ]);

        $this->hidrataCatalogosEnTexto($data);
        $this->requireModalidadTipo($data);
        $this->resolverRegional($data);

        $institucion->update([
            'nombre'                => $data['nombre'],
            'codigo_presupuestario' => $data['codigo_presupuestario'],
            'regional_id'           => $data['regional_id'],
            'direccionreg_id'       => $data['direccionreg_id'],
            'circuito_id'           => $data['circuito_id'],
            'modalidad'             => $data['modalidad'],
            'tipo'                  => $data['tipo'],
            'telefono'              => $data['telefono'] ?? null,
            'email'                 => $data['email'] ?? null,
            'direccion'             => $data['direccion'] ?? null,
            'limite_proyectos'      => $data['limite_proyectos'] ?? $institucion->limite_proyectos,
            'limite_estudiantes'    => $data['limite_estudiantes'] ?? $institucion->limite_estudiantes,
            'activo'                => array_key_exists('activo', $data) ? (bool)$data['activo'] : $institucion->activo,
        ]);

        return response()->json([
            'mensaje'     => 'Institución actualizada',
            'institucion' => $institucion->fresh()->load(['circuito.regional','regional']),
        ]);
    }
   public function destroy(Request $request, Institucion $institucion)
{
    if ($request->user() && $request->user()->hasRole('comite_institucional')) {
        return response()->json(['message' => 'No tienes permisos para eliminar instituciones.'], 403);
    }

    DB::transaction(function () use ($institucion) {
        $institucion->proyectos()->delete();
        $institucion->usuarios()->delete();
        $institucion->estudiantes()->delete(); 
        $institucion->delete();
    });

    return response()->json(['mensaje' => 'Institución, proyectos y usuarios eliminados exitosamente']);
}


    public function toggleActivo(Institucion $institucion)
    {
        $institucion->update(['activo' => !$institucion->activo]);

        return response()->json([
            'mensaje' => $institucion->activo ? 'Institución activada' : 'Institución desactivada',
            'activo'  => $institucion->activo,
        ]);
    }

    public function getCatalogos()
    {
        return response()->json([
            'modalidades'       => Modalidad::where('activo', true)->orderBy('nombre')->get(['id','nombre']),
            'tipos_institucion' => TipoInstitucion::where('activo', true)->orderBy('nombre')->get(['id','nombre']),
        ]);
    }

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