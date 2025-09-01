<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email|unique:usuario,email',
            'password' => 'required|string|min:8|confirmed',
            'rol'      => 'required|string|exists:rol,nombre',
        ]);

        $rolId = Rol::where('nombre',$data['rol'])->value('id');

        $user = Usuario::create([
            'email'   => $data['email'],
            'password'=> $data['password'], // se hashea por el cast
            'rol_id'  => $rolId,
            'activo'  => true,
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['message'=>'Registrado','token'=>$token,'rol'=>$user->rol->nombre], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $user = Usuario::where('email',$data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password) || !$user->activo) {
            return response()->json(['message'=>'Credenciales inválidas'], 401);
        }

        $token = $user->createToken('api')->plainTextToken;
        return response()->json(['token'=>$token,'rol'=>$user->rol->nombre], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message'=>'Sesión cerrada']);
    }
}
