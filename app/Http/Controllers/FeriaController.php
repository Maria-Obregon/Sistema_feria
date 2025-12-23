<?php

namespace App\Http\Controllers;

use App\Models\Feria;
use App\Models\Institucion;
use App\Models\Circuito;
use App\Models\Regional;

use Illuminate\Http\Request;

class FeriaController extends Controller
{
    public function index(Request $r)
    {
        $q = Feria::with(['institucion', 'circuito', 'regional']);

        if ($r->filled('tipo_feria')) {
            $q->where('tipo_feria', $r->tipo_feria);
        }

        if ($r->filled('estado')) {
            $q->where('estado', $r->estado);
        }

        if ($r->filled('anio')) {
            $q->where('anio', $r->anio);
        }

        if ($r->filled('buscar')) {
            $buscar = $r->buscar;
            $q->whereHas('institucion', function ($w) use ($buscar) {
                $w->where('nombre', 'like', "%{$buscar}%");
            });
        }

        return response()->json(
            $q->orderByDesc('anio')->orderBy('tipo_feria')->paginate(15)
        );
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'anio'               => 'required|integer|min:2000|max:2100',
            'tipo_feria'         => 'required|in:institucional,circuital,regional',
            'institucion_id'     => 'nullable|exists:instituciones,id',
            'circuito_id'        => 'nullable|exists:circuitos,id',
            'regional_id'        => 'nullable|exists:regionales,id',
            'fecha'              => 'nullable|date',
            'hora_inicio'        => 'nullable|date_format:H:i',
            'sede'               => 'nullable|string|max:200',
            'proyectos_por_aula' => 'nullable|integer|min:0',
            'correo_notif'       => 'nullable|email|max:120',
            'telefono_fax'       => 'nullable|string|max:30',
            'lugar_realizacion'  => 'nullable|string|max:200',
            'estado'             => 'required|in:borrador,activa,finalizada',
        ]);

        if ($data['tipo_feria'] === 'institucional' && empty($data['institucion_id'])) {
            abort(422, 'institucion_id requerido para ferias institucionales');
        }
        if ($data['tipo_feria'] === 'circuital' && empty($data['circuito_id'])) {
            abort(422, 'circuito_id requerido para ferias circuitales');
        }
        if ($data['tipo_feria'] === 'regional' && empty($data['regional_id'])) {
            abort(422, 'regional_id requerido para ferias regionales');
        }

        $feria = Feria::create($data);

        return response()->json($feria->load(['institucion','circuito','regional']), 201);
    }

    public function update(Request $r, Feria $feria)
    {
        $data = $r->validate([
            'anio'               => 'sometimes|integer|min:2000|max:2100',
            'tipo_feria'         => 'sometimes|in:institucional,circuital,regional',
            'institucion_id'     => 'nullable|exists:instituciones,id',
            'circuito_id'        => 'nullable|exists:circuitos,id',
            'regional_id'        => 'nullable|exists:regionales,id',
            'fecha'              => 'nullable|date',
            'hora_inicio'        => 'nullable|date_format:H:i',
            'sede'               => 'nullable|string|max:200',
            'proyectos_por_aula' => 'nullable|integer|min:0',
            'correo_notif'       => 'nullable|email|max:120',
            'telefono_fax'       => 'nullable|string|max:30',
            'lugar_realizacion'  => 'nullable|string|max:200',
            'estado'             => 'sometimes|in:borrador,activa,finalizada',
        ]);

        $tipo = $data['tipo_feria'] ?? $feria->tipo_feria;
        $inst = $data['institucion_id'] ?? $feria->institucion_id;
        $circ = $data['circuito_id'] ?? $feria->circuito_id;
        $reg  = $data['regional_id'] ?? $feria->regional_id;

        if ($tipo === 'institucional' && empty($inst)) {
            abort(422, 'institucion_id requerido para ferias institucionales');
        }
        if ($tipo === 'circuital' && empty($circ)) {
            abort(422, 'circuito_id requerido para ferias circuitales');
        }
        if ($tipo === 'regional' && empty($reg)) {
            abort(422, 'regional_id requerido para ferias regionales');
        }

        $feria->update($data);

        return response()->json($feria->fresh()->load(['institucion','circuito','regional']));
    }

    public function destroy(Feria $feria)
    {
        if ($feria->proyectos()->exists()) {
            abort(422, 'No puede eliminar una feria con proyectos.');
        }

        $feria->delete();

        return response()->json(['ok' => true]);
    }

      public function formData()
    {
        $anioActual = now()->year;

        return response()->json([
            'tipos_feria' => [
                ['value' => 'institucional', 'label' => 'Institucional'],
                ['value' => 'circuital',     'label' => 'Circuital'],
                ['value' => 'regional',      'label' => 'Regional'],
            ],
            'estados' => [
                ['value' => 'borrador', 'label' => 'Borrador'],
                ['value' => 'activa',   'label' => 'Activa'],
                ['value' => 'finalizada',  'label' => 'Finalizada'],
            ],
            'anios' => [
                $anioActual - 1,
                $anioActual,
                $anioActual + 1,
            ],

            'regionales' => Regional::select('id', 'nombre')
                ->orderBy('nombre')
                ->get(),

            'circuitos' => Circuito::select('id', 'nombre', 'regional_id')
                ->with('regional:id,nombre')
                ->orderBy('nombre')
                ->get(),

            'instituciones' => Institucion::select('id', 'nombre', 'circuito_id')
                ->with('circuito:id,nombre,regional_id')
                ->orderBy('nombre')
                ->get(),
        ]);
    }
}
