<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Modalidad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    public function index()
    {
        return Categoria::with('modalidades:id,nombre')->orderBy('nombre')->get();
    }

    public function show(Categoria $categoria)
    {
        return $categoria->load('modalidades:id,nombre');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'          => ['required','string','max:100','unique:categorias,nombre'],
            'nivel'           => ['required','string','max:120'],
            'modalidades_ids' => ['nullable','array'],
            'modalidades_ids.*' => ['integer', Rule::exists('modalidades','id')],
        ]);

        $cat = Categoria::create([
            'nombre' => $data['nombre'],
            'nivel'  => $data['nivel'],
        ]);

        if (!empty($data['modalidades_ids'])) {
            $cat->modalidades()->sync($data['modalidades_ids']);
        }

        return response()->json($cat->load('modalidades:id,nombre'), 201);
    }

    public function porModalidad(Modalidad $modalidad)
{
    $cats = Categoria::query()
        ->whereHas('modalidades', fn($q) => $q->where('modalidad_id', $modalidad->id))
        ->orderBy('nombre')
        ->get(['id','nombre','nivel']);

    return response()->json($cats);
}

    public function update(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'nombre'          => ['required','string','max:100', Rule::unique('categorias','nombre')->ignore($categoria->id)],
            'nivel'           => ['required','string','max:120'],
            'modalidades_ids' => ['nullable','array'],
            'modalidades_ids.*' => ['integer', Rule::exists('modalidades','id')],
        ]);

        $categoria->update([
            'nombre' => $data['nombre'],
            'nivel'  => $data['nivel'],
        ]);

        if ($request->has('modalidades_ids')) {
            $categoria->modalidades()->sync($data['modalidades_ids'] ?? []);
        }

        return response()->json($categoria->load('modalidades:id,nombre'));
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->modalidades()->detach();
        $categoria->delete();
        return response()->json(['mensaje' => 'Categoría eliminada']);
    }

    public function syncModalidades(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'modalidades_ids' => ['required','array'],
            'modalidades_ids.*' => ['integer', Rule::exists('modalidades','id')],
        ]);

        $categoria->modalidades()->sync($data['modalidades_ids']);
        return response()->json($categoria->load('modalidades:id,nombre'));
    }
}
