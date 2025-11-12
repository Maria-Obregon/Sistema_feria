<?php

namespace App\Http\Controllers;

use App\Models\Criterio;
use App\Models\Etapa;
use App\Models\Juez;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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

        // Visibilidad: si es juez, solo sus asignaciones
        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('juez')) {
            $juez = Juez::where('usuario_id', $user->id)->first();
            if (! $juez) {
                return response()->json(['message' => 'No autorizado'], Response::HTTP_FORBIDDEN);
            }
            $q->where('aj.juez_id', $juez->id);
        }

        $q->orderBy('c.asignaciones_juez_id')->orderBy('c.criterio_id');

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'asignacion_id' => ['required', 'integer', 'exists:asignaciones_jueces,id'],
            'criterio_id' => ['required', 'integer', 'exists:criterios,id'],
            'puntaje' => ['required'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        $asignacionId = (int) $data['asignacion_id'];
        $criterio = Criterio::query()->with('rubrica')->findOrFail((int) $data['criterio_id']);

        // Validación según modo de rúbrica
        $modo = $criterio->rubrica->modo ?? 'por_criterio';
        if ($modo === 'escala_1_5') {
            if (! ctype_digit((string) $data['puntaje'])) {
                return response()->json(['message' => 'El puntaje debe ser un entero entre 1 y 5.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $p = (int) $data['puntaje'];
            if ($p < 1 || $p > 5) {
                return response()->json(['message' => 'El puntaje debe estar entre 1 y 5.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $data['puntaje'] = $p;
        } else {
            // Modo por_criterio (actual): validar contra max_puntos
            if (! is_numeric($data['puntaje']) || (float) $data['puntaje'] < 0) {
                return response()->json(['message' => 'El puntaje debe ser numérico y no negativo.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ((float) $data['puntaje'] > (float) $criterio->max_puntos) {
                return response()->json([
                    'message' => 'El puntaje excede el máximo permitido para el criterio.',
                    'max_puntos' => (float) $criterio->max_puntos,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $asignacion = DB::table('asignaciones_jueces')->where('id', $asignacionId)->first();
        if (! $asignacion) {
            return response()->json(['message' => 'Asignación no encontrada.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Si es juez autenticado, validar que la asignación le pertenece
        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('juez')) {
            $juezAut = Juez::where('usuario_id', $user->id)->first();
            if (! $juezAut) {
                return response()->json(['message' => 'No autorizado'], Response::HTTP_FORBIDDEN);
            }
            if ((int) $asignacion->juez_id !== (int) $juezAut->id) {
                return response()->json(['message' => 'Asignación no pertenece al juez autenticado'], Response::HTTP_FORBIDDEN);
            }
        }

        // Bloqueo por finalización
        if (! is_null($asignacion->finalizada_at)) {
            return response()->json(['message' => 'La asignación ya está finalizada'], Response::HTTP_CONFLICT);
        }

        $proyecto = Proyecto::findOrFail($asignacion->proyecto_id);
        $juez = Juez::with(['usuario.institucion', 'instituciones'])->findOrFail($asignacion->juez_id);
        $institucionJuez = $juez->usuario?->institucion ?? $juez->instituciones()->first();

        $bloqueadas = Etapa::idsCircuitalYRegional();
        if (in_array((int) $asignacion->etapa_id, $bloqueadas, true)) {
            if ($institucionJuez && (int) $institucionJuez->id === (int) $proyecto->institucion_id) {
                return response()->json([
                    'message' => 'Conflicto de interés por etapa: no puede calificar proyectos de su misma institución en esta etapa.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        // Detectar columnas disponibles una sola vez
        $hasLegacy = Schema::hasColumn('calificaciones', 'asignacion_juez_id');
        $hasPlural = Schema::hasColumn('calificaciones', 'asignaciones_juez_id');

        // Resolver el id de la asignación (ya calculado arriba)
        $fkColumn = null;
        $fkValue = $asignacionId;

        // Preferir legacy si existe; si no, usar plural
        if ($hasLegacy) {
            $fkColumn = 'asignacion_juez_id';
        } elseif ($hasPlural) {
            $fkColumn = 'asignaciones_juez_id';
        } else {
            return response()->json([
                'message' => 'Esquema de calificaciones sin FK reconocible',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Construir payload SIN la otra columna
        $criterioId = (int) $data['criterio_id'];
        $puntaje = $data['puntaje'];
        $comentario = $data['comentario'] ?? null;

        $payload = [
            $fkColumn => $fkValue,
            'criterio_id' => $criterioId,
            'puntaje' => $puntaje,
            'comentario' => $comentario,
            'updated_at' => now(),
        ];

        // Upsert por (fkColumn, criterio_id)
        $exists = DB::table('calificaciones')
            ->where($fkColumn, $fkValue)
            ->where('criterio_id', $criterioId)
            ->exists();

        if ($exists) {
            DB::table('calificaciones')
                ->where($fkColumn, $fkValue)
                ->where('criterio_id', $criterioId)
                ->update($payload);

            Log::info('calificaciones.update', [
                'user_id' => $user?->id,
                'asignacion_id' => $fkValue,
                'criterio_id' => $criterioId,
                'ts' => now()->toDateTimeString(),
            ]);

            return response()->json(['message' => 'Actualizado'], Response::HTTP_OK);
        } else {
            $payload['created_at'] = now();
            DB::table('calificaciones')->insert($payload);

            Log::info('calificaciones.create', [
                'user_id' => $user?->id,
                'asignacion_id' => $fkValue,
                'criterio_id' => $criterioId,
                'ts' => now()->toDateTimeString(),
            ]);

            return response()->json(['message' => 'Creado'], Response::HTTP_CREATED);
        }
    }

    public function consolidar(Request $r)
    {
        $data = $r->validate([
            'proyecto_id' => ['required', 'integer', 'exists:proyectos,id'],
            'etapa_id' => ['required', 'integer'],
        ]);

        $proyectoId = (int) $data['proyecto_id'];
        $etapaId = (int) $data['etapa_id'];

        // Detectar columna FK dinámica igual que en store()
        $hasLegacy = Schema::hasColumn('calificaciones', 'asignacion_juez_id');
        $hasPlural = Schema::hasColumn('calificaciones', 'asignaciones_juez_id');
        if (! $hasLegacy && ! $hasPlural) {
            return response()->json(['message' => 'Esquema de calificaciones sin FK reconocible'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $fkColumn = $hasLegacy ? 'asignacion_juez_id' : 'asignaciones_juez_id';

        // Telemetría (solo entorno testing)
        $__dbg = [];
        if (app()->environment('testing')) {
            $__dbg['fkColumn'] = $fkColumn;
            $__dbg['proyecto_id'] = $proyectoId;
            $__dbg['etapa_id'] = $etapaId;
        }

        $rows = DB::table('calificaciones as c')
            ->select([
                'c.puntaje', 'k.max_puntos', 'k.peso', 'r.tipo_eval',
                'r.id as rubrica_id', 'r.modo', 'r.max_total',
                'c.criterio_id', 'k.nombre as criterio_nombre',
            ])
            ->join('criterios as k', 'k.id', '=', 'c.criterio_id')
            ->join('rubricas as r', 'r.id', '=', 'k.rubrica_id')
            ->join('asignaciones_jueces as aj', 'aj.id', '=', DB::raw('c.'.$fkColumn))
            ->where('aj.proyecto_id', $proyectoId)
            ->where('aj.etapa_id', $etapaId)
            ->get();

        if (app()->environment('testing')) {
            $__dbg['rows_count'] = $rows->count();
            $__dbg['rubrica_ids_en_rows'] = array_values(array_unique($rows->pluck('rubrica_id')->map(fn ($v) => (int) $v)->all()));
            $__dbg['tipo_eval_en_rows'] = array_values(array_unique($rows->pluck('tipo_eval')->all()));
        }

        // Detección de rúbrica especial de exposición
        $rubricaExpo = app(\App\Services\RubricaResolver::class)->resolveForProyecto($proyectoId, $etapaId, 'exposicion');
        if (app()->environment('testing')) {
            $__dbg['resolved_expo'] = $rubricaExpo ? [
                'id' => $rubricaExpo->id,
                'modo' => $rubricaExpo->modo,
                'max_total' => $rubricaExpo->max_total,
            ] : null;
        }

        if ($rubricaExpo && $rubricaExpo->modo === 'por_criterio' && ! is_null($rubricaExpo->max_total)) {
            $expoRows = $rows->filter(fn ($r) => $r->rubrica_id === $rubricaExpo->id);

            // Telemetría granular de pares (solo testing)
            $__pairs = [];
            foreach ($expoRows as $r) {
                $p = (float) $r->puntaje;
                $m = (float) $r->max_puntos;
                $clamped = min(max($p, 0.0), $m);
                if (app()->environment('testing')) {
                    $__pairs[] = [
                        'criterio_id' => (int) $r->criterio_id,
                        'p' => $p,
                        'm' => $m,
                        'clamped' => $clamped,
                    ];
                }
            }

            // Suma de puntajes clampados solo de filas existentes
            $sumClamped = 0.0;
            foreach ($expoRows as $r) {
                $p = (float) $r->puntaje;
                $m = (float) $r->max_puntos;
                $sumClamped += min(max($p, 0.0), $m);
            }

            // Suma de máximos de TODOS los criterios de la rúbrica resuelta (no solo los que tienen calificación)
            $sumMax = (float) DB::table('criterios')->where('rubrica_id', $rubricaExpo->id)->sum('max_puntos');

            $totalRaw = (float) $sumClamped;
            $maxSum = (float) $sumMax;
            $maxTotal = (float) ($rubricaExpo->max_total ?? $maxSum);

            if ($maxSum > 0 && $maxTotal !== $maxSum) {
                $total = round(($totalRaw / $maxSum) * $maxTotal, 2);
            } else {
                $total = round($totalRaw, 2);
            }
            $porcentaje = $maxTotal > 0 ? round(($total / $maxTotal) * 100, 2) : 0.0;

            $resp = [
                'proyecto_id' => $proyectoId,
                'etapa_id' => $etapaId,
                'total' => $total,
                'max_total' => $maxTotal,
                'porcentaje' => $porcentaje,
            ];
            if (app()->environment('testing')) {
                $resp['debug'] = array_merge($__dbg, [
                    'pairs' => $__pairs,
                    'sum_clamped' => $totalRaw,
                    'sum_max' => $maxSum,
                ]);
            }

            return response()->json($resp, Response::HTTP_OK);
        }

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

        $resp = [
            'proyecto_id' => $proyectoId,
            'etapa_id' => $etapaId,
            'nota_escrito' => $nota_escrito,
            'nota_exposicion' => $nota_exposicion,
            'nota_final' => $nota_final,
            'criterios' => $detalles,
        ];
        if (app()->environment('testing')) {
            $resp['debug'] = $__dbg;
        }

        return response()->json($resp, Response::HTTP_OK);
    }
}
