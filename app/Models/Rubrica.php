<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubrica extends Model
{
    use HasFactory;

    protected $table = 'rubricas';

    protected $fillable = [
        'nombre',
        'tipo_eval',
        'ponderacion',
    ];

    protected function casts(): array
    {
        return [
            'ponderacion' => 'decimal:2',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relación uno a muchos con criterios
    public function criterios()
    {
        return $this->hasMany(Criterio::class, 'rubrica_id');
    }
}
