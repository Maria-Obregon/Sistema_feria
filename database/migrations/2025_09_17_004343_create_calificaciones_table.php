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
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
    $table->foreignId('asignacion_juez_id')->constrained('asignacion_juez')->cascadeOnDelete();
    $table->foreignId('criterio_id')->constrained('criterios')->cascadeOnDelete();
    $table->integer('puntaje')->default(0);
    $table->text('comentario')->nullable();
    $table->dateTime('creada_en')->nullable();
    $table->timestamps();

    $table->unique(['asignacion_juez_id','criterio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
