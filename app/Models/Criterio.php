<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Criterio extends Model
{
    protected $table = 'criterios';

    protected $fillable = ['rubrica_id', 'nombre', 'peso', 'max_puntos', 'seccion'];

    public function rubrica()
    {
        return $this->belongsTo(Rubrica::class);
    }
}
