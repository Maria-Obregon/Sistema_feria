<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('feria', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('anio');
            $table->foreignId('institucion_id')->constrained('institucion')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('fecha')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->string('sede')->nullable();
            $table->integer('proyectos_por_aula')->nullable();
            $table->string('tipo_feria')->nullable();
            $table->string('correo_notif')->nullable();
            $table->string('telefono_fax')->nullable();
            $table->string('lugar_realizacion')->nullable();
            $table->foreignId('circuito_id')->constrained('circuito')->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('aula', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo');
            $table->foreignId('feria_id')->constrained('feria')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('estudiante', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->string('cedula')->unique();
            $table->date('fecha_nac')->nullable();
            $table->string('sexo', 10)->nullable();
            $table->string('telefono')->nullable();
            $table->boolean('lider')->default(false);
            $table->string('nivel')->nullable();
            $table->foreignId('institucion_id')->constrained('institucion')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuario')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tutor', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->string('cedula')->nullable();
            $table->string('grado_academico')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->foreignId('institucion_id')->constrained('institucion')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('juez', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->string('cedula')->unique();
            $table->string('sexo', 10)->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->string('grado_academico')->nullable();
            $table->foreignId('area_cientifica_id')->constrained('area_cientifica')->cascadeOnUpdate();
            $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuario')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('juez_institucion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('juez_id')->constrained('juez')->cascadeOnDelete();
            $table->foreignId('institucion_id')->constrained('institucion')->cascadeOnDelete();
            $table->unique(['juez_id','institucion_id']);
            $table->timestamps();
        });

        Schema::create('proyecto', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('titulo');
            $table->text('resumen')->nullable();
            $table->string('codigo')->unique();
            $table->foreignId('institucion_id')->constrained('institucion')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categoria')->cascadeOnUpdate();
            $table->foreignId('modalidad_id')->constrained('modalidad')->cascadeOnUpdate();
            $table->foreignId('area_cientifica_id')->constrained('area_cientifica')->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('proyecto');
        Schema::dropIfExists('juez_institucion');
        Schema::dropIfExists('juez');
        Schema::dropIfExists('tutor');
        Schema::dropIfExists('estudiante');
        Schema::dropIfExists('aula');
        Schema::dropIfExists('feria');
    }
};
