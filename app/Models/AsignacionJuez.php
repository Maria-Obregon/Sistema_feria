<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionJuez extends Model
{
    protected $table = 'asignaciones_jueces';

    protected $fillable = [
        'proyecto_id',
        'juez_id',
        'etapa_id',     // catálogo de etapas (o tinyint)
        'tipo_eval',    // escrito|exposicion|integral
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
