<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\Circuito;
use App\Models\Modalidad;
use App\Models\TipoInstitucion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstitucionController extends Controller
{
    /**
     * GET /api/instituciones
     */
    public function index(Request $request)
    {
        $query = Institucion::with(['circuito.regional']);

        if ($request->filled('buscar')) {
            $b = $request->get('buscar');
            $query->where(function ($q) use ($b) {
                $q->where('nombre', 'like', "%{$b}%")
                  ->orWhere('codigo_presupuestario', 'like', "%{$b}%");
            });
        }
        if ($request->filled('tipo'))        $query->where('tipo', $request->get('tipo'));
        if ($request->filled('circuito_id')) $query->where('circuito_id', $request->get('circuito_id'));
        if ($request->has('activo'))         $query->where('activo', $request->boolean('activo'));

        return response()->json(
            $query->orderBy('nombre')->paginate($request->integer('per_page', 15))
        );
    }

    /**
     * POST /api/instituciones
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'               => 'required|string|max:200',
            'codigo_presupuestario'=> 'required|string|max:20|unique:instituciones,codigo_presupuestario',
            'circuito_id'          => 'required|exists:circuitos,id',
            'tipo'                 => 'required|in:publica,privada,subvencionada',
            'telefono'             => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:100',
            'direccion'            => 'nullable|string',
            'limite_proyectos'     => 'nullable|integer|min:1|max:50',
            'limite_estudiantes'   => 'nullable|integer|min:1|max:200',
            'activo'               => 'boolean',
        ]);

        $data['limite_proyectos']   = $data['limite_proyectos']   ?? 50;
        $data['limite_estudiantes'] = $data['limite_estudiantes'] ?? 200;
        $data['activo']             = $data['activo'] ?? true;

        $inst = Institucion::create($data)->load(['circuito.regional']);

        return response()->json(['mensaje' => 'Institución creada exitosamente','institucion' => $inst], 201);
    }

    /**
     * PUT /api/instituciones/{institucion}
     * (ÚNICO update)
     */
    public function update(Request $request, Institucion $institucion)
    {
        $data = $request->validate([
            'nombre'               => 'required|string|max:200',
            'codigo_presupuestario'=> ['required','string','max:20', Rule::unique('instituciones','codigo_presupuestario')->ignore($institucion->id)],
            'circuito_id'          => 'required|exists:circuitos,id',
            // si mantienes 'tipo' como columna string en tu tabla:
            'tipo'                 => 'required|in:publica,privada,subvencionada',
            'telefono'             => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:100',
            'direccion'            => 'nullable|string',
            'limite_proyectos'     => 'nullable|integer|min:1|max:50',
            'limite_estudiantes'   => 'nullable|integer|min:1|max:200',
            'activo'               => 'boolean',
            // opcionales para mapear a texto:
            'modalidad_id'         => 'nullable|exists:modalidades,id',
            'tipo_institucion_id'  => 'nullable|exists:tipos_institucion,id',
        ]);

        // Mapear opcionales a sus nombres (si existen esas tablas)
        if (!empty($data['modalidad_id'])) {
            $modalidadNombre = Modalidad::whereKey($data['modalidad_id'])->value('nombre');
            if ($modalidadNombre) $data['modalidad'] = $modalidadNombre; // solo si tu tabla tiene columna 'modalidad'
        }
        if (!empty($data['tipo_institucion_id'])) {
            $tipoNombre = TipoInstitucion::whereKey($data['tipo_institucion_id'])->value('nombre');
            if ($tipoNombre) $data['tipo'] = $tipoNombre;
        }

        $data['activo']      = $request->boolean('activo', true);
        $data['regional_id'] = Circuito::whereKey($data['circuito_id'])->value('regional_id'); // <-- corregido

        $institucion->update($data);

        return response()->json([
            'mensaje' => 'Institución actualizada',
            'data'    => $institucion->fresh()->load(['circuito.regional']),
        ]);
    }

    /**
     * GET /api/instituciones/{institucion}
     */
    public function show(Institucion $institucion)
    {
        $institucion->load(['circuito.regional','usuarios','proyectos','estudiantes']);

        $estadisticas = [
            'total_usuarios'       => $institucion->usuarios()->count(),
            'total_proyectos'      => $institucion->proyectos()->count(),
            'total_estudiantes'    => $institucion->estudiantes()->count(),
            'proyectos_por_etapa'  => $institucion->proyectos()->selectRaw('etapa_actual, COUNT(*) total')->groupBy('etapa_actual')->pluck('total','etapa_actual'),
            'proyectos_por_estado' => $institucion->proyectos()->selectRaw('estado, COUNT(*) total')->groupBy('estado')->pluck('total','estado'),
        ];

        return response()->json(['institucion' => $institucion, 'estadisticas' => $estadisticas]);
    }

    /**
     * DELETE /api/instituciones/{institucion}
     */
    public function destroy(Institucion $institucion)
    {
        if ($institucion->proyectos()->exists() || $institucion->usuarios()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: tiene proyectos o usuarios asociados.'], 422);
        }
        $institucion->delete();
        return response()->json(['message' => 'Institución eliminada exitosamente']);
    }

    /**
     * PATCH /api/instituciones/{institucion}/toggle
     */
    public function toggleActivo(Institucion $institucion)
    {
        $institucion->update(['activo' => ! $institucion->activo]);

        return response()->json([
            'mensaje' => $institucion->activo ? 'Institución activada' : 'Institución desactivada',
            'activo'  => $institucion->activo,
        ]);
    }
}
