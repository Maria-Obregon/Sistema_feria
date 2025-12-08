<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'proyectos';
protected $fillable = [
    'codigo',
    'titulo',
    'resumen',
    'area_id',
    'categoria_id',
    'institucion_id',
    'feria_id',
    'modalidad_id',
    'etapa_id',        
    'estado',
    'palabras_clave',
    'archivo_proyecto',
    'archivo_presentacion',
    'aula',
];

    protected function casts(): array
    {
        return ['palabras_clave' => 'array'];
    }

    // Recorta resumen a 250 palabras
    public function setResumenAttribute($value)
    {
        $text = (string)($value ?? '');
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > 250) $text = implode(' ', array_slice($words, 0, 250));
        $this->attributes['resumen'] = $text;
    }

    // Autogenerar código
    protected static function booted()
    {
        static::creating(function (Proyecto $p) {
            if (empty($p->codigo)) {
                $p->codigo = self::nuevoCodigo();
            }
        });
    }

    public static function nuevoCodigo(): string
    {
        do {
            $codigo = 'PRJ-'.date('y').'-'.Str::upper(Str::random(7));
        } while (self::where('codigo', $codigo)->exists());
        return $codigo;
    }

    // Relaciones
    public function institucion()  { return $this->belongsTo(Institucion::class); }
    public function estudiantes()  { return $this->belongsToMany(Estudiante::class, 'proyecto_estudiante'); }
    public function tutores()      { return $this->belongsToMany(Usuario::class, 'proyecto_tutor', 'proyecto_id', 'tutor_id'); }
    public function calificaciones(){ return $this->hasMany(Calificacion::class); }
    public function area()         { return $this->belongsTo(Area::class); }
    public function categoria()    { return $this->belongsTo(Categoria::class); }
    public function feria()        { return $this->belongsTo(Feria::class); }
    public function modalidad()    { return $this->belongsTo(Modalidad::class); }
    public function etapa()        { return $this->belongsTo(Etapa::class); }

    public function asignacionesJuez()
    {
        return $this->hasMany(\App\Models\AsignacionJuez::class);
    }

    /* Scopes opcionales */
    public function scopeDeEtapa($q, $etapaId) { return $q->where('etapa_id', $etapaId); }
    public function scopeDeModalidad($q, $modalidadId) { return $q->where('modalidad_id', $modalidadId); }
}
