<?php
// app/Models/Feria.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feria extends Model
{
    protected $fillable = [
        'anio',
        'institucion_id',
        'fecha',
        'hora_inicio',
        'sede',
        'proyectos_por_aula',
        'tipo_feria',       // institucional, circuital, regional
        'correo_notif',
        'telefono_fax',
        'lugar_realizacion',
        'circuito_id',
        'estado',           // borrador, activa, finalizada...
    ];

    // Relaciones
    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }
public function invitados()
{
    return $this->hasMany(Invitado::class);
}

 public function colaboradores()
    {
        return $this->hasMany(Colaborador::class);
    }
    
    public function circuito()
    {
        return $this->belongsTo(Circuito::class);
    }

    public function regional()
    {
        // opcional, solo si luego agregas regional_id a la tabla
        return $this->belongsTo(Regional::class);
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }

    public function etapa()
    {
        return $this->belongsTo(Etapa::class);
    }
}
