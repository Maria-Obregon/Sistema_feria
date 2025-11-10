<?php
// database/migrations/2025_11_10_100200_create_modalidad_etapa_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('modalidad_etapa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modalidad_id')->constrained('modalidades')->cascadeOnDelete();
            $table->unsignedTinyInteger('etapa_id');
            $table->foreign('etapa_id')->references('id')->on('etapas')->cascadeOnDelete();
            $table->unique(['modalidad_id','etapa_id']);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('modalidad_etapa');
    }
};
