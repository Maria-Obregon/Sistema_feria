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
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->id();

            // Cada colaborador pertenece a una feria
            $table->foreignId('feria_id')
                ->constrained('ferias')
                ->cascadeOnDelete();

            // Campos del formulario
            $table->string('nombre', 200);
            $table->string('cedula', 20)->nullable();
            $table->string('sexo', 20)->nullable();          // Femenino, Masculino, etc.
            $table->string('funcion', 200)->nullable();      // Profesor, juez, etc.
            $table->string('telefono', 30)->nullable();
            $table->string('correo', 120)->nullable();
            $table->string('institucion', 200)->nullable();
            $table->text('mensaje_agradecimiento')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colaboradores');
    }
};
