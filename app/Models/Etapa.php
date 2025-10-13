<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etapa extends Model
{
    use HasFactory;

    protected $table = 'etapas';

    protected $fillable = [
        'nombre',
    ];

    protected function casts(): array
    {
        return [
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relación uno a muchos con asignaciones de jueces
    public function asignaciones()
    {
        return $this->hasMany(AsignacionJuez::class, 'etapa_id');
    }
}
