<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\Circuito;
// 👇 AGREGADOS para leer catálogos
use App\Models\Modalidad;
use App\Models\TipoInstitucion;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstitucionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = \App\Models\Institucion::with([
                'circuito:id,nombre,regional_id',
                'circuito.regional:id,nombre',
                'regional:id,nombre' // FK: direccionreg_id
            ])
            ->when($request->filled('buscar'), fn($qq) =>
                $qq->where(fn($s) => $s
                    ->where('nombre','like',"%{$request->buscar}%")
                    ->orWhere('codigo_presupuestario','like',"%{$request->buscar}%")
                )
            )
            ->when($request->filled('tipo'), fn($qq) => $qq->where('tipo', $request->tipo))
            ->when($request->filled('circuito_id'), fn($qq) => $qq->where('circuito_id', $request->circuito_id))
            ->when($request->filled('activo'), fn($qq) =>
                $qq->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN))
            )
            ->orderBy('nombre');

        return response()->json($q->paginate($request->get('per_page', 15)));
    }

    /**
     * Store a newly created resource in storage.
     *
     * Compatibilidad:
     * - Permitimos recibir EITHER:
     *     a) modalidad_id / tipo_institucion_id  (recomendado)
     *     b) modalidad / tipo (texto, como ya lo usabas)
     * - Si viene el *_id resolvemos el nombre y lo guardamos en los campos existentes (modalidad, tipo).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'                => 'required|string|max:200',
            // 👇 ahora son opcionales porque pueden venir por *_id
            'modalidad'             => 'nullable|string|max:100',
            'tipo'                  => 'nullable|string|max:100', // antes tenías in:publica,privada,subvencionada
            'codigo_presupuestario' => 'required|string|max:20|unique:instituciones,codigo_presupuestario',
            'circuito_id'           => 'required|exists:circuitos,id',
            'telefono'              => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:100',
            'direccion'             => 'nullable|string',
            'activo'                => 'boolean',
            'limite_proyectos'      => 'nullable|integer|min:1|max:50',
            'limite_estudiantes'    => 'nullable|integer|min:1|max:200',

            // 👇 NUEVOS (opcionales): si vienen, mapeamos a modalidad/tipo por nombre del catálogo
            'modalidad_id'          => ['nullable','exists:modalidades,id'],
            'tipo_institucion_id'   => ['nullable','exists:tipos_institucion,id'],
        ]);

        // Resolver modalidad/tipo desde IDs si vinieron (sin romper tu schema actual)
        if (!empty($data['modalidad_id'])) {
            $data['modalidad'] = Modalidad::whereKey($data['modalidad_id'])->value('nombre');
        }
        if (!empty($data['tipo_institucion_id'])) {
            $data['tipo'] = TipoInstitucion::whereKey($data['tipo_institucion_id'])->value('nombre');
        }

        // Requerimos que exista modalidad y tipo por al menos una vía
        if (empty($data['modalidad'])) {
            return response()->json(['message' => 'La modalidad es requerida'], 422);
        }
        if (empty($data['tipo'])) {
            return response()->json(['message' => 'El tipo de institución es requerido'], 422);
        }

        $data['activo'] = $request->boolean('activo', true);

        // Derivar la región desde el circuito
        $data['direccionreg_id'] = Circuito::whereKey($data['circuito_id'])->value('regional_id');

        $inst = Institucion::create($data);

        return response()->json([
            'mensaje' => 'Institución creada',
            'data'    => $inst->load(['circuito.regional','regional']),
        ], 201);
    }

    public function update(Request $request, Institucion $institucion)
    {
        $data = $request->validate([
            'nombre'                => 'required|string|max:200',
            // 👇 quedan opcionales; podemos recibir *_id también
            'modalidad'             => 'nullable|string|max:100',
            'tipo'                  => 'nullable|string|max:100',
            'codigo_presupuestario' => 'required|string|max:20|unique:instituciones,codigo_presupuestario,'.$institucion->id,
            'circuito_id'           => 'required|exists:circuitos,id',
            'telefono'              => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:100',
            'direccion'             => 'nullable|string',
            'activo'                => 'boolean',
            'limite_proyectos'      => 'nullable|integer|min:1|max:50',
            'limite_estudiantes'    => 'nullable|integer|min:1|max:200',

            // 👇 NUEVOS (opcionales)
            'modalidad_id'          => ['nullable','exists:modalidades,id'],
            'tipo_institucion_id'   => ['nullable','exists:tipos_institucion,id'],
        ]);

        if (!empty($data['modalidad_id'])) {
            $data['modalidad'] = Modalidad::whereKey($data['modalidad_id'])->value('nombre');
        }
        if (!empty($data['tipo_institucion_id'])) {
            $data['tipo'] = TipoInstitucion::whereKey($data['tipo_institucion_id'])->value('nombre');
        }

        if (empty($data['modalidad'])) {
            return response()->json(['message' => 'La modalidad es requerida'], 422);
        }
        if (empty($data['tipo'])) {
            return response()->json(['message' => 'El tipo de institución es requerido'], 422);
        }

        $data['activo'] = $request->boolean('activo', true);
        $data['direccionreg_id'] = Circuito::whereKey($data['circuito_id'])->value('regional_id');

        $institucion->update($data);

        return response()->json([
            'mensaje' => 'Institución actualizada',
            'data'    => $institucion->fresh()->load(['circuito.regional','regional']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institucion $institucion)
    {
        $institucion->load(['circuito.regional', 'usuarios', 'proyectos', 'estudiantes']);
        
        // Agregar estadísticas
        $estadisticas = [
            'total_usuarios'        => $institucion->usuarios()->count(),
            'total_proyectos'       => $institucion->proyectos()->count(),
            'total_estudiantes'     => $institucion->estudiantes()->count(),
            'proyectos_por_etapa'   => $institucion->proyectos()
                ->selectRaw('etapa_actual, COUNT(*) as total')
                ->groupBy('etapa_actual')
                ->pluck('total', 'etapa_actual'),
            'proyectos_por_estado'  => $institucion->proyectos()
                ->selectRaw('estado, COUNT(*) as total')
                ->groupBy('estado')
                ->pluck('total', 'estado')
        ];

        return response()->json([
            'institucion'  => $institucion,
            'estadisticas' => $estadisticas
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
            'activo'  => $institucion->activo
        ]);
    }

    /**
     * 👇 NUEVO: catálogos para el formulario de institución
     * Devuelve solo activos: { id, nombre } de Modalidades y Tipos de institución
     * (sin tocar tus endpoints /admin).
     */

public function getCatalogos()
{
    return response()->json([
        'modalidades'       => Modalidad::where('activo', true)
                                ->orderBy('nombre')
                                ->get(['id','nombre']),
        'tipos_institucion' => TipoInstitucion::where('activo', true)
                                ->orderBy('nombre')
                                ->get(['id','nombre']),
    ]);
}
}
