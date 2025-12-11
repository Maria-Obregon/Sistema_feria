<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstitucionController extends Controller
{
    // GET /api/instituciones
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Institucion::with(['circuito.regional']);

        // 1. SEGURIDAD: Si es Comité, SOLO ve su propia institución
        if ($user->hasRole('comite_institucional')) {
            $q->where('id', $user->institucion_id);
        }

        // Filtros normales (para admin)
        $q->when($request->filled('buscar'), function ($query) use ($request) {
            $b = $request->buscar;
            $query->where(function ($w) use ($b) {
                $w->where('nombre', 'like', "%{$b}%")
                  ->orWhere('codigo', 'like', "%{$b}%")
                  ->orWhere('codigo_presupuestario', 'like', "%{$b}%");
            });
        });

        return $q->paginate($request->integer('per_page', 10));
    }

    // POST /api/instituciones (CREAR)
    public function store(Request $request)
    {
        if ($request->user()->hasRole('comite_institucional')) {
            return response()->json(['message' => 'No tienes permisos para crear instituciones.'], 403);
        }

        $data = $request->validate([
            'nombre'                => 'required|string|max:200',
            'codigo'                => 'required|string|max:50|unique:instituciones,codigo',
            'regional_id'           => 'required|exists:regionales,id',
            'circuito_id'           => 'required|exists:circuitos,id',
            'telefono'              => 'nullable|string|max:20',
            'modalidad'             => 'required|string',
            'tipo'                  => 'required|string|in:publica,privada,subvencionada',
            'email'                 => 'nullable|email|max:100',
            // ... otros campos ...
        ]);

        // Mapeo de código presupuestario si usas nombres distintos
        $data['codigo_presupuestario'] = $data['codigo'];

        $institucion = Institucion::create($data);
        return response()->json(['message' => 'Institución creada', 'data' => $institucion], 201);
    }

    // PUT /api/instituciones/{institucion} (EDITAR)
    public function update(Request $request, Institucion $institucion)
    {
        // 3. SEGURIDAD: El Comité solo puede editar SU propia institución
        $user = $request->user();
        if ($user->hasRole('comite_institucional') && $user->institucion_id !== $institucion->id) {
            return response()->json(['message' => 'No puedes editar otra institución.'], 403);
        }

        $data = $request->validate([
            'nombre'      => 'required|string|max:200',
            'telefono'    => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:100',
            // El comité usualmente NO debería cambiar su código ni su regional/circuito, 
            // pero si quieres permitirlo, valida esos campos aquí también.
            // Para este ejemplo, permitimos editar datos de contacto y nombre:
        ]);

        $institucion->update($data);
        return response()->json(['message' => 'Institución actualizada', 'data' => $institucion]);
    }

    // DELETE /api/instituciones/{institucion}
    public function destroy(Request $request, Institucion $institucion)
    {
        // 4. SEGURIDAD: El Comité NO puede eliminar instituciones
        if ($request->user()->hasRole('comite_institucional')) {
            return response()->json(['message' => 'No tienes permisos para eliminar instituciones.'], 403);
        }

        $institucion->delete();
        return response()->json(['message' => 'Institución eliminada']);
    }
}