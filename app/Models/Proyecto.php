<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'proyectos';

    protected $fillable = [
        'titulo',
        'resumen',
        'area_id',
        'categoria_id',
        'institucion_id',
        'modalidad_id',
        'etapa_actual',
        'estado',
        'codigo',
        'palabras_clave',
        'archivo_proyecto',
        'archivo_presentacion',
    ];

    protected function casts(): array
    {
        return [
            'palabras_clave' => 'array',
        ];
    }

    // Validación de resumen (máximo 250 palabras)
    public function setResumenAttribute($value)
    {
        $wordCount = str_word_count($value);
        if ($wordCount > 250) {
            throw new \Exception('El resumen no puede exceder las 250 palabras');
        }
        $this->attributes['resumen'] = $value;
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'proyecto_estudiante');
    }

    public function tutores()
    {
        return $this->belongsToMany(Usuario::class, 'proyecto_tutor', 'proyecto_id', 'tutor_id');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }

    public function asignacionesJuez()
    {
        return $this->hasMany(AsignacionJuez::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }
}
