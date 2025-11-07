<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('identificacion', 25)->nullable()->after('institucion_id');
            $table->string('tipo_identificacion', 20)->nullable()->after('identificacion');
            $table->unique('identificacion', 'usuarios_identificacion_unique');
        });
    }
    public function down(): void {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropUnique('usuarios_identificacion_unique');
            $table->dropColumn(['identificacion', 'tipo_identificacion']);
        });
    }
};

