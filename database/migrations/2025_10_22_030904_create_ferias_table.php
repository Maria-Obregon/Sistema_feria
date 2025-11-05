<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si la tabla ya existe (porque otra migración la creó), no hagas nada.
        if (Schema::hasTable('ferias')) {
            return;
        }

        Schema::create('ferias', function (Blueprint $table) {
            $table->id();
            // agrega aquí las columnas reales de tu tabla ferias si las tenías
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Por seguridad (solo si existe)
        if (Schema::hasTable('ferias')) {
            Schema::dropIfExists('ferias');
        }
    }
};
