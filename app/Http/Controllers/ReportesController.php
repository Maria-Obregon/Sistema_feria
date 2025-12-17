<?php

namespace App\Http\Controllers;

use App\Models\Feria;
use App\Models\Estudiante;
use App\Models\Juez;
use App\Models\Invitado;
use App\Models\Colaborador;
use App\Models\Proyecto;
use App\Models\Calificacion;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportesController extends Controller
{
    // ==========================================================
    // RESUMEN DE ESTADÍSTICAS POR FERIA
    // GET /api/reportes/resumen?feria_id=ID
    // ==========================================================
    public function resumen(Request $request)
    {
        $feriaId = $request->input('feria_id');

        if (!$feriaId) {
            return response()->json([
                'estudiantes'   => 0,
                'jueces'        => 0,
                'invitados'     => 0,
                'colaboradores' => 0,
            ]);
        }

        $feria = Feria::find($feriaId);

        if (!$feria) {
            return response()->json([
                'estudiantes'   => 0,
                'jueces'        => 0,
                'invitados'     => 0,
                'colaboradores' => 0,
            ]);
        }

        // Invitados y colaboradores sí tienen feria_id directo
        $invitados     = Invitado::where('feria_id', $feriaId)->count();
        $colaboradores = Colaborador::where('feria_id', $feriaId)->count();

        return response()->json([
            'estudiantes'   => 0, // TODO: cuando definamos relación estudiante–feria
            'jueces'        => 0, // TODO: idem
            'invitados'     => $invitados,
            'colaboradores' => $colaboradores,
        ]);
    }

    // ==========================================================
    // LISTAS POR FERIA (PARA AdminReportesListado.vue)
    // ==========================================================

    // Por ahora devolvemos vacío hasta definir la relación estudiante–feria
    public function feriaEstudiantes(Request $request)
    {
        return response()->json([
            'data' => [],
        ]);
    }

    // Igual para jueces (cuando tengamos claro cómo se relacionan con la feria)
    public function feriaJueces(Request $request)
    {
        return response()->json([
            'data' => [],
        ]);
    }

    // Invitados especiales de una feria
    public function feriaInvitados(Request $request)
    {
        $feriaId = $request->input('feria_id');

        if (!$feriaId) {
            return response()->json(['data' => []]);
        }

        $items = Invitado::where('feria_id', $feriaId)
            ->orderBy('nombre')
            ->get();

        return response()->json(['data' => $items]);
    }

    // Colaboradores de una feria
    public function feriaColaboradores(Request $request)
    {
        $feriaId = $request->input('feria_id');

        if (!$feriaId) {
            return response()->json(['data' => []]);
        }

        $items = Colaborador::where('feria_id', $feriaId)
            ->orderBy('nombre')
            ->get();

        return response()->json(['data' => $items]);
    }

    // ==========================================================
    // HELPER PARA REPORTES SENCILLOS (SOLO LISTAS, NO CERTIFICADOS)
    // ==========================================================
    protected function generarPdfSencillo(string $titulo, string $contenido, string $pie = '')
    {
        $html = view('reportes.plantilla_simple', [
            'titulo'    => $titulo,
            'contenido' => $contenido,
            'pie'       => $pie,
        ])->render();

        return Pdf::loadHTML($html);
    }

    // ==========================================================
    // CERTIFICADOS INDIVIDUALES (OFICIALES, UNO POR PERSONA)
    // ==========================================================

    // Cuando tengas la plantilla oficial de estudiante, la conectamos aquí.
    public function certificadoEstudiante(Estudiante $estudiante)
    {
        // TODO: cambiar luego a una vista tipo: reportes.cert_estudiante_participacion
        $pdf = $this->generarPdfSencillo(
            'Certificado de participación - Estudiante',
            "Se certifica la participación de {$estudiante->nombre} como estudiante en la feria.",
            'PRONAFECYT'
        );

        return $pdf->download("certificado-estudiante-{$estudiante->id}.pdf");
    }

    // Para juez, de momento algo simple hasta que definamos bien la feria / diseño final
   public function certificadoJuez(Juez $juez)
{
    $feria = $juez->feria ?? ($juez->feria_id ?? null
        ? Feria::find($juez->feria_id)
        : null);

    $pdf = Pdf::loadView('certificados.cert_juez', [
        'juez'  => $juez,
        'feria' => $feria,
    ])->setPaper('letter', 'landscape'); // carta apaisado como los otros

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
    // LISTADOS DE CALIFICACIONES (USAN plantilla_simple)
    // ==========================================================
    public function califInformeEscrito(Request $request)
    {
        $feriaId = $request->input('feria_id');

        $pdf = $this->generarPdfSencillo(
            'Listado de calificaciones - Informe escrito',
            "Listado de calificaciones del informe escrito para la feria ID {$feriaId}. (Pendiente de implementación completa)",
            'PRONAFECYT'
        );

        return $pdf->download("calificaciones-informe-escrito-feria-{$feriaId}.pdf");
    }

    public function califGeneral(Request $request)
    {
        $feriaId = $request->input('feria_id');

        $pdf = $this->generarPdfSencillo(
            'Listado de calificaciones - General',
            "Listado de calificaciones generales para la feria ID {$feriaId}. (Pendiente de implementación completa)",
            'PRONAFECYT'
        );

        return $pdf->download("calificaciones-general-feria-{$feriaId}.pdf");
    }

    public function califPorCategoria(Request $request)
    {
        $feriaId = $request->input('feria_id');

        $pdf = $this->generarPdfSencillo(
            'Listado de calificaciones - Por categoría',
            "Listado de calificaciones por categoría para la feria ID {$feriaId}. (Pendiente de implementación completa)",
            'PRONAFECYT'
        );

        return $pdf->download("calificaciones-por-categoria-feria-{$feriaId}.pdf");
    }

    public function califPorModalidad(Request $request)
    {
        $feriaId = $request->input('feria_id');

        $pdf = $this->generarPdfSencillo(
            'Listado de calificaciones - Por modalidad',
            "Listado de calificaciones por modalidad para la feria ID {$feriaId}. (Pendiente de implementación completa)",
            'PRONAFECYT'
        );

        return $pdf->download("calificaciones-por-modalidad-feria-{$feriaId}.pdf");
    }
}
