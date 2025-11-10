<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etapa extends Model
{
    protected $fillable = ['nombre'];

    // helpers
    public const INSTITUCIONAL = 'Institucional';
    public const CIRCUITAL     = 'Circuital';
    public const REGIONAL      = 'Regional';

    public function ferias()     { return $this->hasMany(Feria::class); }
    public function modalidades(){ return $this->belongsToMany(Modalidad::class, 'modalidad_etapa'); }
    public function proyectos()  { return $this->hasMany(Proyecto::class); } // si usás etapa_id en proyectos

}
