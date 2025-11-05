<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Elimina la FK si existe (nombre clásico; si tu FK tiene otro nombre, ajústalo)
        try {
            DB::statement('ALTER TABLE `proyectos` DROP FOREIGN KEY `proyectos_modalidad_id_foreign`');
        } catch (\Throwable $e) {}

        // Vuelve la columna NULL
        DB::statement('ALTER TABLE `proyectos` MODIFY `modalidad_id` BIGINT UNSIGNED NULL');

        // Vuelve a crear la FK con ON DELETE SET NULL (si existe la tabla modalidades)
        if (Schema::hasTable('modalidades')) {
            DB::statement('ALTER TABLE `proyectos` 
                ADD CONSTRAINT `proyectos_modalidad_id_foreign`
                FOREIGN KEY (`modalidad_id`) REFERENCES `modalidades`(`id`)
                ON DELETE SET NULL
                ON UPDATE CASCADE');
        }
    }

    public function down(): void
    {
        // Si quieres revertir, vuelve a NOT NULL (ajústalo a tu realidad)
        try {
            DB::statement('ALTER TABLE `proyectos` DROP FOREIGN KEY `proyectos_modalidad_id_foreign`');
        } catch (\Throwable $e) {}

        DB::statement('ALTER TABLE `proyectos` MODIFY `modalidad_id` BIGINT UNSIGNED NOT NULL');

        if (Schema::hasTable('modalidades')) {
            DB::statement('ALTER TABLE `proyectos` 
                ADD CONSTRAINT `proyectos_modalidad_id_foreign`
                FOREIGN KEY (`modalidad_id`) REFERENCES `modalidades`(`id`)
                ON DELETE RESTRICT
                ON UPDATE CASCADE');
        }
    }
};
