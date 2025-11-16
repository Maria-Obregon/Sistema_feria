<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalificacionIndexRequest;
use App\Http\Requests\CalificacionStoreRequest;
use App\Http\Resources\CalificacionResource;
use App\Models\AsignacionJuez;
use App\Models\Calificacion;
use App\Models\Criterio;
use App\Models\Rubrica;
use App\Services\GradeConsolidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Controlador de Calificaciones
 * 
 * Gestiona la creación y consulta de calificaciones por parte de jueces
 * 
 * @package App\Http\Controllers
 */
class CalificacionController extends Controller
{
    /**
     * Listar calificaciones de una asignación
     * 
     * GET /api/calificaciones?asignacion_juez_id=123
     * 
     * Ejemplo de respuesta 200:
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "asignacion_juez_id": 123,
     *       "criterio_id": 5,
     *       "puntaje": 85,
     *       "comentario": "Excelente trabajo",
     *       "creada_en": "2025-10-12 20:00:00",
     *       "created_at": "2025-10-12 20:00:00",
     *       "updated_at": "2025-10-12 20:00:00"
     *     }
     *   ]
     * }
     * 
     * Ejemplo de respuesta 403:
     * {
     *   "message": "No tienes permiso para ver estas calificaciones"
     * }
     * 
     * @param CalificacionIndexRequest $request
     * @return JsonResponse
     */
    public function index(CalificacionIndexRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        // Obtener el juez asociado al usuario autenticado
        $juez = $user->juez;
        
        if (!$juez) {
            return response()->json([
                'message' => 'Usuario autenticado no tiene un perfil de juez asociado'
            ], 403);
        }

        // Verificar que la asignación pertenezca al juez autenticado
        $asignacionId = $request->asignacion_juez_id;
        $asignacion = AsignacionJuez::find($asignacionId);

        if (!$asignacion || $asignacion->juez_id !== $juez->id) {
            return response()->json([
                'message' => 'No tienes permiso para ver estas calificaciones'
            ], 403);
        }

        // Obtener las calificaciones de la asignación
        $calificaciones = Calificacion::where('asignacion_juez_id', $asignacionId)
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => CalificacionResource::collection($calificaciones)
        ], 200);
    }

    /**
     * Crear o actualizar calificaciones en lote (bulk upsert)
     * 
     * POST /api/calificaciones
     * Body:
     * {
     *   "asignacion_juez_id": 123,
     *   "items": [
     *     {
     *       "criterio_id": 5,
     *       "puntaje": 85,
     *       "comentario": "Excelente trabajo"
     *     },
     *     {
     *       "criterio_id": 6,
     *       "puntaje": 90,
     *       "comentario": null
     *     }
     *   ]
     * }
     * 
     * Ejemplo de respuesta 200:
     * {
     *   "asignacion_juez_id": 123,
     *   "saved": [5, 6],
     *   "updated": [],
     *   "total": 2
     * }
     * 
     * Ejemplo de respuesta 403:
     * {
     *   "message": "No tienes permiso para calificar esta asignación"
     * }
     * 
     * Ejemplo de respuesta 422 (criterio no pertenece a la rúbrica):
     * {
     *   "message": "El criterio 7 no pertenece a la rúbrica del tipo de evaluación"
     * }
     * 
     * Ejemplo de respuesta 422 (puntaje fuera de rango):
     * {
     *   "message": "El puntaje 150 excede el máximo permitido de 100 para el criterio 5"
     * }
     * 
     * Ejemplo de respuesta 422 (etapa cerrada):
     * {
     *   "message": "Etapa cerrada; no se permiten cambios"
     * }
     * 
     * @param CalificacionStoreRequest $request
     * @param GradeConsolidationService $consolidationService
     * @return JsonResponse
     */
    public function store(CalificacionStoreRequest $request, GradeConsolidationService $consolidationService): JsonResponse
    {
        $user = Auth::user();
        
        // Obtener el juez asociado al usuario autenticado
        $juez = $user->juez;
        
        if (!$juez) {
            return response()->json([
                'message' => 'Usuario autenticado no tiene un perfil de juez asociado'
            ], 403);
        }

        // Verificar que la asignación pertenezca al juez autenticado
        $asignacionId = $request->asignacion_juez_id;
        $asignacion = AsignacionJuez::find($asignacionId);

        if (!$asignacion || $asignacion->juez_id !== $juez->id) {
            return response()->json([
                'message' => 'No tienes permiso para calificar esta asignación'
            ], 403);
        }

        // Verificar si la etapa está cerrada
        if ($consolidationService->isStageClosed($asignacion->proyecto_id, $asignacion->etapa_id)) {
            return response()->json([
                'message' => 'Etapa cerrada; no se permiten cambios'
            ], 422);
        }

        // Obtener la rúbrica correspondiente al tipo_eval de la asignación
        if (!$asignacion->tipo_eval) {
            return response()->json([
                'message' => 'La asignación no tiene tipo de evaluación definido'
            ], 422);
        }

        $rubrica = Rubrica::where('tipo_eval', $asignacion->tipo_eval)->first();

        if (!$rubrica) {
            return response()->json([
                'message' => 'No se encontró una rúbrica para el tipo de evaluación ' . $asignacion->tipo_eval
            ], 422);
        }

        // Obtener todos los criterios de la rúbrica con su max_puntos
        $criteriosRubrica = Criterio::where('rubrica_id', $rubrica->id)
            ->get()
            ->keyBy('id');

        // Validar cada item antes de guardar
        $items = $request->items;
        foreach ($items as $item) {
            $criterioId = $item['criterio_id'];
            
            // Verificar que el criterio pertenezca a la rúbrica
            if (!$criteriosRubrica->has($criterioId)) {
                return response()->json([
                    'message' => "El criterio {$criterioId} no pertenece a la rúbrica del tipo de evaluación"
                ], 422);
            }

            // Verificar que el puntaje esté dentro del rango
            $criterio = $criteriosRubrica->get($criterioId);
            $puntaje = $item['puntaje'];
            
            if ($puntaje > $criterio->max_puntos) {
                return response()->json([
                    'message' => "El puntaje {$puntaje} excede el máximo permitido de {$criterio->max_puntos} para el criterio {$criterioId}"
                ], 422);
            }
        }

        // Realizar el upsert en una transacción
        $saved = [];
        $updated = [];

        DB::transaction(function () use ($asignacionId, $items, &$saved, &$updated) {
            foreach ($items as $item) {
                $criterioId = $item['criterio_id'];
                
                // Buscar si ya existe una calificación
                $existing = Calificacion::where('asignacion_juez_id', $asignacionId)
                    ->where('criterio_id', $criterioId)
                    ->first();

                $data = [
                    'asignacion_juez_id' => $asignacionId,
                    'criterio_id' => $criterioId,
                    'puntaje' => $item['puntaje'],
                    'comentario' => $item['comentario'] ?? null,
                ];

                if ($existing) {
                    // Actualizar
                    $existing->update($data);
                    $updated[] = $criterioId;
                } else {
                    // Crear
                    $data['creada_en'] = now();
                    Calificacion::create($data);
                    $saved[] = $criterioId;
                }
            }
        });

        return response()->json([
            'asignacion_juez_id' => $asignacionId,
            'saved' => $saved,
            'updated' => $updated,
            'total' => count($saved) + count($updated)
        ], 200);
    }

    /**
     * Consolidar calificaciones de un proyecto en una etapa
     * 
     * POST /api/calificaciones/consolidar
     * Body:
     * {
     *   "proyecto_id": 1,
     *   "etapa_id": 1
     * }
     * 
     * Ejemplo de respuesta 200:
     * {
     *   "proyectoId": 1,
     *   "etapaId": 1,
     *   "juecesPorTipo": { "escrita": 3, "oral": 2 },
     *   "notaEscrita": 0.82,
     *   "notaOral": 0.76,
     *   "ponderaciones": { "escrita": 0.60, "oral": 0.40 },
     *   "notaFinal": 0.80
     * }
     * 
     * Ejemplo de respuesta 422 (cardinalidad insuficiente):
     * {
     *   "message": "Se requieren al menos 3 jueces en etapa Institucional",
     *   "errors": {
     *     "cardinalidad": ["Se requieren al menos 3 jueces en etapa Institucional"]
     *   }
     * }
     * 
     * Ejemplo de respuesta 422 (etapa cerrada):
     * {
     *   "message": "Etapa cerrada; no se permiten cambios"
     * }
     * 
     * @param Request $request
     * @param GradeConsolidationService $consolidationService
     * @return JsonResponse
     */
    public function consolidar(Request $request, GradeConsolidationService $consolidationService): JsonResponse
    {
        // Validar parámetros
        $validated = $request->validate([
            'proyecto_id' => 'required|integer|exists:proyectos,id',
            'etapa_id' => 'required|integer|exists:etapas,id',
        ]);

        $proyectoId = $validated['proyecto_id'];
        $etapaId = $validated['etapa_id'];

        // Verificar si la etapa está cerrada
        if ($consolidationService->isStageClosed($proyectoId, $etapaId)) {
            return response()->json([
                'message' => 'Etapa cerrada; no se permiten cambios'
            ], 422);
        }

        try {
            // Validar cardinalidad mínima de jueces
            $consolidationService->checkCardinality($proyectoId, $etapaId);

            // Consolidar calificaciones
            $resultado = $consolidationService->consolidateProjectStage($proyectoId, $etapaId);

            return response()->json($resultado, 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        }
    }
}
