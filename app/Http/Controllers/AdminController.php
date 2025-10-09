<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Categoria;
use App\Models\Modalidad;
use App\Models\Nivel;
use App\Models\TipoInstitucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /** ====== Dashboard / utilidades ====== */
    public function stats()
    {
        Log::info('AdminController@stats llamado por: ' . optional(auth()->user())->email);

        $usuarios = class_exists(\App\Models\Usuario::class) ? \App\Models\Usuario::count() : 0;
        $instituciones = class_exists(\App\Models\Institucion::class) ? \App\Models\Institucion::count() : 0;
        $proyectos = class_exists(\App\Models\Proyecto::class) ? \App\Models\Proyecto::count() : 0;
        $feriasActivas = class_exists(\App\Models\Feria::class) ? \App\Models\Feria::where('activa', true)->count() : 0;

        return response()->json([
            'usuarios'       => $usuarios,
            'instituciones'  => $instituciones,
            'proyectos'      => $proyectos,
            'ferias_activas' => $feriasActivas,
        ]);
    }

    /** ====== Catálogos ====== */

    // ---- Modalidades
    public function listModalidades()
    {
        return response()->json(Modalidad::orderBy('nombre')->get());
    }
    public function createModalidad(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:120','unique:modalidades,nombre'],
            'activo' => ['boolean'],
        ]);
        $row = Modalidad::create([
            'nombre' => $data['nombre'],
            'activo' => $data['activo'] ?? true,
        ]);
        return response()->json(['mensaje' => 'Modalidad creada', 'data' => $row], 201);
    }
    public function updateModalidad(Request $request, Modalidad $modalidad)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:120', Rule::unique('modalidades','nombre')->ignore($modalidad->id)],
            'activo' => ['boolean'],
        ]);
        $modalidad->update($data);
        return response()->json(['mensaje' => 'Modalidad actualizada', 'data' => $modalidad]);
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
}
