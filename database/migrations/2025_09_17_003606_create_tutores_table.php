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
        Schema::create('tutores', function (Blueprint $table) {
        $table->id();
        $table->string('nombre', 200);
        $table->string('cedula', 20)->unique();
        $table->string('grado_academico', 120)->nullable();
        $table->string('telefono', 20)->nullable();
        $table->string('correo', 120)->nullable();
        $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutores');
    }
};
