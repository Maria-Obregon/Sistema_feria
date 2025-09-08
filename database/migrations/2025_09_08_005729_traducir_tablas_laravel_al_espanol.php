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
        // Renombrar tablas de Laravel al español
        Schema::rename('users', 'usuarios_sistema');
        Schema::rename('jobs', 'trabajos');
        Schema::rename('failed_jobs', 'trabajos_fallidos');
        Schema::rename('job_batches', 'lotes_trabajos');
        Schema::rename('cache', 'cache_sistema');
        Schema::rename('cache_locks', 'bloqueos_cache');
        Schema::rename('sessions', 'sesiones');
        Schema::rename('password_reset_tokens', 'tokens_restablecimiento_contrasena');

        // Traducir columnas de la tabla usuarios_sistema (ex users)
        Schema::table('usuarios_sistema', function (Blueprint $table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('email_verified_at', 'email_verificado_en');
            $table->renameColumn('password', 'contrasena');
            $table->renameColumn('remember_token', 'token_recordar');
            $table->renameColumn('created_at', 'creado_en');
            $table->renameColumn('updated_at', 'actualizado_en');
        });

        // Traducir columnas de trabajos
        Schema::table('trabajos', function (Blueprint $table) {
            $table->renameColumn('queue', 'cola');
            $table->renameColumn('payload', 'carga_util');
            $table->renameColumn('attempts', 'intentos');
            $table->renameColumn('reserved_at', 'reservado_en');
            $table->renameColumn('available_at', 'disponible_en');
            $table->renameColumn('created_at', 'creado_en');
        });

        // Traducir columnas de trabajos_fallidos
        Schema::table('trabajos_fallidos', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uuid');
            $table->renameColumn('connection', 'conexion');
            $table->renameColumn('queue', 'cola');
            $table->renameColumn('payload', 'carga_util');
            $table->renameColumn('exception', 'excepcion');
            $table->renameColumn('failed_at', 'fallo_en');
        });

        // Traducir columnas de lotes_trabajos
        Schema::table('lotes_trabajos', function (Blueprint $table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('total_jobs', 'total_trabajos');
            $table->renameColumn('pending_jobs', 'trabajos_pendientes');
            $table->renameColumn('failed_jobs', 'trabajos_fallidos');
            $table->renameColumn('failed_job_ids', 'ids_trabajos_fallidos');
            $table->renameColumn('options', 'opciones');
            $table->renameColumn('cancelled_at', 'cancelado_en');
            $table->renameColumn('created_at', 'creado_en');
            $table->renameColumn('finished_at', 'finalizado_en');
        });

        // Traducir columnas de cache_sistema
        Schema::table('cache_sistema', function (Blueprint $table) {
            $table->renameColumn('key', 'clave');
            $table->renameColumn('value', 'valor');
            $table->renameColumn('expiration', 'expiracion');
        });

        // Traducir columnas de bloqueos_cache
        Schema::table('bloqueos_cache', function (Blueprint $table) {
            $table->renameColumn('key', 'clave');
            $table->renameColumn('owner', 'propietario');
            $table->renameColumn('expiration', 'expiracion');
        });

        // Traducir columnas de sesiones
        Schema::table('sesiones', function (Blueprint $table) {
            $table->renameColumn('user_id', 'usuario_id');
            $table->renameColumn('ip_address', 'direccion_ip');
            $table->renameColumn('user_agent', 'agente_usuario');
            $table->renameColumn('payload', 'carga_util');
            $table->renameColumn('last_activity', 'ultima_actividad');
        });

        // Traducir columnas de tokens_restablecimiento_contrasena
        Schema::table('tokens_restablecimiento_contrasena', function (Blueprint $table) {
            $table->renameColumn('token', 'token');
            $table->renameColumn('created_at', 'creado_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir nombres de columnas antes de renombrar tablas
        Schema::table('tokens_restablecimiento_contrasena', function (Blueprint $table) {
            $table->renameColumn('creado_en', 'created_at');
        });

        Schema::table('sesiones', function (Blueprint $table) {
            $table->renameColumn('usuario_id', 'user_id');
            $table->renameColumn('direccion_ip', 'ip_address');
            $table->renameColumn('agente_usuario', 'user_agent');
            $table->renameColumn('carga_util', 'payload');
            $table->renameColumn('ultima_actividad', 'last_activity');
        });

        Schema::table('bloqueos_cache', function (Blueprint $table) {
            $table->renameColumn('clave', 'key');
            $table->renameColumn('propietario', 'owner');
            $table->renameColumn('expiracion', 'expiration');
        });

        Schema::table('cache_sistema', function (Blueprint $table) {
            $table->renameColumn('clave', 'key');
            $table->renameColumn('valor', 'value');
            $table->renameColumn('expiracion', 'expiration');
        });

        Schema::table('lotes_trabajos', function (Blueprint $table) {
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('total_trabajos', 'total_jobs');
            $table->renameColumn('trabajos_pendientes', 'pending_jobs');
            $table->renameColumn('trabajos_fallidos', 'failed_jobs');
            $table->renameColumn('ids_trabajos_fallidos', 'failed_job_ids');
            $table->renameColumn('opciones', 'options');
            $table->renameColumn('cancelado_en', 'cancelled_at');
            $table->renameColumn('creado_en', 'created_at');
            $table->renameColumn('finalizado_en', 'finished_at');
        });

        Schema::table('trabajos_fallidos', function (Blueprint $table) {
            $table->renameColumn('conexion', 'connection');
            $table->renameColumn('cola', 'queue');
            $table->renameColumn('carga_util', 'payload');
            $table->renameColumn('excepcion', 'exception');
            $table->renameColumn('fallo_en', 'failed_at');
        });

        Schema::table('trabajos', function (Blueprint $table) {
            $table->renameColumn('cola', 'queue');
            $table->renameColumn('carga_util', 'payload');
            $table->renameColumn('intentos', 'attempts');
            $table->renameColumn('reservado_en', 'reserved_at');
            $table->renameColumn('disponible_en', 'available_at');
            $table->renameColumn('creado_en', 'created_at');
        });

        Schema::table('usuarios_sistema', function (Blueprint $table) {
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('email_verificado_en', 'email_verified_at');
            $table->renameColumn('contrasena', 'password');
            $table->renameColumn('token_recordar', 'remember_token');
            $table->renameColumn('creado_en', 'created_at');
            $table->renameColumn('actualizado_en', 'updated_at');
        });

        // Revertir nombres de tablas
        Schema::rename('tokens_restablecimiento_contrasena', 'password_reset_tokens');
        Schema::rename('sesiones', 'sessions');
        Schema::rename('bloqueos_cache', 'cache_locks');
        Schema::rename('cache_sistema', 'cache');
        Schema::rename('lotes_trabajos', 'job_batches');
        Schema::rename('trabajos_fallidos', 'failed_jobs');
        Schema::rename('trabajos', 'jobs');
        Schema::rename('usuarios_sistema', 'users');
    }
};
