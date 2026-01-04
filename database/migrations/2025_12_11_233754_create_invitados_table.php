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
        Schema::create('invitados', function (Blueprint $table) {
            $table->id();

            // Invitado SIEMPRE ligado a una feria
            $table->foreignId('feria_id')
                ->constrained('ferias')
                ->cascadeOnDelete();

            // Pantalla "Invitados"
            $table->string('nombre', 200);
            $table->string('institucion', 200)->nullable();
            $table->string('puesto', 200)->nullable();
            // Dedicado / Invitado especial
            $table->enum('tipo_invitacion', ['dedicado', 'especial']);

            // Datos personales (otra pantalla)
            $table->string('cedula', 20)->nullable();
            $table->string('sexo', 10)->nullable();          // 'M', 'F', etc.
            $table->string('funcion', 200)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('correo', 120)->nullable();
            $table->text('mensaje_agradecimiento')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitados');
    }
};
