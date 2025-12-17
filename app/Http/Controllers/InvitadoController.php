<?php

// app/Http/Controllers/InvitadoController.php
namespace App\Http\Controllers;

use App\Models\Feria;
use App\Models\Invitado;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; 

class InvitadoController extends Controller
{
    // GET /api/ferias/{feria}/invitados
    public function index(Request $r, Feria $feria)
    {
        $q = $feria->invitados()->orderBy('nombre');

        if ($r->filled('tipo_invitacion')) {
            $q->where('tipo_invitacion', $r->tipo_invitacion);
        }

        if ($r->filled('buscar')) {
            $buscar = $r->buscar;
            $q->where(function ($w) use ($buscar) {
                $w->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('institucion', 'like', "%{$buscar}%")
                  ->orWhere('puesto', 'like', "%{$buscar}%");
            });
        }

        // puedes cambiar paginate(20) por lo que quieras
        return response()->json($q->paginate(20));
    }

    // POST /api/ferias/{feria}/invitados
    public function store(Request $r, Feria $feria)
    {
        $data = $r->validate([
            'nombre'           => 'required|string|max:200',
            'institucion'      => 'nullable|string|max:200',
            'puesto'           => 'nullable|string|max:200',
            'tipo_invitacion'  => 'required|in:dedicado,especial',
            'cedula'           => 'nullable|string|max:20',
            'sexo'             => 'nullable|string|max:10',
            'funcion'          => 'nullable|string|max:200',
            'telefono'         => 'nullable|string|max:30',
            'correo'           => 'nullable|email|max:120',
            'mensaje_agradecimiento' => 'nullable|string',
        ]);

        $data['feria_id'] = $feria->id;

        $inv = Invitado::create($data);

        return response()->json($inv, 201);
    }

    // PUT /api/invitados/{invitado}
    public function update(Request $r, Invitado $invitado)
    {
        $data = $r->validate([
            'nombre'           => 'sometimes|string|max:200',
            'institucion'      => 'nullable|string|max:200',
            'puesto'           => 'nullable|string|max:200',
            'tipo_invitacion'  => 'sometimes|in:dedicado,especial',
            'cedula'           => 'nullable|string|max:20',
            'sexo'             => 'nullable|string|max:10',
            'funcion'          => 'nullable|string|max:200',
            'telefono'         => 'nullable|string|max:30',
            'correo'           => 'nullable|email|max:120',
            'mensaje_agradecimiento' => 'nullable|string',
        ]);

        $invitado->update($data);

        return response()->json($invitado->fresh());
    }

    // DELETE /api/invitados/{invitado}
    public function destroy(Invitado $invitado)
    {
        $invitado->delete();
        return response()->json(['ok' => true]);
    }

    // GET /api/invitados/{invitado}/carta
    public function carta(Invitado $invitado)
    {
        $pdf = Pdf::loadView('invitados.carta', [
            'invitado' => $invitado->load('feria.circuito'),
        ]);

        return $pdf->download('Carta-'.$invitado->id.'.pdf');
    }

    public function carnet(Invitado $invitado)
    {
        $pdf = Pdf::loadView('invitados.carnet', [
            'invitado' => $invitado->load('feria.circuito'),
        ]);

        return $pdf->download('Carnet-'.$invitado->id.'.pdf');
    }
}
