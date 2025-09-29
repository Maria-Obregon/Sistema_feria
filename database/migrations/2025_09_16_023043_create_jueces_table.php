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
        Schema::create('jueces', function (Blueprint $table) {
          $table->id();
    $table->string('nombre', 200);
    $table->string('cedula', 20)->unique();
    $table->string('sexo', 20)->nullable();
    $table->string('telefono', 20)->nullable();
    $table->string('correo', 120)->nullable();
    $table->string('grado_academico', 120)->nullable();
    $table->foreignId('area__id')->nullable()->constrained('areas')->nullOnDelete();
    $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuarios')->nullOnDelete();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jueces');
    }
};
