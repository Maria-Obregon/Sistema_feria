<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 20)->unique();
            $table->string('nombre', 100);
            $table->string('apellidos', 100);
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['M', 'F', 'Otro']);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('restrict');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->string('nivel', 50); // Primaria, Secundaria, etc.
            $table->string('seccion', 10)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['institucion_id', 'activo']);
            $table->index('cedula');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
