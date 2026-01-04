<?php

// app/Models/Invitado.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitado extends Model
{
    protected $fillable = [
        'feria_id',
        'nombre',
        'institucion',
        'puesto',
        'tipo_invitacion',
        'cedula',
        'sexo',
        'funcion',
        'telefono',
        'correo',
        'mensaje_agradecimiento',
    ];

    public function feria()
    {
        return $this->belongsTo(Feria::class);
    }
}
