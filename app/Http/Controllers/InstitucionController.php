<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\Circuito;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstitucionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Institucion::with(['circuito.regional']);

        // Filtros opcionales
        if ($request->has('buscar')) {
            $buscar = $request->get('buscar');
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo_presupuestario', 'like', "%{$buscar}%");
            });
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->get('tipo'));
        }

        if ($request->has('circuito_id')) {
            $query->where('circuito_id', $request->get('circuito_id'));
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $instituciones = $query->orderBy('nombre')
                              ->paginate($request->get('per_page', 15));

        return response()->json($instituciones);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'nombre' => 'required|string|max:200',
            'codigo_presupuestario' => 'required|string|max:20|unique:instituciones,codigo_presupuestario',
            'circuito_id' => 'required|exists:circuitos,id',
            'tipo' => 'required|in:publica,privada,subvencionada',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'limite_proyectos' => 'integer|min:1|max:50',
            'limite_estudiantes' => 'integer|min:1|max:200',
            'activo' => 'boolean'
        ]);

        // Establecer valores por defecto
        $datosValidados['limite_proyectos'] = $datosValidados['limite_proyectos'] ?? 50;
        $datosValidados['limite_estudiantes'] = $datosValidados['limite_estudiantes'] ?? 200;
        $datosValidados['activo'] = $datosValidados['activo'] ?? true;

        $institucion = Institucion::create($datosValidados);
        $institucion->load(['circuito.regional']);

        return response()->json([
            'mensaje' => 'Institución creada exitosamente',
            'institucion' => $institucion
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institucion $institucion)
    {
        $institucion->load(['circuito.regional', 'usuarios', 'proyectos', 'estudiantes']);
        
        // Agregar estadísticas
        $estadisticas = [
            'total_usuarios' => $institucion->usuarios()->count(),
            'total_proyectos' => $institucion->proyectos()->count(),
            'total_estudiantes' => $institucion->estudiantes()->count(),
            'proyectos_por_etapa' => $institucion->proyectos()
                ->selectRaw('etapa_actual, COUNT(*) as total')
                ->groupBy('etapa_actual')
                ->pluck('total', 'etapa_actual'),
            'proyectos_por_estado' => $institucion->proyectos()
                ->selectRaw('estado, COUNT(*) as total')
                ->groupBy('estado')
                ->pluck('total', 'estado')
        ];

        return response()->json([
            'institucion' => $institucion,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Institucion $institucion)
    {
        $datosValidados = $request->validate([
            'nombre' => 'required|string|max:200',
            'codigo_presupuestario' => [
                'required',
                'string',
                'max:20',
                Rule::unique('instituciones')->ignore($institucion->id)
            ],
            'circuito_id' => 'required|exists:circuitos,id',
            'tipo' => 'required|in:publica,privada,subvencionada',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'limite_proyectos' => 'integer|min:1|max:50',
            'limite_estudiantes' => 'integer|min:1|max:200',
            'activo' => 'boolean'
        ]);

        $institucion->update($datosValidados);
        $institucion->load(['circuito.regional']);

        return response()->json([
            'mensaje' => 'Institución actualizada exitosamente',
            'institucion' => $institucion
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institucion $institucion)
    {
        // Verificar si tiene proyectos o usuarios asociados
        if ($institucion->proyectos()->count() > 0 || $institucion->usuarios()->count() > 0) {
            return response()->json([
                'mensaje' => 'No se puede eliminar la institución porque tiene proyectos o usuarios asociados'
            ], 422);
        }

        $institucion->delete();

        return response()->json([
            'mensaje' => 'Institución eliminada exitosamente'
        ]);
    }

    /**
     * Get circuitos for select options
     */
    public function getCircuitos()
    {
        $circuitos = Circuito::with('regional')
                            ->where('activo', true)
                            ->orderBy('nombre')
                            ->get();

        return response()->json($circuitos);
    }

    /**
     * Toggle active status
     */
    public function toggleActivo(Institucion $institucion)
    {
        $institucion->update(['activo' => !$institucion->activo]);
        
        return response()->json([
            'mensaje' => $institucion->activo ? 'Institución activada' : 'Institución desactivada',
            'activo' => $institucion->activo
        ]);
    }
}
