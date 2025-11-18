<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MisCalificacionesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $usuarioId = $user->id;

        // Traer solo asignaciones finalizadas
        $rows = DB::table('jueces as j')
            ->join('asignacion_juez as aj', 'aj.juez_id', '=', 'j.id')
            ->join('proyectos as p', 'p.id', '=', 'aj.proyecto_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->leftJoin('modalidades as m', 'm.id', '=', 'p.modalidad_id')
            ->leftJoin('etapas as e', 'e.id', '=', 'aj.etapa_id')
            ->where('j.usuario_id', $usuarioId)
            ->whereNotNull('aj.finalizada_at') // solo finalizadas
            ->orderByDesc('aj.finalizada_at')
            ->selectRaw('
                aj.id           as asignacion_id,
                aj.proyecto_id  as proyecto_id,
                aj.etapa_id     as etapa_id,
                aj.tipo_eval,
                aj.finalizada_at,
                p.titulo        as proyecto_titulo,
                c.nombre        as categoria_nombre,
                m.nombre        as modalidad_nombre,
                e.nombre        as etapa_nombre
            ')
            ->get();

        $data = $rows->map(function ($r) {
            return [
                'id' => (int) $r->asignacion_id,
                'proyecto_id' => (int) $r->proyecto_id,
                'etapa_id' => (int) $r->etapa_id,

                'proyecto' => [
                    'id' => (int) $r->proyecto_id,
                    'titulo' => $r->proyecto_titulo,
                    'categoria' => $r->categoria_nombre,
                    'modalidad' => $r->modalidad_nombre,
                    'etapa' => $r->etapa_nombre,
                ],

                'tipo_eval' => $r->tipo_eval,
                'finalizada_at' => $r->finalizada_at,
            ];
        });

        return response()->json([
            'data' => $data->values(),
            'meta' => ['count' => $data->count()],
        ]);
    }
}
