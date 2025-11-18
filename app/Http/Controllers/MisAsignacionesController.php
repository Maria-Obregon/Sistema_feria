<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MisAsignacionesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $usuarioId = $user->id;

        // Importante: usamos la tabla plural "asignaciones_jueces"
        $rows = DB::table('jueces as j')
            ->join('asignacion_juez as aj', 'aj.juez_id', '=', 'j.id')
            ->join('proyectos as p', 'p.id', '=', 'aj.proyecto_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->leftJoin('modalidades as m', 'm.id', '=', 'p.modalidad_id')
            ->leftJoin('etapas as e', 'e.id', '=', 'aj.etapa_id')
            ->where('j.usuario_id', $usuarioId)
            ->whereNull('aj.finalizada_at') // solo asignaciones pendientes
            ->orderByDesc('aj.id')
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
                // ids planos (lo que necesita el front para navegar)
                'id' => (int) $r->asignacion_id,
                'proyecto_id' => (int) $r->proyecto_id,
                'etapa_id' => (int) $r->etapa_id,

                // objeto anidado para mostrar en tabla
                'proyecto' => [
                    'id' => (int) $r->proyecto_id,
                    'titulo' => $r->proyecto_titulo,
                    'categoria' => $r->categoria_nombre,
                    'modalidad' => $r->modalidad_nombre,
                    'etapa' => $r->etapa_nombre,
                ],

                'tipo_eval' => $r->tipo_eval,
                'finalizada' => ! is_null($r->finalizada_at),
            ];
        });

        return response()->json([
            'data' => $data->values(),
            'meta' => ['count' => $data->count()],
        ]);
    }
}
