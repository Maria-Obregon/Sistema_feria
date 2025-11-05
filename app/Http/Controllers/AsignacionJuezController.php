<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\AsignacionJuez;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AsignacionJuezController extends Controller
{
    // POST /api/proyectos/{proyecto}/asignar-jueces
    public function assign(Request $request, Proyecto $proyecto)
    {
        $data = $request->validate([
            'etapa_id'    => 'required|integer',                    // adapta si tienes catálogo
            'tipo_eval'   => ['required', Rule::in(['escrito','exposicion','integral'])],
            'jueces'      => 'required|array|min:1',
            'jueces.*.id' => 'required|exists:jueces,id',
        ]);

        DB::transaction(function() use ($proyecto, $data) {
            foreach ($data['jueces'] as $j) {
                AsignacionJuez::firstOrCreate(
                    [
                        'proyecto_id' => $proyecto->id,
                        'juez_id'     => $j['id'],
                        'etapa_id'    => $data['etapa_id'],
                    ],
                    ['tipo_eval' => $data['tipo_eval']]
                );
            }
        });

        $asigs = $proyecto->asignacionesJuez()->with('juez')->get();

        return response()->json([
            'message'       => 'Jueces asignados correctamente',
            'asignaciones'  => $asigs
        ]);
    }

    // GET /api/proyectos/{proyecto}/asignaciones
    public function listByProyecto(Proyecto $proyecto)
    {
        return $proyecto->asignacionesJuez()->with('juez')->get();
    }

    // DELETE /api/asignaciones-jueces/{id}
    public function unassign($id)
    {
        $asig = AsignacionJuez::findOrFail($id);
        $asig->delete();
        return response()->json(['message' => 'Asignación eliminada']);
    }
}
