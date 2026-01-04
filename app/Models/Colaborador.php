<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Colaborador extends Model
{
    // 👇 Forzar el nombre correcto de la tabla
    protected $table = 'colaboradores';

    protected $fillable = [
        'feria_id',
        'nombre',
        'cedula',
        'sexo',
        'funcion',
        'telefono',
        'correo',
        'institucion',
        'mensaje_agradecimiento',
    ];

    public function feria()
    {
        return $this->belongsTo(Feria::class);
    }
}
