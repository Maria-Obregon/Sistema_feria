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
        Schema::table('ferias', function (Blueprint $table) {
            // regional_id opcional, con FK hacia 'regionales'
            $table->foreignId('regional_id')
                ->nullable()
                ->after('circuito_id')
                ->constrained('regionales')
                ->nullOnDelete();

            // estado de la feria: borrador / activa / cerrada
            $table->string('estado', 20)
                ->default('borrador')
                ->after('regional_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ferias', function (Blueprint $table) {
            // Eliminar la FK y la columna regional_id
            $table->dropConstrainedForeignId('regional_id');

            // Eliminar la columna estado
            $table->dropColumn('estado');
        });
    }
};
