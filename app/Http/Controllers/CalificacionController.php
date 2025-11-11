<?php

namespace App\Http\Controllers;

use App\Models\Criterio;
use App\Models\Juez;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CalificacionController extends Controller
{
    public function index(Request $r)
    {
        $perPage = max(1, min((int) $r->query('per_page', 50), 200));
        $proyId = $r->query('proyecto_id');
        $juezId = $r->query('juez_id');
        $etapaId = $r->query('etapa_id');

        $q = DB::table('calificaciones as c')
            ->select([
                'c.id', 'c.asignaciones_juez_id', 'c.criterio_id', 'c.puntaje', 'c.comentario',
                'k.nombre as criterio_nombre', 'k.peso', 'k.max_puntos',
                'r.tipo_eval',
                'aj.proyecto_id', 'aj.juez_id', 'aj.etapa_id',
            ])
            ->join('criterios as k', 'k.id', '=', 'c.criterio_id')
            ->join('rubricas as r', 'r.id', '=', 'k.rubrica_id')
            ->join('asignaciones_jueces as aj', 'aj.id', '=', 'c.asignaciones_juez_id');

        if ($proyId) {
            $q->where('aj.proyecto_id', (int) $proyId);
        }
        if ($juezId) {
            $q->where('aj.juez_id', (int) $juezId);
        }
        if ($etapaId) {
            $q->where('aj.etapa_id', (int) $etapaId);
        }

        $q->orderBy('c.asignaciones_juez_id')->orderBy('c.criterio_id');

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'asignacion_id' => ['required', 'integer', 'exists:asignaciones_jueces,id'],
            'criterio_id' => ['required', 'integer', 'exists:criterios,id'],
            'puntaje' => ['required', 'numeric', 'min:0'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        $asignacionId = (int) $data['asignacion_id'];
        $criterio = Criterio::query()->with('rubrica')->findOrFail((int) $data['criterio_id']);

        if ($data['puntaje'] > (float) $criterio->max_puntos) {
            return response()->json([
                'message' => 'El puntaje excede el máximo permitido para el criterio.',
                'max_puntos' => (float) $criterio->max_puntos,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $asignacion = DB::table('asignaciones_jueces')->where('id', $asignacionId)->first();
        if (! $asignacion) {
            return response()->json(['message' => 'Asignación no encontrada.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $proyecto = Proyecto::findOrFail($asignacion->proyecto_id);
        $juez = Juez::with(['usuario.institucion', 'instituciones'])->findOrFail($asignacion->juez_id);
        $institucionJuez = $juez->usuario?->institucion ?? $juez->instituciones()->first();

        $ETAPA_INSTITUCIONAL = 1;
        $ETAPA_CIRCUITAL = 2;
        $ETAPA_REGIONAL = 3;

        if (in_array((int) $asignacion->etapa_id, [$ETAPA_CIRCUITAL, $ETAPA_REGIONAL], true)) {
            if ($institucionJuez && (int) $institucionJuez->id === (int) $proyecto->institucion_id) {
                return response()->json([
                    'message' => 'Conflicto de interés por etapa: no puede calificar proyectos de su misma institución en esta etapa.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $row = DB::table('calificaciones')
            ->where('asignaciones_juez_id', $asignacionId)
            ->where('criterio_id', (int) $data['criterio_id'])
            ->first();

        if ($row) {
            DB::table('calificaciones')
                ->where('id', $row->id)
                ->update([
                    'puntaje' => $data['puntaje'],
                    'comentario' => $data['comentario'] ?? null,
                    'updated_at' => now(),
                ]);
            $id = $row->id;
        } else {
            $id = DB::table('calificaciones')->insertGetId([
                'asignaciones_juez_id' => $asignacionId,
                'criterio_id' => (int) $data['criterio_id'],
                'puntaje' => $data['puntaje'],
                'comentario' => $data['comentario'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $saved = DB::table('calificaciones')->where('id', $id)->first();

        return response()->json([
            'id' => $saved->id,
            'asignaciones_juez_id' => $saved->asignaciones_juez_id,
            'criterio_id' => $saved->criterio_id,
            'puntaje' => (float) $saved->puntaje,
            'comentario' => $saved->comentario,
        ], Response::HTTP_CREATED);
    }

    public function consolidar(Request $r)
    {
        $data = $r->validate([
            'proyecto_id' => ['required', 'integer', 'exists:proyectos,id'],
            'etapa_id' => ['required', 'integer'],
        ]);

        $proyectoId = (int) $data['proyecto_id'];
        $etapaId = (int) $data['etapa_id'];

        $rows = DB::table('calificaciones as c')
            ->select([
                'c.puntaje', 'k.max_puntos', 'k.peso', 'r.tipo_eval',
                'c.criterio_id', 'k.nombre as criterio_nombre',
            ])
            ->join('criterios as k', 'k.id', '=', 'c.criterio_id')
            ->join('rubricas as r', 'r.id', '=', 'k.rubrica_id')
            ->join('asignaciones_jueces as aj', 'aj.id', '=', 'c.asignaciones_juez_id')
            ->where('aj.proyecto_id', $proyectoId)
            ->where('aj.etapa_id', $etapaId)
            ->get();

        $sumEscrito = 0.0;
        $sumExpo = 0.0;
        $detalles = [];

        foreach ($rows as $row) {
            $den = max(1.0, (float) $row->max_puntos);
            $peso = (float) $row->peso;
            $contrib = ((float) $row->puntaje / $den) * $peso * 100.0;
            $detalles[] = [
                'criterio_id' => (int) $row->criterio_id,
                'nombre' => $row->criterio_nombre,
                'tipo_eval' => $row->tipo_eval,
                'puntaje' => (float) $row->puntaje,
                'max_puntos' => (float) $row->max_puntos,
                'peso' => $peso,
                'contribucion' => $contrib,
            ];
            if ($row->tipo_eval === 'escrito') {
                $sumEscrito += $contrib;
            }
            if ($row->tipo_eval === 'exposicion') {
                $sumExpo += $contrib;
            }
        }

        $nota_escrito = round($sumEscrito, 2);
        $nota_exposicion = round($sumExpo, 2);
        $nota_final = round(0.5 * $nota_escrito + 0.5 * $nota_exposicion, 2);

        return response()->json([
            'proyecto_id' => $proyectoId,
            'etapa_id' => $etapaId,
            'nota_escrito' => $nota_escrito,
            'nota_exposicion' => $nota_exposicion,
            'nota_final' => $nota_final,
            'criterios' => $detalles,
        ], Response::HTTP_OK);
    }
}
