<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('proyecto_estudiante', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('estudiante_id')->constrained('estudiante')->cascadeOnDelete();
            $table->foreignId('proyecto_id')->constrained('proyecto')->cascadeOnDelete();
            $table->string('rol')->nullable();
            $table->unique(['proyecto_id','estudiante_id']);
            $table->timestamps();
        });

        Schema::create('proyecto_tutor', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tutor_id')->constrained('tutor')->cascadeOnDelete();
            $table->foreignId('proyecto_id')->constrained('proyecto')->cascadeOnDelete();
            $table->unique(['proyecto_id','tutor_id']);
            $table->timestamps();
        });

        Schema::create('asignacion_juez', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('proyecto_id')->constrained('proyecto')->cascadeOnDelete();
            $table->foreignId('juez_id')->constrained('juez')->cascadeOnDelete();
            $table->unsignedTinyInteger('etapa_id');
            $table->foreign('etapa_id')->references('id')->on('etapa')->cascadeOnUpdate();
            $table->string('tipo_eval'); // escrito | exposicion
            $table->dateTime('asignado_en')->useCurrent();
            $table->unique(['proyecto_id','juez_id','etapa_id','tipo_eval']);
            $table->timestamps();
        });

        Schema::create('rubrica', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->string('tipo_eval'); // escrito | exposicion
            $table->decimal('ponderacion',5,2)->default(100);
            $table->timestamps();
        });

        Schema::create('criterio', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('rubrica_id')->constrained('rubrica')->cascadeOnDelete();
            $table->string('nombre');
            $table->decimal('peso',5,2)->default(0);
            $table->integer('max_puntos')->default(10);
            $table->timestamps();
        });

        Schema::create('calificacion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('asignacion_juez_id')->constrained('asignacion_juez')->cascadeOnDelete();
            $table->foreignId('criterio_id')->constrained('criterio')->cascadeOnDelete();
            $table->integer('puntaje');
            $table->text('comentario')->nullable();
            $table->dateTime('creada_en')->useCurrent();
            $table->unique(['asignacion_juez_id','criterio_id']);
            $table->timestamps();
        });

        Schema::create('resultado_etapa', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('proyecto_id')->constrained('proyecto')->cascadeOnDelete();
            $table->unsignedTinyInteger('etapa_id');
            $table->foreign('etapa_id')->references('id')->on('etapa')->cascadeOnUpdate();
            $table->decimal('nota_escrito',5,2)->nullable();
            $table->decimal('nota_exposicion',5,2)->nullable();
            $table->decimal('nota_final',5,2)->nullable();
            $table->boolean('ganador')->default(false);
            $table->text('observaciones')->nullable();
            $table->unique(['proyecto_id','etapa_id']);
            $table->timestamps();
        });

        Schema::create('distribucion_aula', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('aula_id')->constrained('aula')->cascadeOnDelete();
            $table->foreignId('proyecto_id')->constrained('proyecto')->cascadeOnDelete();
            $table->unique(['aula_id','proyecto_id']);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('distribucion_aula');
        Schema::dropIfExists('resultado_etapa');
        Schema::dropIfExists('calificacion');
        Schema::dropIfExists('criterio');
        Schema::dropIfExists('rubrica');
        Schema::dropIfExists('asignacion_juez');
        Schema::dropIfExists('proyecto_tutor');
        Schema::dropIfExists('proyecto_estudiante');
    }
};
