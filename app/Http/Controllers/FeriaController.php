<?php

namespace App\Http\Controllers;

use App\Models\Feria;
use Illuminate\Http\Request;

class FeriaController extends Controller
{
  public function index(Request $r){
    $q = Feria::with(['institucion','circuito','regional']);
    if ($r->filled('tipo')) $q->where('tipo',$r->tipo);
    return response()->json($q->orderByDesc('id')->paginate(15));
  }
  public function store(Request $r){
    $data = $r->validate([
      'nombre'=>'required|string|max:255',
      'tipo'=>'required|in:institucional,circuital,regional',
      'institucion_id'=>'nullable|exists:instituciones,id',
      'circuito_id'=>'nullable|exists:circuitos,id',
      'regional_id'=>'nullable|exists:regionales,id',
      'fecha_inicio'=>'nullable|date', 'fecha_fin'=>'nullable|date|after_or_equal:fecha_inicio',
    ]);
    // regla: según tipo, exigir el dueño correcto
    if($data['tipo']==='institucional' && empty($data['institucion_id'])) abort(422,'institucion_id requerido');
    if($data['tipo']==='circuital' && empty($data['circuito_id'])) abort(422,'circuito_id requerido');
    if($data['tipo']==='regional' && empty($data['regional_id'])) abort(422,'regional_id requerido');

    $feria = Feria::create($data);
    return response()->json($feria,201);
  }
  public function update(Request $r, Feria $feria){
    $feria->update($r->validate([
      'nombre'=>'sometimes|string|max:255',
      'fecha_inicio'=>'nullable|date','fecha_fin'=>'nullable|date|after_or_equal:fecha_inicio',
      'estado'=>'sometimes|in:borrador,activa,cerrada'
    ]));
    return response()->json($feria->fresh());
  }
  public function destroy(Feria $feria){
    if($feria->proyectos()->exists()) abort(422,'No puede eliminar una feria con proyectos.');
    $feria->delete(); return response()->json(['ok'=>true]);
  }
}
