<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juez extends Model
{
    use HasFactory;

    protected $table = 'jueces';

    protected $fillable = [
        'nombre',
        'cedula',
        'sexo',
        'telefono',
        'correo',
        'grado_academico',
        'area__id',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relación muchos a muchos con instituciones
    public function institutions()
    {
        return $this->belongsToMany(Institucion::class, 'juez_institucion', 'juez_id', 'institucion_id')
            ->withTimestamps();
    }

    // Relación uno a muchos con asignaciones
    public function assignments()
    {
        return $this->hasMany(AsignacionJuez::class, 'juez_id');
    }

    // Relación uno a uno con usuario (nullable)
    public function user()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación muchos a uno con área
    public function area()
    {
        return $this->belongsTo(Area::class, 'area__id');
    }
}
