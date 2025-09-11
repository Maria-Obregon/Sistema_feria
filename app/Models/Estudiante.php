<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiantes';

    protected $fillable = [
        'cedula',
        'nombre',
        'apellidos',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'email',
        'institucion_id',
        'nivel',
        'seccion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_estudiante');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    // Scope para filtrar por institución
    public function scopeDeInstitucion($query, $institucionId)
    {
        return $query->where('institucion_id', $institucionId);
    }

    // Obtener edad
    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null;
    }
}
