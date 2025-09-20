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
        Schema::create('distribucion_aula', function (Blueprint $table) {
              $table->id();
    $table->foreignId('aula_id')->constrained('aulas')->cascadeOnDelete();
    $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
    $table->timestamps();

    $table->unique(['aula_id','proyecto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribucion_aula');
    }
};
