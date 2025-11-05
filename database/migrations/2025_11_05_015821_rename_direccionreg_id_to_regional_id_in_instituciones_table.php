<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            // Renombrar la columna
            $table->renameColumn('direccionreg_id', 'regional_id');
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            // Volver al nombre anterior si haces rollback
            $table->renameColumn('regional_id', 'direccionreg_id');
        });
    }
};
