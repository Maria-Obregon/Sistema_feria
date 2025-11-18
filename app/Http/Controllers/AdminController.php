<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Categoria;
use App\Models\Modalidad;
use App\Models\Nivel;
use App\Models\Usuario;
use App\Models\Institucion;
use App\Models\Proyecto;
use App\Models\Feria;
use App\Models\TipoInstitucion;
use App\Models\Regional;
use App\Models\Circuito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /** ====== Dashboard / utilidades ====== */
   public function stats()
{
    $usuarios      = class_exists(Usuario::class)     ? Usuario::count()     : 0;
    $instituciones = class_exists(Institucion::class) ? Institucion::count() : 0;
    $proyectos     = class_exists(Proyecto::class)     ? Proyecto::count()     : 0;

    // por ahora, total de ferias (no “activas”)
    $feriasActivas = class_exists(Feria::class) ? Feria::count() : 0;

    return response()->json([
        'usuarios'       => $usuarios,
        'instituciones'  => $instituciones,
        'proyectos'      => $proyectos,
        'ferias_activas' => $feriasActivas,
    ]);
}

    /** ====== Catálogos ====== */

    // ---- Modalidades
    public function listModalidades(Request $request)
{
    // opcional: filtrar por nivel_id
    $q = Modalidad::query()
        ->with('nivel:id,nombre')
        ->when($request->filled('nivel_id'), fn($qq)=>$qq->where('nivel_id', $request->integer('nivel_id')))
        ->orderBy('nombre');

    return response()->json($q->get());
}

public function createModalidad(Request $request)
{
    $data = $request->validate([
        'nombre'   => ['required','string','max:120',
            Rule::unique('modalidades', 'nombre')
                ->where(fn($q) => $q->where('nivel_id', $request->input('nivel_id')))
        ],
        'nivel_id' => ['required','integer', Rule::exists('niveles','id')],
        'activo'   => ['boolean'],
    ]);

    $row = Modalidad::create([
        'nombre'   => $data['nombre'],
        'nivel_id' => $data['nivel_id'],
        'activo'   => $data['activo'] ?? true,
    ]);

    return response()->json(['mensaje' => 'Modalidad creada', 'data' => $row->load('nivel:id,nombre')], 201);
}

public function updateModalidad(Request $request, Modalidad $modalidad)
{
    $data = $request->validate([
        'nombre'   => ['required','string','max:120',
            Rule::unique('modalidades', 'nombre')
                ->ignore($modalidad->id)
                ->where(fn($q) => $q->where('nivel_id', $request->input('nivel_id', $modalidad->nivel_id)))
        ],
        'nivel_id' => ['required','integer', Rule::exists('niveles','id')],
        'activo'   => ['boolean'],
    ]);

    $modalidad->update($data);

    return response()->json(['mensaje' => 'Modalidad actualizada', 'data' => $modalidad->load('nivel:id,nombre')]);
}
    public function destroyModalidad(Modalidad $modalidad)
    {
        $modalidad->delete();
        return response()->json(['mensaje' => 'Modalidad eliminada']);
    }

    // ---- Áreas
    public function listAreas()
    {
        return response()->json(Area::orderBy('nombre')->get());
    }
    public function createArea(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:100','unique:areas,nombre'],
            'activo' => ['boolean'],
        ]);
        $row = Area::create([
            'nombre' => $data['nombre'],
            'activo' => $data['activo'] ?? true,
        ]);
        return response()->json(['mensaje' => 'Área creada', 'data' => $row], 201);
    }
    public function updateArea(Request $request, Area $area)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:100', Rule::unique('areas','nombre')->ignore($area->id)],
            'activo' => ['boolean'],
        ]);
        $area->update($data);
        return response()->json(['mensaje' => 'Área actualizada', 'data' => $area]);
    }
    public function destroyArea(Area $area)
    {
        $area->delete();
        return response()->json(['mensaje' => 'Área eliminada']);
    }

    // ---- Categorías (nivel viene de la tabla niveles.nombre)
    public function listCategorias()
    {
        return response()->json(Categoria::orderBy('nombre')->get());
    }
    public function createCategoria(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:100','unique:categorias,nombre'],
            'nivel'  => ['required', Rule::exists('niveles','nombre')],
        ]);
        $row = Categoria::create($data);
        return response()->json(['mensaje' => 'Categoría creada', 'data' => $row], 201);
    }
    public function updateCategoria(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:100', Rule::unique('categorias','nombre')->ignore($categoria->id)],
            'nivel'  => ['required', Rule::exists('niveles','nombre')],
        ]);
        $categoria->update($data);
        return response()->json(['mensaje' => 'Categoría actualizada', 'data' => $categoria]);
    }
    public function destroyCategoria(Categoria $categoria)
    {
        $categoria->delete();
        return response()->json(['mensaje' => 'Categoría eliminada']);
    }

    // ---- Tipos de Institución
    public function listTiposInstitucion()
    {
        return response()->json(TipoInstitucion::orderBy('nombre')->get());
    }
    public function createTipoInstitucion(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:120','unique:tipos_institucion,nombre'],
            'activo' => ['boolean'],
        ]);
        $row = TipoInstitucion::create([
            'nombre' => $data['nombre'],
            'activo' => $data['activo'] ?? true,
        ]);
        return response()->json(['mensaje' => 'Tipo creado', 'data' => $row], 201);
    }
    public function updateTipoInstitucion(Request $request, TipoInstitucion $tipo)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:120', Rule::unique('tipos_institucion','nombre')->ignore($tipo->id)],
            'activo' => ['boolean'],
        ]);
        $tipo->update($data);
        return response()->json(['mensaje' => 'Tipo actualizado', 'data' => $tipo]);
    }
    public function destroyTipoInstitucion(TipoInstitucion $tipo)
    {
        $tipo->delete();
        return response()->json(['mensaje' => 'Tipo eliminado']);
    }

    // ---- Niveles (catálogo libre)
    public function listNiveles()
    {
        return response()->json(Nivel::orderBy('nombre')->get());
    }
    public function createNivel(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:120','unique:niveles,nombre'],
            'activo' => ['boolean'],
        ]);
        $row = Nivel::create([
            'nombre' => $data['nombre'],
            'activo' => $data['activo'] ?? true,
        ]);
        return response()->json(['mensaje' => 'Nivel creado', 'data' => $row], 201);
    }
    public function updateNivel(Request $request, Nivel $nivel)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:120', Rule::unique('niveles','nombre')->ignore($nivel->id)],
            'activo' => ['boolean'],
        ]);
        $nivel->update($data);
        return response()->json(['mensaje' => 'Nivel actualizado', 'data' => $nivel]);
    }
    public function destroyNivel(Nivel $nivel)
    {
        $nivel->delete();
        return response()->json(['mensaje' => 'Nivel eliminado']);
    }

    // ===== REGIONES =====
public function listRegionales()
{
    return response()->json(Regional::orderBy('nombre')->get());
}
public function createRegional(Request $request)
{
    $data = $request->validate([
        'nombre' => ['required','string','max:150','unique:regionales,nombre'],
        'activo' => ['boolean'],
    ]);
    $row = Regional::create([
        'nombre' => $data['nombre'],
        'activo' => $data['activo'] ?? true,
    ]);
    return response()->json(['mensaje' => 'Regional creada', 'data' => $row], 201);
}
public function updateRegional(Request $request, Regional $regional)
{
    $data = $request->validate([
        'nombre' => ['required','string','max:150', Rule::unique('regionales','nombre')->ignore($regional->id)],
        'activo' => ['boolean'],
    ]);
    $regional->update($data);
    return response()->json(['mensaje' => 'Regional actualizada', 'data' => $regional]);
}
public function destroyRegional(Regional $regional)
{
    $regional->delete();
    return response()->json(['mensaje' => 'Regional eliminada']);
}

// ===== CIRCUITOS =====
public function listCircuitos(Request $request)
{
    // opcional: filtrar por regional_id
    $q = Circuito::query()
        ->when($request->filled('regional_id'), fn($qq)=>$qq->where('regional_id',$request->integer('regional_id')))
        ->with('regional:id,nombre')
        ->orderBy('nombre');

    return response()->json($q->get());
}
public function createCircuito(Request $request)
{
    $data = $request->validate([
        'nombre'      => ['required','string','max:150','unique:circuitos,nombre'],
        'codigo'      => ['nullable','string','max:50','unique:circuitos,codigo'],
        'regional_id' => ['required','integer', Rule::exists('regionales','id')],
        'activo'      => ['boolean'],
    ]);
    $row = Circuito::create([
        'nombre'      => $data['nombre'],
        'codigo'      => $data['codigo'] ?? null,
        'regional_id' => $data['regional_id'],
        'activo'      => $data['activo'] ?? true,
    ]);
    return response()->json(['mensaje' => 'Circuito creado', 'data' => $row->load('regional:id,nombre')], 201);
}
public function updateCircuito(Request $request, Circuito $circuito)
{
    $data = $request->validate([
        'nombre'      => ['required','string','max:150', Rule::unique('circuitos','nombre')->ignore($circuito->id)],
        'codigo'      => ['nullable','string','max:50',  Rule::unique('circuitos','codigo')->ignore($circuito->id)],
        'regional_id' => ['required','integer', Rule::exists('regionales','id')],
        'activo'      => ['boolean'],
    ]);
    $circuito->update($data);
    return response()->json(['mensaje' => 'Circuito actualizado', 'data' => $circuito->load('regional:id,nombre')]);
}
public function destroyCircuito(Circuito $circuito)
{
    $circuito->delete();
    return response()->json(['mensaje' => 'Circuito eliminado']);
}
}