<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rubrica extends Model
{
    protected $table = 'rubricas';

    protected $fillable = ['nombre', 'tipo_eval', 'ponderacion', 'modo', 'max_total'];

    public function criterios()
    {
        return $this->hasMany(Criterio::class);
    }
}
