<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ferias', function (Blueprint $table) {
          $table->id();
    $table->integer('anio');
    $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
    $table->date('fecha')->nullable();
    $table->time('hora_inicio')->nullable();
    $table->string('sede', 200)->nullable();
    $table->integer('proyectos_por_aula')->default(0);
    $table->string('tipo_feria', 120)->nullable();
    $table->string('correo_notif', 120)->nullable();
    $table->string('telefono_fax', 30)->nullable();
    $table->string('lugar_realizacion', 200)->nullable();
    $table->foreignId('circuito_id')->nullable()->constrained('circuitos')->nullOnDelete();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ferias');
    }
};
