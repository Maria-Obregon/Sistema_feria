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
        Schema::table('usuarios', function (Blueprint $table) {
            $table->text('secreto_dos_factores')->nullable()->after('contrasena');
            $table->text('codigos_recuperacion_dos_factores')->nullable()->after('secreto_dos_factores');
            $table->timestamp('dos_factores_confirmado_en')->nullable()->after('codigos_recuperacion_dos_factores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn([
                'secreto_dos_factores',
                'codigos_recuperacion_dos_factores',
                'dos_factores_confirmado_en'
            ]);
        });
    }
};