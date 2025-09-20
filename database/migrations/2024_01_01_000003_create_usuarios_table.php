<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            
            // Multi-tenant fields
            $table->foreignId('regional_id')->nullable()->constrained('regionales');
            $table->foreignId('circuito_id')->nullable()->constrained('circuitos');
         //   $table->foreignId('institucion_id')->nullable()->constrained('instituciones');
             $table->unsignedBigInteger('institucion_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['regional_id', 'circuito_id', 'institucion_id']);
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
