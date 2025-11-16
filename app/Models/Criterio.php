<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criterio extends Model
{
    use HasFactory;

    protected $table = 'criterios';

    protected $fillable = [
        'rubrica_id',
        'nombre',
        'peso',
        'max_puntos',
    ];

    protected function casts(): array
    {
        return [
            'peso' => 'decimal:2',
            'max_puntos' => 'integer',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relación muchos a uno con rúbrica
    public function rubrica()
    {
        return $this->belongsTo(Rubrica::class, 'rubrica_id');
    }

    // Relación uno a muchos con calificaciones
    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'criterio_id');
    }
}
