<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    use HasFactory;

    protected $table = 'calificaciones';

    protected $fillable = [
        'asignacion_juez_id',
        'criterio_id',
        'puntaje',
        'comentario',
    ];

    protected function casts(): array
    {
        return [
            'puntaje' => 'integer',
            'creada_en' => 'datetime',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relación muchos a uno con asignación de juez
    public function assignment()
    {
        return $this->belongsTo(AsignacionJuez::class, 'asignacion_juez_id');
    }

    // Relación muchos a uno con criterio
    public function criterion()
    {
        return $this->belongsTo(Criterio::class, 'criterio_id');
    }
}
