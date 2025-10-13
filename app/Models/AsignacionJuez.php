<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionJuez extends Model
{
    use HasFactory;

    protected $table = 'asignacion_juez';

    protected $fillable = [
        'proyecto_id',
        'juez_id',
        'etapa_id',
        'tipo_eval',
        'asignado_en',
    ];

    protected function casts(): array
    {
        return [
            'asignado_en' => 'datetime',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relación muchos a uno con proyecto
    public function project()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    // Relación muchos a uno con juez
    public function judge()
    {
        return $this->belongsTo(Juez::class, 'juez_id');
    }

    // Relación muchos a uno con etapa
    public function stage()
    {
        return $this->belongsTo(Etapa::class, 'etapa_id');
    }

    // Relación uno a muchos con calificaciones
    public function grades()
    {
        return $this->hasMany(Calificacion::class, 'asignacion_juez_id');
    }

    /**
     * Verifica si la asignación está completa
     * 
     * @return bool
     */
    public function isComplete(): bool
    {
        $rubrica = Rubrica::where('tipo_eval', $this->tipo_eval)->first();
        
        if (!$rubrica) {
            return false;
        }

        $criteriosIds = $rubrica->criterios()->pluck('id')->toArray();
        
        if (empty($criteriosIds)) {
            return false;
        }

        $calificadosIds = $this->grades()->pluck('criterio_id')->toArray();
        
        return count(array_diff($criteriosIds, $calificadosIds)) === 0;
    }
}
