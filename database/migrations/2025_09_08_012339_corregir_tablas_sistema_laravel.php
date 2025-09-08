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
        // Revertir cambios en tabla sesiones para mantener compatibilidad con Laravel
        if (Schema::hasTable('sesiones')) {
            Schema::table('sesiones', function (Blueprint $table) {
                if (Schema::hasColumn('sesiones', 'carga_util')) {
                    $table->renameColumn('carga_util', 'payload');
                }
                if (Schema::hasColumn('sesiones', 'ultima_actividad')) {
                    $table->renameColumn('ultima_actividad', 'last_activity');
                }
                if (Schema::hasColumn('sesiones', 'usuario_id')) {
                    $table->renameColumn('usuario_id', 'user_id');
                }
                if (Schema::hasColumn('sesiones', 'direccion_ip')) {
                    $table->renameColumn('direccion_ip', 'ip_address');
                }
                if (Schema::hasColumn('sesiones', 'agente_usuario')) {
                    $table->renameColumn('agente_usuario', 'user_agent');
                }
            });
        }

        // Revertir cambios en tabla cache_sistema para mantener compatibilidad
        if (Schema::hasTable('cache_sistema')) {
            Schema::table('cache_sistema', function (Blueprint $table) {
                if (Schema::hasColumn('cache_sistema', 'expiracion')) {
                    $table->renameColumn('expiracion', 'expiration');
                }
            });
        }

        // Revertir cambios en tabla trabajos para mantener compatibilidad
        if (Schema::hasTable('trabajos')) {
            Schema::table('trabajos', function (Blueprint $table) {
                if (Schema::hasColumn('trabajos', 'cola')) {
                    $table->renameColumn('cola', 'queue');
                }
                if (Schema::hasColumn('trabajos', 'carga_util')) {
                    $table->renameColumn('carga_util', 'payload');
                }
                if (Schema::hasColumn('trabajos', 'intentos')) {
                    $table->renameColumn('intentos', 'attempts');
                }
                if (Schema::hasColumn('trabajos', 'reservado_en')) {
                    $table->renameColumn('reservado_en', 'reserved_at');
                }
                if (Schema::hasColumn('trabajos', 'disponible_en')) {
                    $table->renameColumn('disponible_en', 'available_at');
                }
                if (Schema::hasColumn('trabajos', 'creado_en')) {
                    $table->renameColumn('creado_en', 'created_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios si es necesario
        if (Schema::hasTable('sesiones')) {
            Schema::table('sesiones', function (Blueprint $table) {
                if (Schema::hasColumn('sesiones', 'payload')) {
                    $table->renameColumn('payload', 'carga_util');
                }
                if (Schema::hasColumn('sesiones', 'last_activity')) {
                    $table->renameColumn('last_activity', 'ultima_actividad');
                }
                if (Schema::hasColumn('sesiones', 'user_id')) {
                    $table->renameColumn('user_id', 'usuario_id');
                }
                if (Schema::hasColumn('sesiones', 'ip_address')) {
                    $table->renameColumn('ip_address', 'direccion_ip');
                }
                if (Schema::hasColumn('sesiones', 'user_agent')) {
                    $table->renameColumn('user_agent', 'agente_usuario');
                }
            });
        }
    }
};
