<?php
// app/Models/Nivel.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Nivel extends Model {
    protected $table = 'niveles';
    protected $fillable = ['nombre','activo'];
    protected $casts = ['activo'=>'boolean'];
}
