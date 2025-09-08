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
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos por módulo
        $permissions = [
            // Usuarios
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            
            // Instituciones
            'instituciones.ver',
            'instituciones.crear',
            'instituciones.editar',
            'instituciones.eliminar',
            
            // Proyectos
            'proyectos.ver',
            'proyectos.crear',
            'proyectos.editar',
            'proyectos.eliminar',
            'proyectos.calificar',
            
            // Estudiantes
            'estudiantes.ver',
            'estudiantes.crear',
            'estudiantes.editar',
            'estudiantes.eliminar',
            
            // Jueces
            'jueces.ver',
            'jueces.crear',
            'jueces.editar',
            'jueces.eliminar',
            'jueces.asignar',
            
            // Ferias
            'ferias.ver',
            'ferias.crear',
            'ferias.editar',
            'ferias.eliminar',
            'ferias.cerrar_etapa',
            
            // Calificaciones
            'calificaciones.ver',
            'calificaciones.crear',
            'calificaciones.consolidar',
            
            // Reportes
            'reportes.ver',
            'reportes.exportar',
            
            // Certificados
            'certificados.ver',
            'certificados.generar',
            
            // Administración
            'admin.configuracion',
            'admin.respaldos',
            'admin.logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'sanctum'
            ]);
        }

        // Crear roles
        $roleEstudiante = Role::create(['name' => 'estudiante', 'guard_name' => 'sanctum']);
        $roleJuez = Role::create(['name' => 'juez', 'guard_name' => 'sanctum']);
        $roleComite = Role::create(['name' => 'comite_institucional', 'guard_name' => 'sanctum']);
        $roleCoordinadorCircuito = Role::create(['name' => 'coordinador_circuito', 'guard_name' => 'sanctum']);
        $roleCoordinadorRegional = Role::create(['name' => 'coordinador_regional', 'guard_name' => 'sanctum']);
        $roleAdmin = Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);

        // Asignar permisos a roles
        
        // Estudiante - Solo lectura de su información
        $roleEstudiante->givePermissionTo([
            'proyectos.ver',
            'certificados.ver',
        ]);

        // Juez - Calificar proyectos asignados
        $roleJuez->givePermissionTo([
            'proyectos.ver',
            'proyectos.calificar',
            'calificaciones.ver',
            'calificaciones.crear',
        ]);

        // Comité Institucional
        $roleComite->givePermissionTo([
            'instituciones.ver',
            'proyectos.ver',
            'proyectos.crear',
            'proyectos.editar',
            'estudiantes.ver',
            'estudiantes.crear',
            'estudiantes.editar',
            'jueces.ver',
            'ferias.ver',
            'calificaciones.ver',
            'reportes.ver',
            'certificados.ver',
            'certificados.generar',
        ]);

        // Coordinador de Circuito
        $roleCoordinadorCircuito->givePermissionTo([
            'instituciones.ver',
            'instituciones.editar',
            'proyectos.ver',
            'proyectos.editar',
            'estudiantes.ver',
            'jueces.ver',
            'jueces.crear',
            'jueces.editar',
            'jueces.asignar',
            'ferias.ver',
            'ferias.crear',
            'ferias.editar',
            'ferias.cerrar_etapa',
            'calificaciones.ver',
            'calificaciones.consolidar',
            'reportes.ver',
            'reportes.exportar',
            'certificados.ver',
            'certificados.generar',
        ]);

        // Coordinador Regional
        $roleCoordinadorRegional->givePermissionTo([
            'instituciones.ver',
            'instituciones.crear',
            'instituciones.editar',
            'proyectos.ver',
            'proyectos.editar',
            'estudiantes.ver',
            'jueces.ver',
            'jueces.crear',
            'jueces.editar',
            'jueces.eliminar',
            'jueces.asignar',
            'ferias.ver',
            'ferias.crear',
            'ferias.editar',
            'ferias.eliminar',
            'ferias.cerrar_etapa',
            'calificaciones.ver',
            'calificaciones.consolidar',
            'reportes.ver',
            'reportes.exportar',
            'certificados.ver',
            'certificados.generar',
        ]);

        // Admin - Todos los permisos
        $roleAdmin->givePermissionTo(Permission::all());
    }
}
