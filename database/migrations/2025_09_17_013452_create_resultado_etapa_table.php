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
        Schema::create('resultado_etapa', function (Blueprint $table) {
            $table->id();
    $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
    $table->unsignedTinyInteger('etapa_id');
    $table->decimal('nota_escrito', 5,2)->nullable();
    $table->decimal('nota_exposicion', 5,2)->nullable();
    $table->decimal('nota_final', 5,2)->nullable();
    $table->boolean('ganador')->default(false);
    $table->text('observaciones')->nullable();
    $table->timestamps();

    $table->foreign('etapa_id')->references('id')->on('etapas')->restrictOnDelete();
    $table->unique(['proyecto_id','etapa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultado_etapa');
    }
};
