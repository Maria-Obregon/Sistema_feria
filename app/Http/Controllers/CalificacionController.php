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
        $perPage  = max(1, min((int) $r->query('per_page', 200), 200));
        $proyId   = (int) $r->query('proyecto_id');
        $etapaId  = (int) $r->query('etapa_id');
        $tipoEval = (string) $r->query('tipo_eval'); // ✅ obligatorio, sin default

        if (! $proyId || ! $etapaId) {
            return response()->json(['message' => 'proyecto_id y etapa_id son requeridos'], 422);
        }

        // ✅ tipo_eval obligatorio y validado (tu flujo trabaja con escrito/exposicion)
        if (! in_array($tipoEval, ['escrito', 'exposicion'], true)) {
            return response()->json(['message' => 'tipo_eval es requerido y debe ser escrito o exposicion'], 422);
        }

        $user = Auth::user();
        if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole('juez')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $juez = Juez::where('usuario_id', $user->id)->first();
        if (! $juez) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // ✅ match exacto con tipo_eval
        $asign = DB::table('asignacion_juez')
            ->where('proyecto_id', $proyId)
            ->where('etapa_id', $etapaId)
            ->where('juez_id', $juez->id)
            ->where('tipo_eval', $tipoEval)
            ->first();

        if (! $asign) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'count' => 0,
                    'message' => 'No hay asignación para este juez/proyecto/etapa/tipo_eval (revisa asignacion_juez.tipo_eval)',
                ],
            ]);
        }

        $rubrica = app(\App\Services\RubricaResolver::class)
            ->resolveForProyecto($proyId, $etapaId, $tipoEval);

        if (! $rubrica) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'count' => 0,
                    'message' => 'No hay rúbrica resuelta para esos parámetros',
                ],
            ]);
        }

        /**
         * ✅ CLAVE: detectar cuál FK usa tu tabla calificaciones
         * (porque en store vos lo hacés dinámico, pero en index lo tenías fijo a asignacion_juez_id).
         */
        $hasLegacy = Schema::hasColumn('calificaciones', 'asignacion_juez_id');
        $hasPlural = Schema::hasColumn('calificaciones', 'asignaciones_juez_id');

        if (! $hasLegacy && ! $hasPlural) {
            return response()->json(['message' => 'Esquema de calificaciones sin FK reconocible'], 500);
        }

        $fkColumn = $hasLegacy ? 'asignacion_juez_id' : 'asignaciones_juez_id';

        $rows = DB::table('criterios as k')
            ->leftJoin('calificaciones as c', function ($j) use ($asign, $fkColumn) {
                $j->on('c.criterio_id', '=', 'k.id')
                  ->where("c.$fkColumn", '=', $asign->id);
            })
            ->where('k.rubrica_id', $rubrica->id)
            ->orderBy('k.id')
            ->selectRaw("
                COALESCE(c.$fkColumn, ?) as asignacion_juez_id,
                k.id   as criterio_id,
                k.nombre as criterio_nombre,
                k.seccion,
                k.peso,
                k.max_puntos,
                c.puntaje,
                c.comentario
            ", [$asign->id])
            ->paginate($perPage);

        return response()->json($rows);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'asignacion_id' => ['required', 'integer', 'exists:asignacion_juez,id'],
            'criterio_id'   => ['required', 'integer', 'exists:criterios,id'],
            'puntaje'       => ['required'],
            'comentario'    => ['nullable', 'string', 'max:2000'],
        ]);

        $asignacionId = (int) $data['asignacion_id'];
        $criterio = Criterio::query()->with('rubrica')->findOrFail((int) $data['criterio_id']);

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

        $asignacion = DB::table('asignacion_juez')->where('id', $asignacionId)->first();
        if (! $asignacion) {
            return response()->json(['message' => 'Asignación no encontrada.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

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

        if (! is_null($asignacion->finalizada_at)) {
            return response()->json(['message' => 'La asignación ya está finalizada'], Response::HTTP_CONFLICT);
        }

        // Validación de conflicto por institución en etapas bloqueadas
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

        // ✅ FK dinámica (igual que en tu código)
        $hasLegacy = Schema::hasColumn('calificaciones', 'asignacion_juez_id');
        $hasPlural = Schema::hasColumn('calificaciones', 'asignaciones_juez_id');

        if (! $hasLegacy && ! $hasPlural) {
            return response()->json(['message' => 'Esquema de calificaciones sin FK reconocible'], 500);
        }

        $fkColumn = $hasLegacy ? 'asignacion_juez_id' : 'asignaciones_juez_id';
        $fkValue  = $asignacionId;

        $criterioId = (int) $data['criterio_id'];
        $puntaje    = $data['puntaje'];
        $comentario = $data['comentario'] ?? null;

        $payload = [
            $fkColumn     => $fkValue,
            'criterio_id' => $criterioId,
            'puntaje'     => $puntaje,
            'comentario'  => $comentario,
            'updated_at'  => now(),
        ];

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
                'user_id'       => $user?->id,
                'asignacion_id' => $fkValue,
                'criterio_id'   => $criterioId,
                'ts'            => now()->toDateTimeString(),
            ]);

            $totalPuntaje = DB::table('calificaciones')
                ->where($fkColumn, $fkValue)
                ->sum('puntaje');

            DB::table('asignacion_juez')
                ->where('id', $fkValue)
                ->update(['puntaje' => $totalPuntaje]);

            return response()->json(['message' => 'Actualizado'], Response::HTTP_OK);
        }

        $payload['created_at'] = now();
        DB::table('calificaciones')->insert($payload);

        Log::info('calificaciones.create', [
            'user_id'       => $user?->id,
            'asignacion_id' => $fkValue,
            'criterio_id'   => $criterioId,
            'ts'            => now()->toDateTimeString(),
        ]);

        $totalPuntaje = DB::table('calificaciones')
            ->where($fkColumn, $fkValue)
            ->sum('puntaje');

        DB::table('asignacion_juez')
            ->where('id', $fkValue)
            ->update(['puntaje' => $totalPuntaje]);

        return response()->json(['message' => 'Creado'], Response::HTTP_CREATED);
    }

    public function consolidar(Request $request)
    {
        $data = $request->validate([
            'proyecto_id' => 'required|integer',
            'etapa_id'    => 'required|integer',
        ]);

        $proyectoId = (int) $data['proyecto_id'];
        $etapaId    = (int) $data['etapa_id'];

        $proyecto = Proyecto::with('categoria')->findOrFail($proyectoId);

        $esF13 = $proyecto->categoria &&
            str_contains(mb_strtoupper($proyecto->categoria->nombre), 'MI EXPERIENCIA CIENTIFICA');

        if ($proyecto->categoria && $proyecto->categoria->nombre === 'Mi Experiencia Científica') {
            $esF13 = true;
        }

        $asignaciones = \App\Models\AsignacionJuez::with(['calificaciones', 'rubrica'])
            ->where('proyecto_id', $proyectoId)
            ->where('etapa_id', $etapaId)
            ->whereIn('tipo_eval', ['escrito', 'exposicion']) // ✅ integral no entra aquí
            ->get();

        if ($asignaciones->isEmpty()) {
            return response()->json([
                'nota_final'      => 0,
                'nota_escrito'    => 0,
                'nota_exposicion' => 0,
                'mensaje'         => 'No hay asignaciones para consolidar',
            ]);
        }

        $notasEscrito    = [];
        $notasExposicion = [];

        foreach ($asignaciones as $asig) {
            $rubrica = $asig->rubrica;

            if (! $rubrica) {
                $rubrica = app(\App\Services\RubricaResolver::class)
                    ->resolveForProyecto($proyectoId, $etapaId, $asig->tipo_eval);
            }

            if (! $rubrica) {
                continue;
            }

            // ✅ CLAVE: usar max_total (o fallback a max_puntos o sumatoria de criterios)
            $maxRubrica = (float) (
                $rubrica->max_total
                ?? $rubrica->max_puntos
                ?? 0
            );

            if ($maxRubrica <= 0) {
                $maxRubrica = (float) DB::table('criterios')
                    ->where('rubrica_id', $rubrica->id)
                    ->sum('max_puntos');
            }

            if ($maxRubrica <= 0) {
                continue;
            }

            $puntosObtenidos = (float) $asig->calificaciones->sum('puntaje');
            $notaBase100     = ($puntosObtenidos / $maxRubrica) * 100;
            $notaBase100     = min($notaBase100, 100);

            if ($asig->tipo_eval === 'escrito') {
                $notasEscrito[] = $notaBase100;
            } elseif ($asig->tipo_eval === 'exposicion') {
                $notasExposicion[] = $notaBase100;
            }
        }

        $promedioEscrito    = ! empty($notasEscrito) ? array_sum($notasEscrito) / count($notasEscrito) : 0;
        $promedioExposicion = ! empty($notasExposicion) ? array_sum($notasExposicion) / count($notasExposicion) : 0;

        if ($esF13) {
            $notaFinal = $promedioExposicion;
            $desglose  = 'F13 (100% Exposición)';
        } else {
            $notaFinal = ($promedioEscrito * 0.5) + ($promedioExposicion * 0.5);
            $desglose  = '50% Escrito + 50% Exposición';
        }

        return response()->json([
            'nota_final'      => round($notaFinal, 2),
            'nota_escrito'    => round($promedioEscrito, 2),
            'nota_exposicion' => round($promedioExposicion, 2),
            'es_f13'          => $esF13,
            'desglose'        => $desglose,
            'meta' => [
                'count_escrito' => count($notasEscrito),
                'count_expo'    => count($notasExposicion),
            ],
        ]);
    }
}
