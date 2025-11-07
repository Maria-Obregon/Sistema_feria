<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    use HasFactory;

    protected $table = 'instituciones';

    // Timestamps con nombres en español (si tu tabla los usa así)
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'codigo_presupuestario',
        'regional_id',        // <- AHORA este es el nombre oficial
        'circuito_id',
        'modalidad',          // string: Primaria|Secundaria|Técnica
        'tipo',               // publica|privada|subvencionada
        'telefono',
        'email',
        'direccion',
        'activo',
        'limite_proyectos',
        'limite_estudiantes',
    ];

    protected function casts(): array
    {
        return [
            'activo'             => 'boolean',
            'limite_proyectos'   => 'integer',
            'limite_estudiantes' => 'integer',
        ];
    }

    public function circuito()
    {
        return $this->belongsTo(Circuito::class);
    }

    public function regional()
    {
        return $this->belongsTo(Regional::class, 'regional_id');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class);
    }

    // Reglas de negocio
    public function puedeAgregarProyecto(): bool
    {
        return $this->proyectos()->count() < ($this->limite_proyectos ?? 50);
    }

    public function puedeAgregarEstudiante(): bool
    {
        return $this->estudiantes()->count() < ($this->limite_estudiantes ?? 200);
    }
}
