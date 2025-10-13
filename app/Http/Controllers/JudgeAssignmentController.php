<?php

namespace App\Http\Controllers;

use App\Http\Requests\JudgeAssignmentIndexRequest;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\JudgeProjectResource;
use App\Models\AsignacionJuez;
use App\Models\Proyecto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class JudgeAssignmentController extends Controller
{
    /**
     * Lista las asignaciones del juez autenticado
     * GET /api/juez/asignaciones
     */
    public function index(JudgeAssignmentIndexRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        // Obtener el juez asociado al usuario autenticado
        $juez = $user->juez;
        
        if (!$juez) {
            return response()->json([
                'message' => 'Usuario autenticado no tiene un perfil de juez asociado'
            ], 403);
        }

        // Query base: asignaciones del juez autenticado
        $query = AsignacionJuez::where('juez_id', $juez->id)
            ->with(['project.area', 'project.categoria', 'stage', 'grades.criterion']);

        // Filtro por etapa_id
        if ($request->filled('etapa_id')) {
            $query->where('etapa_id', $request->etapa_id);
        }

        // Filtro por tipo_eval
        if ($request->filled('tipo_eval')) {
            $query->where('tipo_eval', $request->tipo_eval);
        }

        // Obtener asignaciones antes de aplicar filtro de estado
        $assignments = $query->get();

        // Filtro por estado (pending o completed)
        if ($request->filled('estado')) {
            $assignments = $assignments->filter(function ($assignment) use ($request) {
                $isCompleted = $this->isAssignmentCompleted($assignment);
                
                if ($request->estado === 'completed') {
                    return $isCompleted;
                } else if ($request->estado === 'pending') {
                    return !$isCompleted;
                }
                
                return true;
            });
        }

        // Paginar manualmente después del filtrado
        $perPage = 15;
        $page = $request->input('page', 1);
        $total = $assignments->count();
        $paginatedAssignments = $assignments->forPage($page, $perPage)->values();

        return response()->json([
            'data' => AssignmentResource::collection($paginatedAssignments),
            'meta' => [
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ]
        ]);
    }

    /**
     * Muestra los detalles de un proyecto asignado al juez autenticado
     * GET /api/juez/proyectos/{id}
     */
    public function showProject(int $id): JsonResponse
    {
        $user = Auth::user();
        
        // Obtener el juez asociado
        $juez = $user->juez;
        
        if (!$juez) {
            return response()->json([
                'message' => 'Usuario autenticado no tiene un perfil de juez asociado'
            ], 403);
        }

        // Verificar que el proyecto existe
        $proyecto = Proyecto::find($id);
        
        if (!$proyecto) {
            return response()->json([
                'message' => 'Proyecto no encontrado'
            ], 404);
        }

        // Verificar que el juez tiene asignación a este proyecto
        $asignacion = AsignacionJuez::where('juez_id', $juez->id)
            ->where('proyecto_id', $id)
            ->with(['stage', 'project.area', 'project.categoria'])
            ->first();

        if (!$asignacion) {
            return response()->json([
                'message' => 'No tienes permiso para ver este proyecto'
            ], 403);
        }

        return response()->json([
            'data' => new JudgeProjectResource($asignacion)
        ]);
    }

    /**
     * Determina si una asignación está completa
     * Una asignación está completa si existen calificaciones para todos los criterios
     * de la rúbrica correspondiente al tipo_eval
     */
    private function isAssignmentCompleted(AsignacionJuez $assignment): bool
    {
        if (!$assignment->tipo_eval) {
            return false;
        }

        // Obtener la rúbrica correspondiente al tipo_eval
        $rubrica = \App\Models\Rubrica::where('tipo_eval', $assignment->tipo_eval)->first();
        
        if (!$rubrica) {
            return false;
        }

        // Contar criterios de la rúbrica
        $totalCriterios = $rubrica->criterios()->count();
        
        if ($totalCriterios === 0) {
            return false;
        }

        // Contar calificaciones completadas para esta asignación
        $calificacionesCompletadas = $assignment->grades()
            ->whereHas('criterion', function ($query) use ($rubrica) {
                $query->where('rubrica_id', $rubrica->id);
            })
            ->count();

        return $calificacionesCompletadas >= $totalCriterios;
    }
}
