<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\AsignacionJuez;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AsignacionJuezController extends Controller
{
    /**
     * POST /api/proyectos/{proyecto}/asignar-jueces
     */
    public function assign(Request $request, Proyecto $proyecto)
    {
        $data = $request->validate([
            'etapa_id'          => ['required','integer','between:1,9'], // ajusta rango a tu catálogo
            'tipo_eval'         => ['required', Rule::in(['escrito','exposicion','integral'])],
            'jueces'            => ['required','array','min:1'],
            'jueces.*.id'       => ['required','integer','exists:jueces,id'],
        ]);

        DB::transaction(function () use ($proyecto, $data) {
            foreach ($data['jueces'] as $j) {
                AsignacionJuez::updateOrCreate(
                    [
                        'proyecto_id' => $proyecto->id,
                        'juez_id'     => (int)$j['id'],
                        'etapa_id'    => (int)$data['etapa_id'],
                    ],
                    [
                        'tipo_eval'   => $data['tipo_eval'],
                    ]
                );
            }
        });

        $asigs = $proyecto->asignacionesJuez()
            ->with(['juez:id,nombre,cedula,correo,telefono'])
            ->orderBy('etapa_id')
            ->get();

        return response()->json([
            'message'      => 'Jueces asignados correctamente',
            'asignaciones' => $asigs,
        ]);
    }

    /**
     * GET /api/proyectos/{proyecto}/asignaciones
     */
    public function listByProyecto(Proyecto $proyecto)
    {
        return $proyecto->asignacionesJuez()
            ->with(['juez:id,nombre,cedula,correo,telefono'])
            ->orderBy('etapa_id')
            ->get();

    }

    /**
     * DELETE /api/asignaciones-jueces/{id}
     */
    public function unassign($id)
    {
        $asig = AsignacionJuez::findOrFail($id);
        $asig->delete();

        return response()->json(['message' => 'Asignación eliminada']);
    }
}
