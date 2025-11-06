<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TipoInstitucion extends Model {
    protected $table = 'tipos_institucion';
    protected $fillable = ['nombre','activo'];
    protected $casts = ['activo' => 'boolean'];
}

