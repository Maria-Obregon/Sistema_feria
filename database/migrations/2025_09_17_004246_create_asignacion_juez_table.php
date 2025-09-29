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
        Schema::create('asignacion_juez', function (Blueprint $table) {
            $table->id();
    $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
    $table->foreignId('juez_id')->constrained('jueces')->cascadeOnDelete();
    $table->unsignedTinyInteger('etapa_id');
    $table->string('tipo_eval', 50)->nullable();
    $table->dateTime('asignado_en')->nullable();
    $table->timestamps();

    $table->foreign('etapa_id')->references('id')->on('etapas')->restrictOnDelete();
    $table->unique(['proyecto_id','juez_id','etapa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_juez');
    }
};
