<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache de permisos/roles
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ---- Crear/asegurar permisos (idempotente) ----
        $all = [
            // Usuarios
            'usuarios.ver','usuarios.crear','usuarios.editar','usuarios.eliminar',

            // Instituciones
            'instituciones.ver','instituciones.crear','instituciones.editar','instituciones.eliminar',

            // Proyectos
            'proyectos.ver','proyectos.crear','proyectos.editar','proyectos.eliminar','proyectos.calificar',

            // Estudiantes
            'estudiantes.ver','estudiantes.crear','estudiantes.editar','estudiantes.eliminar',

            // Jueces
            'jueces.ver','jueces.crear','jueces.editar','jueces.eliminar','jueces.asignar',

            // Ferias
            'ferias.ver','ferias.crear','ferias.editar','ferias.eliminar','ferias.cerrar_etapa',

            // Calificaciones
            'calificaciones.ver','calificaciones.crear','calificaciones.consolidar',

            // Reportes
            'reportes.ver','reportes.exportar',

            // Certificados
            'certificados.ver','certificados.generar',

            // Administración
            'admin.configuracion','admin.respaldos','admin.logs',
        ];

        foreach ($all as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'sanctum']);
        }

        // ---- Roles (idempotente) ----
        $roleEstudiante          = Role::firstOrCreate(['name' => 'estudiante',           'guard_name' => 'sanctum']);
        $roleJuez                = Role::firstOrCreate(['name' => 'juez',                 'guard_name' => 'sanctum']);
        $roleComite              = Role::firstOrCreate(['name' => 'comite_institucional', 'guard_name' => 'sanctum']);
        $roleCoordinadorCircuito = Role::firstOrCreate(['name' => 'coordinador_circuito', 'guard_name' => 'sanctum']);
        $roleCoordinadorRegional = Role::firstOrCreate(['name' => 'coordinador_regional', 'guard_name' => 'sanctum']);
        $roleAdmin               = Role::firstOrCreate(['name' => 'admin',                'guard_name' => 'sanctum']);

        // ---- Permisos por rol ----

        // Estudiante
        $roleEstudiante->syncPermissions([
            'proyectos.ver',
            'certificados.ver',
        ]);

        // Juez (leer proyecto asignado y calificar)
        $roleJuez->syncPermissions([
            'proyectos.ver',
            'proyectos.calificar',
            'jueces.ver',                    // <- necesario para /proyectos/{id}/rubrica si dejaras permission
            'calificaciones.ver',
            'calificaciones.crear',
            'calificaciones.consolidar',     // <- para consolidar
        ]);

        // Comité Institucional
        $roleComite->syncPermissions([
            'instituciones.ver','instituciones.crear','instituciones.editar',
            'proyectos.ver','proyectos.crear','proyectos.editar',
            'estudiantes.ver','estudiantes.crear','estudiantes.editar',
            'jueces.ver','jueces.crear','jueces.editar','jueces.eliminar','jueces.asignar',
            'ferias.ver',
            'calificaciones.ver',
            'reportes.ver',
            'certificados.ver','certificados.generar',
        ]);

        // Coordinador de Circuito
        $roleCoordinadorCircuito->syncPermissions([
            'instituciones.ver','instituciones.editar',
            'proyectos.ver','proyectos.editar',
            'estudiantes.ver',
            'jueces.ver','jueces.crear','jueces.editar','jueces.asignar',
            'ferias.ver','ferias.crear','ferias.editar','ferias.cerrar_etapa',
            'calificaciones.ver','calificaciones.consolidar',
            'reportes.ver','reportes.exportar',
            'certificados.ver','certificados.generar',
        ]);

        // Coordinador Regional
        $roleCoordinadorRegional->syncPermissions([
            'instituciones.ver','instituciones.crear','instituciones.editar',
            'proyectos.ver','proyectos.editar',
            'estudiantes.ver',
            'jueces.ver','jueces.crear','jueces.editar','jueces.eliminar','jueces.asignar',
            'ferias.ver','ferias.crear','ferias.editar','ferias.eliminar','ferias.cerrar_etapa',
            'calificaciones.ver','calificaciones.consolidar',
            'reportes.ver','reportes.exportar',
            'certificados.ver','certificados.generar',
        ]);

        // Admin: todos
        $roleAdmin->syncPermissions(Permission::all());

        // Limpia el caché de nuevo por seguridad
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
