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
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->decimal('puntaje', 8, 2)->change();
        });

        Schema::table('criterios', function (Blueprint $table) {
            $table->decimal('max_puntos', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->integer('puntaje')->change();
        });

        Schema::table('criterios', function (Blueprint $table) {
            $table->unsignedInteger('max_puntos')->change();
        });
    }
};
