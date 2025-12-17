<?php

namespace App\Http\Controllers;

use App\Models\Feria;
use App\Models\Colaborador;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ColaboradorController extends Controller
{
    // GET /api/ferias/{feria}/colaboradores
    public function index(Request $r, Feria $feria)
    {
        $q = $feria->colaboradores()->orderBy('nombre');

        if ($r->filled('buscar')) {
            $buscar = $r->buscar;
            $q->where(function ($w) use ($buscar) {
                $w->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('institucion', 'like', "%{$buscar}%")
                  ->orWhere('funcion', 'like', "%{$buscar}%");
            });
        }

        return response()->json($q->paginate(20));
    }

    // POST /api/ferias/{feria}/colaboradores
    public function store(Request $r, Feria $feria)
    {
        $data = $r->validate([
            'nombre'                => 'required|string|max:200',
            'cedula'                => 'nullable|string|max:20',
            'sexo'                  => 'nullable|string|max:15',
            'funcion'               => 'nullable|string|max:200',
            'telefono'              => 'nullable|string|max:30',
            'correo'                => 'nullable|email|max:120',
            'institucion'           => 'nullable|string|max:200',
            'mensaje_agradecimiento'=> 'nullable|string',
        ]);

        $data['feria_id'] = $feria->id;

        $col = Colaborador::create($data);

        return response()->json($col, 201);
    }

    // PUT /api/colaboradores/{colaborador}
    public function update(Request $r, Colaborador $colaborador)
    {
        $data = $r->validate([
            'nombre'                => 'sometimes|string|max:200',
            'cedula'                => 'nullable|string|max:20',
            'sexo'                  => 'nullable|string|max:15',
            'funcion'               => 'nullable|string|max:200',
            'telefono'              => 'nullable|string|max:30',
            'correo'                => 'nullable|email|max:120',
            'institucion'           => 'nullable|string|max:200',
            'mensaje_agradecimiento'=> 'nullable|string',
        ]);

        $colaborador->update($data);

        return response()->json($colaborador->fresh());
    }

    // DELETE /api/colaboradores/{colaborador}
    public function destroy(Colaborador $colaborador)
    {
        $colaborador->delete();
        return response()->json(['ok' => true]);
    }

    // GET /api/colaboradores/{colaborador}/carta
    public function carta(Colaborador $colaborador)
    {
        $colaborador->load('feria.circuito');

        $pdf = Pdf::loadView('colaboradores.carta', [
            'colaborador' => $colaborador,
        ])->setPaper('letter', 'portrait');

        return $pdf->download('Carta-colaborador-'.$colaborador->id.'.pdf');
    }

    // GET /api/colaboradores/{colaborador}/carnet
    public function carnet(Colaborador $colaborador)
    {
        $colaborador->load('feria.circuito');

        $pdf = Pdf::loadView('colaboradores.carnet', [
            'colaborador' => $colaborador,
        ])->setPaper('letter', 'landscape');

        return $pdf->download('Carnet-colaborador-'.$colaborador->id.'.pdf');
    }
}
