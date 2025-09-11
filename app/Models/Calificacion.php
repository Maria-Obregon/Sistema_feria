<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    use HasFactory;

    protected $table = 'calificaciones';

    protected $fillable = [
        'proyecto_id',
        'juez_id',
        'etapa',
        'calificacion_escrita',
        'calificacion_oral',
        'observaciones',
        'fecha_calificacion',
        'finalizada',
    ];

    protected function casts(): array
    {
        return [
            'calificacion_escrita' => 'decimal:2',
            'calificacion_oral' => 'decimal:2',
            'fecha_calificacion' => 'datetime',
            'finalizada' => 'boolean',
        ];
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function juez()
    {
        return $this->belongsTo(Usuario::class, 'juez_id');
    }

    // Calcular calificación total con ponderación
    public function getCalificacionTotalAttribute()
    {
        $ponderacionEscrita = 0.6; // 60% escrita
        $ponderacionOral = 0.4;    // 40% oral
        
        return ($this->calificacion_escrita * $ponderacionEscrita) + 
               ($this->calificacion_oral * $ponderacionOral);
    }
}
