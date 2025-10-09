<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserCredentialsMail;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);

        $q = Usuario::query()
            ->with(['roles:id,name','institucion:id,nombre']);

        if ($s = $request->get('buscar')) {
            $q->where(function ($w) use ($s) {
                $w->where('nombre','like',"%{$s}%")
                  ->orWhere('email','like',"%{$s}%");
            });
        }

        if ($rolName = $request->get('rol')) {
            $q->whereHas('roles', fn ($r) => $r->where('name', $rolName));
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
     * - asignación de rol por name (role) o por id (rol_id)
     * - envío de correo con credenciales
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'         => ['required','string','max:255'],
            'email'          => ['required','email','max:255','unique:usuarios,email'],
            'password'       => ['nullable','string','min:8'], // OPCIONAL
            'activo'         => ['boolean'],
            'institucion_id' => ['nullable','exists:instituciones,id'],
            'role'           => ['nullable','string'],
            'rol_id'         => ['nullable','integer','exists:roles,id'],
        ]);

        DB::beginTransaction();
        try {
            // genera si no viene
            $plainPassword = $data['password'] ?? str()->password(10);

            $user = Usuario::create([
                'nombre'         => $data['nombre'],
                'email'          => $data['email'],
                'password'       => Hash::make($plainPassword),
                'activo'         => $data['activo'] ?? true,
                'institucion_id' => $data['institucion_id'] ?? null,
            ]);

            // asignar 1 rol si vino
            if (!empty($data['role'])) {
                $user->assignRole($data['role']); // por name
            } elseif (!empty($data['rol_id'])) {
                $role = Role::find($data['rol_id']);
                if ($role) $user->assignRole($role->name);
            }

            // correo con credenciales
            Mail::to($user->email)->send(new NewUserCredentialsMail($user, $plainPassword));

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado y credenciales enviadas',
                'usuario' => $user->load('roles','institucion'),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear usuario',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $u = Usuario::with(['roles:id,name','institucion:id,nombre'])->findOrFail($id);
        return response()->json($u);
    }

    public function update(Request $request, $id)
    {
        $user = Usuario::findOrFail($id);

        $data = $request->validate([
            'nombre'         => ['sometimes','string','max:255'],
            'email'          => ['sometimes','email','max:255', Rule::unique('usuarios','email')->ignore($user->id)],
            'password'       => ['nullable','string','min:8'], // opcional
            'activo'         => ['boolean'],
            'institucion_id' => ['nullable','exists:instituciones,id'],
            'role'           => ['nullable','string'],
            'rol_id'         => ['nullable','integer','exists:roles,id'],
        ]);

        DB::beginTransaction();
        try {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            if (!empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            } elseif (!empty($data['rol_id'])) {
                $role = Role::find($data['rol_id']);
                if ($role) $user->syncRoles([$role->name]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado',
                'usuario' => $user->fresh()->load('roles','institucion'),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar usuario',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = Usuario::findOrFail($id);

        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'No puede eliminar su propio usuario'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado']);
    }

    public function toggleStatus($id)
    {
        $user = Usuario::findOrFail($id);
        $user->activo = !$user->activo;
        $user->save();

        return response()->json([
            'message' => $user->activo ? 'Usuario activado' : 'Usuario desactivado',
            'usuario' => $user,
        ]);
    }

    /** Opcional: mover aquí los roles disponibles (era de AdminController) */
    public function rolesDisponibles()
    {
        return response()->json(Role::where('guard_name','sanctum')->pluck('name'));
    }
public function actualizarRoles(Request $request, Usuario $usuario)
{
    $defaultGuard = Config::get('auth.defaults.guard', null);

    $request->validate([
        'roles'   => ['required','array','min:1'],
        'roles.*' => [
            Rule::exists('roles','name')
                ->when($defaultGuard, fn($q) => $q->where('guard_name', $defaultGuard))
        ],
    ]);

    $usuario->syncRoles($request->input('roles'));

    return response()->json([
        'message' => 'Roles actualizados',
        'usuario' => $usuario->fresh('roles'),
    ]);
}
    /** Opcional: resetear contraseña desde UserController (con correo) */
    public function resetPassword(Request $request, Usuario $usuario)
{
    $data = $request->validate([
        'password' => ['nullable','string','min:8'], // si no viene, se genera
    ]);

    $plain = $data['password'] ?? str()->password(10);

    $usuario->forceFill(['password' => Hash::make($plain)])->save();

    // Enviar correo (si falla, que no tumbe la petición)
    try {
        Mail::to($usuario->email)->send(new NewUserCredentialsMail($usuario, $plain, true));
    } catch (\Throwable $e) {
        Log::warning('Fallo enviando credenciales: '.$e->getMessage());
    }

    // Respuesta: solo expón el password en local/testing
    $resp = ['message' => 'Contraseña restablecida'];
    if (app()->environment(['local','testing'])) {
        $resp['password_plano']  = $plain;
        $resp['auto_generada']   = ! $request->filled('password');
    }

    return response()->json($resp);
}
}
