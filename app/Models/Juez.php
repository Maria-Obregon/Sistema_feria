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
        return $this->belongsTo(\App\Models\Area::class, 'area__id');
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
            ->withPivot(['etapa_id', 'tipo_eval'])
            ->withTimestamps();
    }

    public function instituciones()
    {
        return $this->belongsToMany(\App\Models\Institucion::class, 'juez_institucion', 'juez_id', 'institucion_id')
            ->withTimestamps();
    }

    public function institucionPrincipal(): ?\App\Models\Institucion
    {
        if ($this->usuario && $this->usuario->institucion) {
            return $this->usuario->institucion;
        }

        return $this->instituciones()->first();
    }
}
