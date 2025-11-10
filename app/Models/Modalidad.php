<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modalidad extends Model
{
    protected $table = 'modalidades';

    protected $fillable = ['nombre', 'activo', 'nivel_id'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /* Relaciones */
    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'categoria_modalidad');
    }

    public function etapas()
    {
        return $this->belongsToMany(Etapa::class, 'modalidad_etapa');
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }
}
