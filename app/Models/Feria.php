<?php
//app/Models/Feria.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Feria extends Model
{
  protected $fillable = ['nombre','tipo','institucion_id','circuito_id','regional_id','fecha_inicio','fecha_fin','estado'];
  public function institucion(){ return $this->belongsTo(Institucion::class); }
  public function circuito(){ return $this->belongsTo(Circuito::class); }
  public function regional(){ return $this->belongsTo(Regional::class); }
  public function proyectos(){ return $this->hasMany(Proyecto::class); }
  public function etapa() { return $this->belongsTo(Etapa::class); }
}
