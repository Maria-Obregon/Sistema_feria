<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        // Los middleware se manejan en las rutas
    }

    /**
     * Listar usuarios con filtros
     */
    public function index(Request $request)
    {
        $query = Usuario::with(['rol', 'institucion']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rol_id')) {
            $query->where('rol_id', $request->rol_id);
        }

        if ($request->filled('institucion_id')) {
            $query->where('institucion_id', $request->institucion_id);
        }

        // Multi-tenant: Solo usuarios de la misma regional/circuito
        /** @var \App\Models\Usuario $currentUser */
        $currentUser = $request->user();
        if ($currentUser && $currentUser->regional_id) {
            $query->where('regional_id', $currentUser->regional_id);
        }
        if ($currentUser && $currentUser->circuito_id) {
            $query->where('circuito_id', $currentUser->circuito_id);
        }

        $usuarios = $query->paginate(15);

        return response()->json([
            'usuarios' => $usuarios,
            'roles' => Rol::all(['id', 'nombre']),
            'instituciones' => Institucion::select('id', 'nombre')->get()
        ]);
    }

    /**
     * Crear nuevo usuario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'cedula' => 'required|string|max:20|unique:usuarios,cedula',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'rol_id' => 'required|exists:roles,id',
            'institucion_id' => 'nullable|exists:instituciones,id',
            'regional_id' => 'nullable|exists:regionales,id',
            'circuito_id' => 'nullable|exists:circuitos,id',
            'activo' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $validated['password'] = Hash::make($validated['password']);
            $validated['activo'] = $validated['activo'] ?? true;
            
            // Heredar regional/circuito del usuario actual si no se especifica
            /** @var \App\Models\Usuario $currentUser */
            $currentUser = $request->user();
            if ($currentUser && !isset($validated['regional_id']) && $currentUser->regional_id) {
                $validated['regional_id'] = $currentUser->regional_id;
            }
            if ($currentUser && !isset($validated['circuito_id']) && $currentUser->circuito_id) {
                $validated['circuito_id'] = $currentUser->circuito_id;
            }

            $usuario = Usuario::create($validated);
            
            // Asignar rol con Spatie
            $rol = Rol::find($validated['rol_id']);
            $usuario->syncRoles([$rol->nombre]);

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'usuario' => $usuario->load(['rol', 'institucion'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de usuario
     */
    public function show($id)
    {
        $usuario = Usuario::with(['rol', 'institucion', 'regional', 'circuito'])
            ->findOrFail($id);

        // Verificar acceso multi-tenant
        $this->checkMultiTenantAccess($usuario);

        return response()->json($usuario);
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        
        // Verificar acceso multi-tenant
        $this->checkMultiTenantAccess($usuario);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('usuarios')->ignore($usuario->id)],
            'cedula' => ['sometimes', 'string', 'max:20', Rule::unique('usuarios')->ignore($usuario->id)],
            'telefono' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'rol_id' => 'sometimes|exists:roles,id',
            'institucion_id' => 'nullable|exists:instituciones,id',
            'activo' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $usuario->update($validated);

            // Actualizar rol si cambió
            if (isset($validated['rol_id'])) {
                $rol = Rol::find($validated['rol_id']);
                $usuario->syncRoles([$rol->nombre]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'usuario' => $usuario->fresh()->load(['rol', 'institucion'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar usuario (soft delete)
     */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        
        // Verificar acceso multi-tenant
        $this->checkMultiTenantAccess($usuario);

        // No permitir auto-eliminación
        /** @var \App\Models\Usuario $currentUser */
        $currentUser = request()->user();
        if ($currentUser && $usuario->id === $currentUser->id) {
            return response()->json([
                'message' => 'No puede eliminar su propio usuario'
            ], 403);
        }

        $usuario->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    /**
     * Activar/Desactivar usuario
     */
    public function toggleStatus($id)
    {
        $usuario = Usuario::findOrFail($id);
        
        // Verificar acceso multi-tenant
        $this->checkMultiTenantAccess($usuario);

        $usuario->activo = !$usuario->activo;
        $usuario->save();

        return response()->json([
            'message' => $usuario->activo ? 'Usuario activado' : 'Usuario desactivado',
            'usuario' => $usuario
        ]);
    }

    /**
     * Verificar acceso multi-tenant
     */
    private function checkMultiTenantAccess($usuario)
    {
        /** @var \App\Models\Usuario $currentUser */
        $currentUser = request()->user();
        
        // Admins tienen acceso total
        if ($currentUser->hasRole('admin')) {
            return;
        }

        // Verificar misma regional/circuito
        if ($currentUser->regional_id && $usuario->regional_id !== $currentUser->regional_id) {
            abort(403, 'No tiene permisos para acceder a este usuario');
        }
        
        if ($currentUser->circuito_id && $usuario->circuito_id !== $currentUser->circuito_id) {
            abort(403, 'No tiene permisos para acceder a este usuario');
        }
    }
}
