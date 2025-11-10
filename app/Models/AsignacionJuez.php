<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionJuez extends Model
{
    protected $table = 'asignaciones_jueces';

    protected $fillable = [
        'proyecto_id',
        'juez_id',
        'etapa_id',     // 1=Institucional, 2=Circuital, 3=Regional (ejemplo)
        'tipo_eval',    // escrito|exposicion|integral
    ];

    protected $casts = [
        'proyecto_id' => 'integer',
        'juez_id'     => 'integer',
        'etapa_id'    => 'integer',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function juez()
    {
        return $this->belongsTo(Juez::class);
    }
}
