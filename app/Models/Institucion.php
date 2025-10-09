<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    use HasFactory;

    protected $table = 'instituciones';
 
    // Configurar nombres de timestamps en español
   const CREATED_AT = 'creado_en';
const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
    'nombre',
    'modalidad',          // <-- FALTA
    'codigo_presupuestario',
    'direccionreg_id',    // <-- FALTA
    'circuito_id',
    'tipo',
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
            'activo' => 'boolean',
            'limite_proyectos' => 'integer',
            'limite_estudiantes' => 'integer',
        ];
    }

    public function circuito()
    {
        return $this->belongsTo(Circuito::class);
    }

    // app/Models/Institucion.php
public function tipoInstitucion() { return $this->belongsTo(\App\Models\TipoInstitucion::class, 'tipo_institucion_id'); }
public function modalidad()       { return $this->belongsTo(\App\Models\Modalidad::class, 'modalidad_id'); }

    
public function regional() { return $this->belongsTo(\App\Models\Regional::class, 'direccionreg_id'); }

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

    // Validación de límites
    public function puedeAgregarProyecto(): bool
    {
        return $this->proyectos()->count() < ($this->limite_proyectos ?? 50);
    }

    public function puedeAgregarEstudiante(): bool
    {
        return $this->estudiantes()->count() < ($this->limite_estudiantes ?? 200);
    }
}
