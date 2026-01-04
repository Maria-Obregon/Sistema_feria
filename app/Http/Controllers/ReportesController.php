<?php

namespace App\Http\Controllers;

use App\Models\Feria;
use App\Models\Estudiante;
use App\Models\Juez;
use App\Models\Invitado;
use App\Models\Colaborador;
use App\Models\Proyecto;
use App\Models\Calificacion;
use App\Models\AsignacionJuez;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;     // ✅ NUEVO
use Barryvdh\DomPDF\Facade\Pdf;

class ReportesController extends Controller
{
    public function resumen(Request $request)
    {
        $feriaId = (int) $request->input('feria_id');

        if (! $feriaId) {
            return response()->json([
                'estudiantes'   => 0,
                'jueces'        => 0,
                'invitados'     => 0,
                'colaboradores' => 0,
            ]);
        }

        $feria = Feria::find($feriaId);
        if (! $feria) {
            return response()->json([
                'estudiantes'   => 0,
                'jueces'        => 0,
                'invitados'     => 0,
                'colaboradores' => 0,
            ]);
        }

        $estudiantes = Estudiante::whereHas('proyectos', fn($q) => $q->where('feria_id', $feriaId))->count();
        $jueces      = Juez::whereHas('proyectos', fn($q) => $q->where('feria_id', $feriaId))->count();

        $invitados     = Invitado::where('feria_id', $feriaId)->count();
        $colaboradores = Colaborador::where('feria_id', $feriaId)->count();

        return response()->json([
            'estudiantes'   => $estudiantes,
            'jueces'        => $jueces,
            'invitados'     => $invitados,
            'colaboradores' => $colaboradores,
        ]);
    }

    // ==========================================================
    // LISTAS POR FERIA
    // ==========================================================
    public function feriaEstudiantes(Request $request)
    {
        $feriaId = (int) $request->input('feria_id');
        if (! $feriaId) return response()->json(['data' => []]);

        $items = Estudiante::whereHas('proyectos', fn($q) => $q->where('feria_id', $feriaId))
            ->orderBy('nombre')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function feriaJueces(Request $request)
    {
        $feriaId = (int) $request->input('feria_id');
        if (! $feriaId) return response()->json(['data' => []]);

        $items = Juez::whereHas('proyectos', fn($q) => $q->where('feria_id', $feriaId))
            ->orderBy('nombre')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function feriaInvitados(Request $request)
    {
        $feriaId = (int) $request->input('feria_id');
        if (! $feriaId) return response()->json(['data' => []]);

        $items = Invitado::where('feria_id', $feriaId)->orderBy('nombre')->get();
        return response()->json(['data' => $items]);
    }

    public function feriaColaboradores(Request $request)
    {
        $feriaId = (int) $request->input('feria_id');
        if (! $feriaId) return response()->json(['data' => []]);

        $items = Colaborador::where('feria_id', $feriaId)->orderBy('nombre')->get();
        return response()->json(['data' => $items]);
    }

    // ==========================================================
    // HELPER PDF TABLA
    // ==========================================================
    protected function generarPdfTabla(string $titulo, array $headers, array $rows, string $subtitulo = '', string $pie = '')
    {
        return Pdf::loadView('reportes.plantilla_tabla', [
            'titulo'    => $titulo,
            'headers'   => $headers,
            'rows'      => $rows,
            'subtitulo' => $subtitulo,
            'pie'       => $pie,
        ]);
    }

    protected function esF13(?Proyecto $p): bool
    {
        if (! $p) return false;
        $cat = mb_strtoupper($p->categoria?->nombre ?? '');
        return str_contains($cat, 'MI EXPERIENCIA');
    }

    /**
     * ✅ Detecta cuál FK usa calificaciones (asignacion_juez_id vs asignaciones_juez_id)
     */
    protected function calificacionesFkColumn(): ?string
    {
        $hasLegacy = Schema::hasColumn('calificaciones', 'asignacion_juez_id');
        $hasPlural = Schema::hasColumn('calificaciones', 'asignaciones_juez_id');

        if ($hasLegacy) return 'asignacion_juez_id';
        if ($hasPlural) return 'asignaciones_juez_id';
        return null;
    }

    /**
     * ✅ Obtiene el máximo de la rúbrica:
     * 1) max_total (lo que vos devolvés: 78/64/etc.)
     * 2) max_puntos (si existiera)
     * 3) suma de criterios
     */
    protected function rubricaMax($rubrica): float
    {
        if (! $rubrica) return 0.0;

        $max = (float) (
            ($rubrica->max_total ?? null)
            ?? ($rubrica->max_puntos ?? null)
            ?? 0
        );

        if ($max > 0) return $max;

        // fallback por DB (no dependemos de relación cargada)
        return (float) \DB::table('criterios')
            ->where('rubrica_id', $rubrica->id)
            ->sum('max_puntos');
    }

    /**
     * Construye dataset de notas (base 100) por proyecto para una feria y etapa.
     */
    protected function datasetCalificacionesFeria(int $feriaId, int $etapaId): array
    {
        $fkColumn = $this->calificacionesFkColumn(); // ✅ NUEVO
        if (! $fkColumn) {
            // si esto pasa, tu BD no tiene ninguna FK reconocible
            return [];
        }

        $proyectos = Proyecto::with(['categoria', 'modalidad', 'institucion', 'area'])
            ->where('feria_id', $feriaId)
            ->orderBy('categoria_id')
            ->orderBy('titulo')
            ->get();

        $resolver = app(\App\Services\RubricaResolver::class);

        $rows = [];

        foreach ($proyectos as $p) {
            $esF13 = $this->esF13($p);

            $asigs = AsignacionJuez::where('proyecto_id', $p->id)
                ->where('etapa_id', $etapaId)
                ->whereIn('tipo_eval', ['escrito', 'exposicion'])
                ->get();

            $notasEscrito = [];
            $notasExpo    = [];
            $pendEscrito  = false;
            $pendExpo     = false;

            foreach ($asigs as $a) {
                if (is_null($a->finalizada_at)) {
                    if ($a->tipo_eval === 'escrito')    $pendEscrito = true;
                    if ($a->tipo_eval === 'exposicion') $pendExpo    = true;
                }

                $rubrica = $resolver->resolveForProyecto($p->id, $etapaId, $a->tipo_eval);
                if (! $rubrica) continue;

                $max = $this->rubricaMax($rubrica); // ✅ CAMBIADO
                if ($max <= 0) continue;

                // ✅ CAMBIADO: sum dinámico según FK real
                $puntos = (float) Calificacion::where($fkColumn, $a->id)->sum('puntaje');

                $nota100 = min(($puntos / $max) * 100.0, 100.0);

                if ($a->tipo_eval === 'escrito')    $notasEscrito[] = $nota100;
                if ($a->tipo_eval === 'exposicion') $notasExpo[]    = $nota100;
            }

            $promEscrito = count($notasEscrito) ? (array_sum($notasEscrito) / count($notasEscrito)) : null;
            $promExpo    = count($notasExpo)    ? (array_sum($notasExpo) / count($notasExpo))       : null;

            $final = null;
            if ($esF13) {
                $final = $promExpo;
            } else {
                if (!is_null($promEscrito) || !is_null($promExpo)) {
                    $final = ((float)($promEscrito ?? 0) * 0.5) + ((float)($promExpo ?? 0) * 0.5);
                }
            }

            $rows[] = [
                'proyecto'     => $p,
                'prom_escrito' => $promEscrito,
                'prom_expo'    => $promExpo,
                'final'        => $final,
                'pend_escrito' => $pendEscrito,
                'pend_expo'    => $pendExpo,
            ];
        }

        // ordenar por nota final desc (null al final)
        usort($rows, function ($a, $b) {
            $fa = is_null($a['final']) ? -1 : $a['final'];
            $fb = is_null($b['final']) ? -1 : $b['final'];
            return $fb <=> $fa;
        });

        return $rows;
    }

    // ==========================================================
    // CERTIFICADOS (sin cambios)
    // ==========================================================
    public function certificadoEstudiante(Estudiante $estudiante, Request $request)
    {
        $feriaId = $request->input('feria_id');

        $proyecto = $estudiante->proyectos()
            ->when($feriaId, fn($q) => $q->where('feria_id', $feriaId))
            ->first();

        $feria = $proyecto?->feria ?? ($feriaId ? Feria::find($feriaId) : null);

        if (! $proyecto || ! $feria) {
            return response()->json(['message' => 'No se encontró un proyecto del estudiante para la feria indicada.'], 404);
        }

        $pdf = Pdf::loadView('certificados.cert_estudiante', [
            'estudiante' => $estudiante,
            'proyecto'   => $proyecto,
            'feria'      => $feria,
        ])->setPaper('letter', 'landscape');

        return $pdf->download("certificado-estudiante-{$estudiante->id}.pdf");
    }

    public function certificadoJuez(Juez $juez, Request $request)
    {
        $feriaId = $request->input('feria_id');

        $proyecto = $juez->proyectos()
            ->when($feriaId, fn($q) => $q->where('feria_id', $feriaId))
            ->first();

        $feria = $proyecto?->feria ?? ($feriaId ? Feria::find($feriaId) : null);

        if (! $feria) {
            return response()->json(['message' => 'No se encontró la feria para este juez. Enviá feria_id o verificá asignaciones.'], 404);
        }

        $pdf = Pdf::loadView('certificados.cert_juez', [
            'juez'  => $juez,
            'feria' => $feria,
        ])->setPaper('letter', 'landscape');

        return $pdf->download("certificado-juez-{$juez->id}.pdf");
    }

    public function certificadoInvitado(Invitado $invitado)
    {
        $feria = $invitado->feria ?? Feria::find($invitado->feria_id);

        $pdf = Pdf::loadView('certificados.cert_invitado', [
            'invitado' => $invitado,
            'feria'    => $feria,
        ])->setPaper('letter', 'landscape');

        return $pdf->download("certificado-invitado-{$invitado->id}.pdf");
    }

    public function certificadoColaborador(Colaborador $colaborador)
    {
        $feria = $colaborador->feria ?? Feria::find($colaborador->feria_id);

        $pdf = Pdf::loadView('certificados.cert_colaborador', [
            'colaborador' => $colaborador,
            'feria'       => $feria,
        ])->setPaper('letter', 'landscape');

        return $pdf->download("certificado-colaborador-{$colaborador->id}.pdf");
    }

    // ==========================================================
    // LISTADOS DE CALIFICACIONES
    // ==========================================================
    public function califInformeEscrito(Request $request)
    {
        $feriaId = (int) $request->input('feria_id');
        $etapaId = (int) $request->input('etapa_id', 1);

        $data = $this->datasetCalificacionesFeria($feriaId, $etapaId);

        $headers = ['#', 'Código', 'Proyecto', 'Categoría', 'Modalidad', 'Nota escrito', 'Estado'];
        $rows = [];

        $i = 1;
        foreach ($data as $d) {
            $p = $d['proyecto'];

            $nota = is_null($d['prom_escrito']) ? '—' : number_format($d['prom_escrito'], 2) . '%';
            $estado = $d['pend_escrito']
                ? '<span class="badge b-pend">Pendiente</span>'
                : '<span class="badge b-ok">OK</span>';

            $rows[] = [
                (string) $i++,
                e($p->codigo ?? ('PRJ-' . $p->id)),
                e($p->titulo),
                e($p->categoria?->nombre ?? 'Sin categoría'),
                e($p->modalidad?->nombre ?? '—'),
                '<div class="right">' . e($nota) . '</div>',
                $estado,
            ];
        }

        $pdf = $this->generarPdfTabla(
            'Listado de calificaciones - Informe escrito',
            $headers,
            $rows,
            "Feria ID {$feriaId} — Etapa {$etapaId}",
            'PRONAFECYT'
        );

        return $pdf->download("calificaciones-informe-escrito-feria-{$feriaId}-etapa-{$etapaId}.pdf");
    }

    public function califGeneral(Request $request)
    {
        $feriaId = (int) $request->input('feria_id');
        $etapaId = (int) $request->input('etapa_id', 1);

        $data = $this->datasetCalificacionesFeria($feriaId, $etapaId);

        $headers = ['#', 'Código', 'Proyecto', 'Categoría', 'Escrito', 'Exposición', 'Final', 'Estado'];
        $rows = [];

        $i = 1;
        foreach ($data as $d) {
            $p = $d['proyecto'];

            $escrito = is_null($d['prom_escrito']) ? '—' : number_format($d['prom_escrito'], 2) . '%';
            $expo    = is_null($d['prom_expo'])    ? '—' : number_format($d['prom_expo'], 2) . '%';
            $final   = is_null($d['final'])        ? '—' : number_format($d['final'], 2) . '%';

            $pend = $d['pend_escrito'] || $d['pend_expo'];
            $estado = $pend
                ? '<span class="badge b-pend">Pendiente</span>'
                : '<span class="badge b-ok">OK</span>';

            $rows[] = [
                (string) $i++,
                e($p->codigo ?? ('PRJ-' . $p->id)),
                e($p->titulo),
                e($p->categoria?->nombre ?? 'Sin categoría'),
                '<div class="right">' . e($escrito) . '</div>',
                '<div class="right">' . e($expo) . '</div>',
                '<div class="right"><strong>' . e($final) . '</strong></div>',
                $estado,
            ];
        }

        $pdf = $this->generarPdfTabla(
            'Listado de calificaciones - General',
            $headers,
            $rows,
            "Feria ID {$feriaId} — Etapa {$etapaId}",
            'PRONAFECYT'
        );

        return $pdf->download("calificaciones-general-feria-{$feriaId}-etapa-{$etapaId}.pdf");
    }

    public function califPorCategoria(Request $request)
    {
        $feriaId = (int) $request->input('feria_id');
        $etapaId = (int) $request->input('etapa_id', 1);

        $data = $this->datasetCalificacionesFeria($feriaId, $etapaId);

        $groups = [];
        foreach ($data as $d) {
            $p = $d['proyecto'];
            $key = $p->categoria?->nombre ?? 'Sin categoría';
            $groups[$key][] = $d;
        }

        $headers = ['#', 'Código', 'Proyecto', 'Escrito', 'Exposición', 'Final', 'Estado'];
        $rows = [];

        foreach ($groups as $catName => $items) {
            $rows[] = ['<strong>' . e($catName) . '</strong>', '', '', '', '', '', ''];

            $i = 1;
            foreach ($items as $d) {
                $p = $d['proyecto'];

                $escrito = is_null($d['prom_escrito']) ? '—' : number_format($d['prom_escrito'], 2) . '%';
                $expo    = is_null($d['prom_expo'])    ? '—' : number_format($d['prom_expo'], 2) . '%';
                $final   = is_null($d['final'])        ? '—' : number_format($d['final'], 2) . '%';

                $pend = $d['pend_escrito'] || $d['pend_expo'];
                $estado = $pend
                    ? '<span class="badge b-pend">Pendiente</span>'
                    : '<span class="badge b-ok">OK</span>';

                $rows[] = [
                    (string) $i++,
                    e($p->codigo ?? ('PRJ-' . $p->id)),
                    e($p->titulo),
                    '<div class="right">' . e($escrito) . '</div>',
                    '<div class="right">' . e($expo) . '</div>',
                    '<div class="right"><strong>' . e($final) . '</strong></div>',
                    $estado,
                ];
            }
        }

        $pdf = $this->generarPdfTabla(
            'Listado de calificaciones - Por categoría',
            $headers,
            $rows,
            "Feria ID {$feriaId} — Etapa {$etapaId}",
            'PRONAFECYT'
        );

        return $pdf->download("calificaciones-por-categoria-feria-{$feriaId}-etapa-{$etapaId}.pdf");
    }

    public function califPorModalidad(Request $request)
    {
        $feriaId = (int) $request->input('feria_id');
        $etapaId = (int) $request->input('etapa_id', 1);

        $data = $this->datasetCalificacionesFeria($feriaId, $etapaId);

        $groups = [];
        foreach ($data as $d) {
            $p = $d['proyecto'];
            $key = $p->modalidad?->nombre ?? 'Sin modalidad';
            $groups[$key][] = $d;
        }

        $headers = ['#', 'Código', 'Proyecto', 'Categoría', 'Final', 'Estado'];
        $rows = [];

        foreach ($groups as $modName => $items) {
            $rows[] = ['<strong>' . e($modName) . '</strong>', '', '', '', '', ''];

            $i = 1;
            foreach ($items as $d) {
                $p = $d['proyecto'];

                $final = is_null($d['final']) ? '—' : number_format($d['final'], 2) . '%';
                $pend = $d['pend_escrito'] || $d['pend_expo'];
                $estado = $pend
                    ? '<span class="badge b-pend">Pendiente</span>'
                    : '<span class="badge b-ok">OK</span>';

                $rows[] = [
                    (string) $i++,
                    e($p->codigo ?? ('PRJ-' . $p->id)),
                    e($p->titulo),
                    e($p->categoria?->nombre ?? 'Sin categoría'),
                    '<div class="right"><strong>' . e($final) . '</strong></div>',
                    $estado,
                ];
            }
        }

        $pdf = $this->generarPdfTabla(
            'Listado de calificaciones - Por modalidad',
            $headers,
            $rows,
            "Feria ID {$feriaId} — Etapa {$etapaId}",
            'PRONAFECYT'
        );

        return $pdf->download("calificaciones-por-modalidad-feria-{$feriaId}-etapa-{$etapaId}.pdf");
    }
}
