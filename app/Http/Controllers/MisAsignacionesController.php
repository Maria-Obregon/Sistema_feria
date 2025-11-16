<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MisAsignacionesController extends Controller
{
    public function index(Request $request)
    {
        $usuarioId = $request->user()->id;

        $rows = DB::table('jueces as j')
            ->join('asignaciones_jueces as aj', 'aj.juez_id', '=', 'j.id')
            ->join('proyectos as p', 'p.id', '=', 'aj.proyecto_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->leftJoin('modalidades as m', 'm.id', '=', 'p.modalidad_id')
            ->leftJoin('etapas as e', 'e.id', '=', 'aj.etapa_id')
            ->where('j.usuario_id', $usuarioId)
            // ->whereNull('aj.finalizada_at')
            ->orderByDesc('aj.id')
            ->selectRaw('
                aj.id as asignacion_id, aj.tipo_eval, aj.finalizada_at,
                p.id as proyecto_id, p.titulo,
                c.nombre as categoria, m.nombre as modalidad,
                e.nombre as etapa
            ')
            ->get();

        $data = $rows->map(function ($r) {
            return [
                'id' => (int) $r->asignacion_id,
                'proyecto' => [
                    'id' => (int) $r->proyecto_id,
                    'titulo' => $r->titulo,
                    'categoria' => $r->categoria,
                    'modalidad' => $r->modalidad,
                    'etapa' => $r->etapa,
                ],
                'tipo_eval' => $r->tipo_eval,
                'finalizada' => ! is_null($r->finalizada_at),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => ['count' => $data->count()],
        ]);
    }
}
