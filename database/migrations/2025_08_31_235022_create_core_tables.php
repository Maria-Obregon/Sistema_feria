<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rol', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('rol_id')->constrained('rol')->cascadeOnUpdate();
            $table->string('email')->unique();
            // Usamos 'password' (no 'password_hash') para compatibilidad con Laravel
            $table->string('password');
            $table->boolean('activo')->default(true);
            $table->dateTime('creado_en')->useCurrent();
            $table->rememberToken();
        });

        Schema::create('direccion_regional', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('circuito', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->string('codigo')->nullable();
            $table->foreignId('direccion_regional_id')->constrained('direccion_regional')->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('institucion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->string('modalidad')->nullable();
            $table->string('tipo')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->string('codigo_presup')->nullable();
            $table->foreignId('direccionreg_id')->constrained('direccion_regional')->cascadeOnUpdate();
            $table->foreignId('circuito_id')->constrained('circuito')->cascadeOnUpdate();
            $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuario')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('categoria', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        Schema::create('modalidad', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        Schema::create('area_cientifica', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        Schema::create('etapa', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('nombre');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('etapa');
        Schema::dropIfExists('area_cientifica');
        Schema::dropIfExists('modalidad');
        Schema::dropIfExists('categoria');
        Schema::dropIfExists('institucion');
        Schema::dropIfExists('circuito');
        Schema::dropIfExists('direccion_regional');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('rol');
    }
};
