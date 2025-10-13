<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultadoEtapa extends Model
{
    use HasFactory;

    protected $table = 'resultado_etapa';

    protected $fillable = [
        'proyecto_id',
        'etapa_id',
        'nota_escrito',
        'nota_exposicion',
        'nota_final',
        'ganador',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'nota_escrito' => 'decimal:2',
            'nota_exposicion' => 'decimal:2',
            'nota_final' => 'decimal:2',
            'ganador' => 'boolean',
        ];
    }

    // Relación muchos a uno con proyecto
    public function project()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    // Relación muchos a uno con etapa
    public function stage()
    {
        return $this->belongsTo(Etapa::class, 'etapa_id');
    }
}
