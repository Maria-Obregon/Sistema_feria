<?php

namespace App\Http\Controllers;

use App\Mail\NewUserCredentialsMail;
use App\Models\Usuario;
use App\Models\Juez;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);

        $q = Usuario::query()
            ->with(['roles:id,name', 'institucion:id,nombre']);

        if ($s = $request->get('buscar')) {
            $q->where(function ($w) use ($s) {
                $w->where('nombre', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($rolName = $request->get('rol')) {
            $q->whereHas('roles', fn($r) => $r->where('name', $rolName));
        }

        if ($inst = $request->get('institucion_id')) {
            $q->where('institucion_id', $inst);
        }

        $q->orderBy('nombre');

        return response()->json($q->paginate($perPage));
    }

    /**
     * Alta básica con:
     * - password opcional -> si no viene, se genera
     * - asignación de 1 rol por name (role) o por id (rol_id)
     * - envío de correo con credenciales
     */
    /**
 * Alta básica con:
 * - password opcional -> si no viene, se genera
 * - asignación de 1 rol por name (role) o por id (rol_id)
 * - creación opcional de registro en jueces si el rol es "juez"
 * - envío de correo con credenciales
 */
public function store(Request $request)
{
    $data = $request->validate([
        'nombre'         => ['required', 'string', 'max:255'],
        'email'          => ['required', 'email', 'max:255', 'unique:usuarios,email'],
        'password'       => ['nullable', 'string', 'min:8'], // OPCIONAL
        'activo'         => ['boolean'],
        'institucion_id' => ['nullable', 'exists:instituciones,id'],
        'role'           => ['nullable', 'string'],
        'rol_id'         => ['nullable', 'integer', 'exists:roles,id'],

        // Estos son OPCIONALES, solo se usan si el rol es juez
        'cedula_juez'        => ['nullable', 'string', 'max:50'],
        'sexo_juez'          => ['nullable', 'string', 'max:10'],
        'telefono_juez'      => ['nullable', 'string', 'max:50'],
        'grado_academico_juez' => ['nullable', 'string', 'max:255'],
        'area_id_juez'       => ['nullable', 'integer', 'exists:areas,id'],
    ]);

    DB::beginTransaction();

    try {
        // 1) Crear usuario
        $plainPassword = $data['password'] ?? str()->password(10);

        $user = Usuario::create([
            'nombre'         => $data['nombre'],
            'email'          => $data['email'],
            'password'       => Hash::make($plainPassword),
            'activo'         => $data['activo'] ?? true,
            'institucion_id' => $data['institucion_id'] ?? null,
        ]);

        // 2) Asignar rol (por name o por id) y quedarnos con el nombre del rol
        $roleName = null;

        if (!empty($data['role'])) {
            $roleName = $data['role'];           // viene por nombre
            $user->assignRole($roleName);
        } elseif (!empty($data['rol_id'])) {
            $role = Role::find($data['rol_id']); // viene por id
            if ($role) {
                $roleName = $role->name;
                $user->assignRole($roleName);
            }
        }

        // 3) Si el rol del usuario es "juez", creamos también el registro en la tabla jueces
        if ($roleName === 'juez') {
            Juez::create([
                'nombre'          => $user->nombre,
                // Si no te mandan cédula, se genera una genérica pero única
                'cedula'          => $data['cedula_juez'] ?? ('SIN-CED-' . $user->id),
                'sexo'            => $data['sexo_juez'] ?? 'N/D',
                'telefono'        => $data['telefono_juez'] ?? null,
                'correo'          => $user->email,
                'grado_academico' => $data['grado_academico_juez'] ?? null,
                'area_id'         => $data['area_id_juez'] ?? null,
                'usuario_id'      => $user->id,
            ]);
        }

        // 4) Enviar correo con credenciales
        Mail::to($user->email)->send(new NewUserCredentialsMail($user, $plainPassword));

        DB::commit();

        return response()->json([
            'message' => 'Usuario creado y credenciales enviadas',
            'mensaje' => 'Usuario creado y credenciales enviadas',
            'usuario' => $user->load('roles', 'institucion'),
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Error al crear usuario',
            'mensaje' => 'Error al crear usuario',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
    public function show($id)
    {
        $u = Usuario::with(['roles:id,name', 'institucion:id,nombre'])->findOrFail($id);
        return response()->json($u);
    }

    public function update(Request $request, $id)
    {
        $user = Usuario::findOrFail($id);

        $data = $request->validate([
            'nombre'         => ['sometimes', 'string', 'max:255'],
            'email'          => ['sometimes', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($user->id)],
            'password'       => ['nullable', 'string', 'min:8'], // opcional
            'activo'         => ['boolean'],
            'institucion_id' => ['nullable', 'exists:instituciones,id'],
            'role'           => ['nullable', 'string'],
            'rol_id'         => ['nullable', 'integer', 'exists:roles,id'],
        ]);

        DB::beginTransaction();
        try {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            // Si vino role/rol_id por aquí, permite también sincronizar desde update
            if (!empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            } elseif (!empty($data['rol_id'])) {
                $role = Role::find($data['rol_id']);
                if ($role) {
                    $user->syncRoles([$role->name]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado',
                'mensaje' => 'Usuario actualizado',
                'usuario' => $user->fresh()->load('roles', 'institucion'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar usuario',
                'mensaje' => 'Error al actualizar usuario',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = Usuario::findOrFail($id);

        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'No puede eliminar su propio usuario', 'mensaje' => 'No puede eliminar su propio usuario'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado', 'mensaje' => 'Usuario eliminado']);
    }

    public function toggleStatus($id)
    {
        $user = Usuario::findOrFail($id);
        $user->activo = !$user->activo;
        $user->save();

        return response()->json([
            'message' => $user->activo ? 'Usuario activado' : 'Usuario desactivado',
            'mensaje' => $user->activo ? 'Usuario activado' : 'Usuario desactivado',
            'usuario' => $user,
        ]);
    }

    /**
     * Lista de roles disponibles (por guard sanctum)
     */
    public function rolesDisponibles()
    {
        return response()->json(Role::where('guard_name', 'sanctum')->pluck('name'));
    }

    /**
     * Actualiza (sincroniza) los roles del usuario.
     * Ruta esperada: POST /api/admin/usuarios/{usuario}/roles
     * Body: { "roles": ["admin"] }
     */
    public function actualizarRoles(Request $request, Usuario $usuario)
    {
        $defaultGuard = Config::get('auth.defaults.guard', 'sanctum');

        $request->validate([
            'roles'   => ['required', 'array', 'min:1'],
            'roles.*' => [
                Rule::exists('roles', 'name')
                    ->where(fn($q) => $q->where('guard_name', $defaultGuard)),
            ],
        ]);

        $usuario->syncRoles($request->input('roles'));

        return response()->json([
            'message' => 'Roles actualizados',
            'mensaje' => 'Roles actualizados',
            'usuario' => $usuario->fresh('roles'),
        ]);
    }

    /**
     * Reset de contraseña (opcionalmente con password dado; si no, se genera).
     * Ruta: PUT /api/admin/usuarios/{usuario}/password
     * Body opcional: { "password": "NuevaSegura123" }
     */
   public function resetPassword(Request $request, Usuario $usuario)
{
    // 1) Exigir contraseña explícita desde el front
    $data = $request->validate([
        'password' => ['required', 'string', 'min:8'],
    ]);

    // 2) Guardar la contraseña ingresada (hasheada en BD)
    $usuario->forceFill([
        'password' => Hash::make($data['password']),
    ])->save();

    // 3) (Opcional) enviar correo de aviso sin incluir el password en claro
    // try {
    //     Mail::to($usuario->email)->send(new PasswordChangedMail($usuario));
    // } catch (\Throwable $e) {
    //     Log::warning('Fallo enviando notificación: '.$e->getMessage());
    // }

    // 4) Respuesta limpia (no exponer contraseña)
    return response()->json([
        'message' => 'Contraseña actualizada',
        'mensaje' => 'Contraseña actualizada',
    ]);
}
}
