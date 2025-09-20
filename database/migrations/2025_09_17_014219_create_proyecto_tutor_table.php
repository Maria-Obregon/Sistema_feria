<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyecto_tutor', function (Blueprint $table) {
            $table->id();
           // $table->foreignId('tutor_id')->constrained('usuarios')->onDelete('cascade');
  $table->foreignId('tutor_id')->constrained('tutores')->cascadeOnDelete();
    $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['proyecto_id', 'tutor_id']);
            $table->index('tutor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_tutor');
    }
};
