<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Rubrica;
use App\Services\RubricaResolver;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RubricaResolverController extends Controller
{
    public function show(Request $r, Proyecto $proyecto)
    {
        $etapaId = (int) $r->query('etapa_id');
        $tipoEval = (string) $r->query('tipo_eval');
        if (! in_array($tipoEval, ['escrito', 'exposicion'], true) || $etapaId <= 0) {
            return response()->json(['message' => 'Parámetros inválidos'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var RubricaResolver $resolver */
        $resolver = app(RubricaResolver::class);
        $resolved = $resolver->resolveForProyecto($proyecto->id, $etapaId, $tipoEval);

        if (! $resolved) {
            return response()->json(['message' => 'No hay rúbrica asignada para los parámetros dados'], Response::HTTP_NOT_FOUND);
        }

        /** @var Rubrica $rubrica */
        $rubrica = Rubrica::with('criterios')->find($resolved->id);
        if (! $rubrica) {
            return response()->json(['message' => 'Rúbrica no encontrada'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'rubrica' => [
                'id' => $rubrica->id,
                'nombre' => $rubrica->nombre,
                'tipo_eval' => $rubrica->tipo_eval,
                'modo' => $rubrica->modo,
                'max_total' => $rubrica->max_total,
            ],
            'criterios' => $rubrica->criterios
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'peso' => (float) $c->peso,
                    'max_puntos' => (float) $c->max_puntos,
                ])->values(),
        ], Response::HTTP_OK);
    }
}
