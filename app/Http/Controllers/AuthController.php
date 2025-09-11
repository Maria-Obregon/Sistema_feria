<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|unique:usuarios,email',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'rol'      => 'required|string|in:estudiante,juez,comite_institucional,coordinador_circuito,coordinador_regional',
            'institucion_id' => 'nullable|exists:instituciones,id',
            'telefono' => 'nullable|string|max:20',
        ]);

        $user = Usuario::create([
            'nombre'   => $data['nombre'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'institucion_id' => $data['institucion_id'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'activo'   => true,
        ]);

        // Asignar rol usando Spatie
        $user->assignRole($data['rol']);

        // Crear token de acceso
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permisos' => $user->getAllPermissions()->pluck('name'),
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = Usuario::where('email', $data['email'])->first();
        
        if (!$user || !Hash::check($data['password'], $user->contrasena)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        if (!$user->activo) {
            return response()->json(['message' => 'Usuario inactivo'], 403);
        }

        // Verificar si el usuario requiere 2FA
        if ($user->two_factor_confirmed_at && ($user->hasRole('juez') || $user->hasRole('admin'))) {
            // Crear token temporal para verificación 2FA
            $tempToken = $user->createToken('2fa-token', ['2fa-pending'])->plainTextToken;
            
            return response()->json([
                'message' => 'Se requiere autenticación de dos factores',
                'requires_2fa' => true,
                'temp_token' => $tempToken,
                'two_fa_enabled' => true
            ], 200);
        }

        // Crear token de acceso
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permisos' => $user->getAllPermissions()->pluck('name'),
                'institucion' => $user->institucion,
            ]
        ], 200);
    }

    public function verify2FA(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        if (!$user->two_factor_secret || !$user->two_factor_confirmed_at) {
            return response()->json(['message' => '2FA no está configurado'], 400);
        }

        $google2fa = new Google2FA();
        $code = $data['code'];
        
        // Verificar código normal de 6 dígitos
        if (strlen($code) === 6) {
            $secret = decrypt($user->two_factor_secret);
            $valid = $google2fa->verifyKey($secret, $code);
            
            if ($valid) {
                // Eliminar token temporal y crear token permanente
                $request->user()->currentAccessToken()->delete();
                $token = $user->createToken('api-token')->plainTextToken;
                
                return response()->json([
                    'message' => 'Verificación exitosa',
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'nombre' => $user->nombre,
                        'email' => $user->email,
                        'roles' => $user->getRoleNames(),
                        'permisos' => $user->getAllPermissions()->pluck('name'),
                        'institucion' => $user->institucion,
                    ]
                ], 200);
            }
        }
        
        // Verificar código de recuperación de 10 caracteres
        if (strlen($code) === 10) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            
            if (in_array($code, $recoveryCodes)) {
                // Remover código usado
                $recoveryCodes = array_diff($recoveryCodes, [$code]);
                $user->two_factor_recovery_codes = encrypt(json_encode(array_values($recoveryCodes)));
                $user->save();
                
                // Eliminar token temporal y crear token permanente
                $request->user()->currentAccessToken()->delete();
                $token = $user->createToken('api-token')->plainTextToken;
                
                return response()->json([
                    'message' => 'Verificación exitosa con código de recuperación',
                    'token' => $token,
                    'recovery_used' => true,
                    'codes_remaining' => count($recoveryCodes),
                    'user' => [
                        'id' => $user->id,
                        'nombre' => $user->nombre,
                        'email' => $user->email,
                        'roles' => $user->getRoleNames(),
                        'permisos' => $user->getAllPermissions()->pluck('name'),
                        'institucion' => $user->institucion,
                    ]
                ], 200);
            }
        }

        return response()->json(['message' => 'Código inválido'], 422);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'roles' => $user->getRoleNames(),
                'permisos' => $user->getAllPermissions()->pluck('name'),
                'institucion' => $user->institucion,
                'circuito' => $user->circuito,
                'regional' => $user->regional,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }
}
