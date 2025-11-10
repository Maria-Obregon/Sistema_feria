<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Juez extends Model
{
    protected $table = 'jueces';

    protected $fillable = [
        'nombre',
        'cedula',
        'sexo',
        'telefono',
        'correo',
        'grado_academico',
        'area_id',
        'usuario_id',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionJuez::class, 'juez_id');
    }

    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'asignaciones_jueces', 'juez_id', 'proyecto_id')
            ->withPivot(['etapa_id','tipo_eval'])
            ->withTimestamps();
/*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Devuelve los proyectos a los que est  asignado el juez actual.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
/*******  8b5ab102-ec9f-458f-9fd7-4f4d819843f6  *******/    }
}
