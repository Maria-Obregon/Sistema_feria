<?php

namespace App\Http\Controllers;

use App\Models\Juez;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JuezController extends Controller
{
    // GET /api/jueces
    public function index(Request $request)
    {
    
        $q = Juez::query()
            ->when($request->filled('buscar'), function($qq) use ($request) {
                $b = $request->get('buscar');
                $qq->where(function($w) use ($b) {
                    $w->where('nombre','like',"%{$b}%")
                      ->orWhere('cedula','like',"%{$b}%")
                      ->orWhere('correo','like',"%{$b}%");
                });
            })
            ->when($request->filled('area_id'), fn($qq)=>$qq->where('area_id',$request->integer('area_id')))
            ->withCount('asignaciones as proyectos_count')
            ->orderBy('nombre');

        if ($request->filled('exclude_juez_id')) {
        $juezId = $request->integer('exclude_juez_id');
        
        $q->whereDoesntHave('asignacionesJuez', function($query) use ($juezId) {
            $query->where('juez_id', $juezId);
        });
    }


        if ($request->boolean('con_proyectos')) {
            $q->with(['asignaciones' => function($w){
                $w->with([
                    'proyecto:id,titulo,categoria_id,institucion_id', 
                    
                    'proyecto.categoria:id,nombre',
                    
                    'proyecto.institucion:id,nombre', 
                    
                    'proyecto.estudiantes:id,nombre,apellidos,cedula,nivel,seccion' 
                ])->orderBy('etapa_id');
            }]);
        }

        $res = $q->paginate($request->integer('per_page', 10));

        if ($request->boolean('con_proyectos')) {
            $res->getCollection()->transform(function ($juez) {
                $juez->proyectos = $juez->asignaciones->map(function ($a) {
                    if (!$a->proyecto) return null;

                    return [
                        'id'        => $a->proyecto_id,
                        'titulo'    => $a->proyecto->titulo ?? '—',
                        'categoria' => $a->proyecto->categoria->nombre ?? null,
                        'etapa_id'  => (int)$a->etapa_id,
                        'tipo_eval' => $a->tipo_eval,
                        'asig_id'   => $a->id,
                        
                        'institucion' => $a->proyecto->institucion ? [
                            'nombre' => $a->proyecto->institucion->nombre
                        ] : null,

                        'estudiantes' => $a->proyecto->estudiantes->map(function($e){
                            return [
                                'id'        => $e->id,
                                'nombre'    => $e->nombre,
                                'apellidos' => $e->apellidos,
                                'cedula'    => $e->cedula,
                                'nivel'     => $e->nivel,
                                'seccion'   => $e->seccion,
                            ];
                        }),
                    ];
                })->filter()->values(); 
                unset($juez->asignaciones);
                return $juez;
            });
        }

        return $res;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'          => 'required|string|max:200',
            'cedula'          => 'required|string|max:20|unique:jueces,cedula',
            'sexo'            => ['nullable', Rule::in(['M','F','O'])],
            'telefono'        => 'nullable|string|max:30',
            'correo'          => 'nullable|email|max:120|unique:jueces,correo',
            'grado_academico' => 'nullable|string|max:120',
            'area_id'         => 'nullable|exists:areas,id',
            'usuario_id'      => 'nullable|exists:usuarios,id',
        ]);

        $juez = Juez::create($data);
        return response()->json($juez, 201);
    }

    public function update(Request $request, Juez $juez)
    {
        $data = $request->validate([
            'nombre'          => 'required|string|max:200',
            'cedula'          => ['required','string','max:20', Rule::unique('jueces','cedula')->ignore($juez->id)],
            'sexo'            => ['nullable', Rule::in(['M','F','O'])],
            'telefono'        => 'nullable|string|max:30',
            'correo'          => ['nullable','email','max:120', Rule::unique('jueces','correo')->ignore($juez->id)],
            'grado_academico' => 'nullable|string|max:120',
            'area_id'         => 'nullable|exists:areas,id',
            'usuario_id'      => 'nullable|exists:usuarios,id',
        ]);

        $juez->update($data);
        return response()->json($juez);
    }

    public function destroy(Juez $juez)
    {
        if ($juez->asignaciones()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: tiene asignaciones.'], 422);
        }
        $juez->delete();
        return response()->json(['message' => 'Juez eliminado']);
    }
}
