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
        $perPage = max(1, min((int) $r->query('per_page', 200), 200));
        $proyId = (int) $r->query('proyecto_id');
        $etapaId = (int) $r->query('etapa_id');
        $tipoEval = $r->query('tipo_eval', 'exposicion'); // por defecto

        if (! $proyId || ! $etapaId) {
            return response()->json(['message' => 'proyecto_id y etapa_id son requeridos'], 422);
        }

        // Juez autenticado
        $user = Auth::user();
        if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole('juez')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $juez = Juez::where('usuario_id', $user->id)->first();
        if (! $juez) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Asignación del juez para ese proyecto/etapa y TIPO DE EVALUACIÓN
        $asign = DB::table('asignacion_juez')
            ->where('proyecto_id', $proyId)
            ->where('etapa_id', $etapaId)
            ->where('juez_id', $juez->id)
            ->where('tipo_eval', $tipoEval) // Filtrar por tipo (escrito/exposicion)
            ->first();

        if (! $asign) {
            return response()->json(['data' => [], 'meta' => ['count' => 0]]);
        }

        // Resolver rúbrica (servicio que ya usas)
        $rubrica = app(\App\Services\RubricaResolver::class)
            ->resolveForProyecto($proyId, $etapaId, $tipoEval);

        if (! $rubrica) {
            return response()->json(['data' => [], 'meta' => ['count' => 0]]);
        }

        // Partimos de CRITERIOS y hacemos LEFT JOIN a calificaciones
        $rows = DB::table('criterios as k')
            ->leftJoin('calificaciones as c', function ($j) use ($asign) {
                $j->on('c.criterio_id', '=', 'k.id')
                    ->where('c.asignacion_juez_id', '=', $asign->id);
            })
            ->where('k.rubrica_id', $rubrica->id)
            ->orderBy('k.id')
            ->selectRaw('
            COALESCE(c.asignacion_juez_id, ?) as asignacion_juez_id,
            k.id   as criterio_id,
            k.nombre as criterio_nombre,
            k.peso,
            k.max_puntos,
            c.puntaje,
            c.comentario
        ', [$asign->id])
            ->paginate($perPage);

        return response()->json($rows);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'asignacion_id' => ['required', 'integer', 'exists:asignacion_juez,id'],
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

        $asignacion = DB::table('asignacion_juez')->where('id', $asignacionId)->first();
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
            return response()->json(['message' => 'Esquema de calificaciones sin FK reconocible'], 500);
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

    /**
     * Calcula la nota final consolidada de un proyecto en una etapa.
     * Lógica PRONAFECYT:
     * - Normaliza cada evaluación a base 100 (PuntosObtenidos / MaxRubrica * 100)
     * - Promedia evaluaciones del mismo tipo
     * - Pondera: F13 (100% Expo), Resto (50% Escrito + 50% Expo)
     */
    public function consolidar(Request $request)
    {
        $data = $request->validate([
            'proyecto_id' => 'required|integer',
            'etapa_id' => 'required|integer',
        ]);

        $proyectoId = $data['proyecto_id'];
        $etapaId = $data['etapa_id'];

        // 1. Obtener proyecto y categoría
        $proyecto = Proyecto::with('categoria')->findOrFail($proyectoId);
        $esF13 = $proyecto->categoria &&
            str_contains(mb_strtoupper($proyecto->categoria->nombre), 'MI EXPERIENCIA CIENTIFICA'); // Sin tildes o con tildes por seguridad, mejor uso str_contains

        // Nombre exacto del seeder es 'Mi Experiencia Científica'
        if ($proyecto->categoria && $proyecto->categoria->nombre === 'Mi Experiencia Científica') {
            $esF13 = true;
        }

        // 2. Obtener asignaciones con sus calificaciones y rúbrica
        // Usamos la tabla singular 'asignacion_juez' que es la que se está usando actualmente
        // (Hubo una migración de plural a singular, asumimos singular según logs anteriores)
        $asignaciones = \App\Models\AsignacionJuez::with(['calificaciones', 'rubrica'])
            ->where('proyecto_id', $proyectoId)
            ->where('etapa_id', $etapaId)
            ->get();

        if ($asignaciones->isEmpty()) {
            return response()->json([
                'nota_final' => 0,
                'nota_escrito' => 0,
                'nota_exposicion' => 0,
                'mensaje' => 'No hay asignaciones para consolidar',
            ]);
        }

        $notasEscrito = [];
        $notasExposicion = [];

        foreach ($asignaciones as $asig) {
            $rubrica = $asig->rubrica;
            if (! $rubrica) {
                // Intentar resolver rúbrica si no está eager loaded o vinculada
                // Esto pasa si la asignación no guardó rubrica_id o el modelo no tiene la relación
                // Por ahora saltamos si falla, pero debería tenerla.
                // En el sistema actual, AsignacionJuez no suele tener rubrica_id directo,
                // sino que se resuelve dinámicamente. Vamos a resolverla si es null.
                $rubrica = app(\App\Services\RubricaResolver::class)
                    ->resolveForProyecto($proyectoId, $etapaId, $asig->tipo_eval);
            }

            if (! $rubrica || $rubrica->max_puntos <= 0) {
                continue; // No se puede normalizar sin max_puntos
            }

            $puntosObtenidos = $asig->calificaciones->sum('puntaje');

            // Normalizar a base 100
            // Ejemplo: Sacó 32 de 40 -> (32/40)*100 = 80
            $notaBase100 = ($puntosObtenidos / $rubrica->max_puntos) * 100;

            // Clamp por seguridad (no más de 100)
            $notaBase100 = min($notaBase100, 100);

            if ($asig->tipo_eval === 'escrito') {
                $notasEscrito[] = $notaBase100;
            } elseif ($asig->tipo_eval === 'exposicion') {
                $notasExposicion[] = $notaBase100;
            }
        }

        // 3. Promediar por tipo
        $promedioEscrito = ! empty($notasEscrito) ? array_sum($notasEscrito) / count($notasEscrito) : 0;
        $promedioExposicion = ! empty($notasExposicion) ? array_sum($notasExposicion) / count($notasExposicion) : 0;

        // 4. Ponderar
        if ($esF13) {
            // F13: 100% Exposición
            $notaFinal = $promedioExposicion;
            $desglose = 'F13 (100% Exposición)';
        } else {
            // Resto: 50% Escrito + 50% Exposición
            // Si falta alguno de los componentes, el promedio actual asume 0 en esa parte
            // ¿O deberíamos ignorar la parte faltante?
            // La regla dice 50/50. Si no hay escrito, tienes 0 en el 50% escrito.
            $notaFinal = ($promedioEscrito * 0.5) + ($promedioExposicion * 0.5);
            $desglose = '50% Escrito + 50% Exposición';
        }

        return response()->json([
            'nota_final' => round($notaFinal, 2),
            'nota_escrito' => round($promedioEscrito, 2),
            'nota_exposicion' => round($promedioExposicion, 2),
            'es_f13' => $esF13,
            'desglose' => $desglose,
            'meta' => [
                'count_escrito' => count($notasEscrito),
                'count_expo' => count($notasExposicion),
            ],
        ]);
    }
}
