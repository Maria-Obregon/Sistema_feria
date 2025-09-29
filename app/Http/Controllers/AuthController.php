<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre'         => 'required|string|max:255',
            'email'          => 'required|email|unique:usuarios,email',
            'password'       => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'rol'            => 'required|string|in:estudiante,juez,comite_institucional,coordinador_circuito,coordinador_regional,admin',
            'institucion_id' => 'nullable|exists:instituciones,id',
            'telefono'       => 'nullable|string|max:20',
        ]);

        $user = Usuario::create([
            'nombre'         => $data['nombre'],
            'email'          => $data['email'],
            'password'       => $data['password'], // <-- columna 'password', cast hashed
            'institucion_id' => $data['institucion_id'] ?? null,
            'telefono'       => $data['telefono'] ?? null,
            'activo'         => true,
        ]);

        // Asignar rol con Spatie
        $user->assignRole($data['rol']);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'token'   => $token,
            'user'    => [
                'id'     => $user->id,
                'nombre' => $user->nombre,
                'email'  => $user->email,
                'roles'  => $user->getRoleNames(), // <-- array de roles
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = Usuario::where('email', $data['email'])->first();

        // Solo 'password' (no mezclar con password_hash)
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        if (!$user->activo) {
            return response()->json(['message' => 'Usuario inactivo'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'          => $user->id,
                'nombre'      => $user->nombre,
                'email'       => $user->email,
                'roles'       => $user->getRoleNames(), // <-- aquí
                'institucion' => $user->institucion,
            ],
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id'          => $user->id,
                'nombre'      => $user->nombre,
                'email'       => $user->email,
                'telefono'    => $user->telefono,
                'roles'       => $user->getRoleNames(), // <-- aquí también
                'institucion' => $user->institucion,
                'circuito'    => $user->circuito,
                'regional'    => $user->regional,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }
}
