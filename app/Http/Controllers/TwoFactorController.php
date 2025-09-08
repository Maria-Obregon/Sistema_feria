<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    private $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Habilitar 2FA para el usuario
     */
    public function enable(Request $request)
    {
        $user = $request->user();
        
        // Solo jueces pueden habilitar 2FA obligatoriamente
        if (!$user->hasRole('juez') && !$user->hasRole('admin')) {
            return response()->json([
                'message' => '2FA solo está disponible para jueces y administradores'
            ], 403);
        }

        // Generar secret key
        $secretKey = $this->google2fa->generateSecretKey();
        
        // Guardar temporalmente el secret (no confirmado aún)
        $user->two_factor_secret = encrypt($secretKey);
        $user->save();

        // Generar QR code
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );

        // Generar códigos de recuperación
        $recoveryCodes = $this->generateRecoveryCodes();

        return response()->json([
            'secret' => $secretKey,
            'qr_code' => $this->generateQrCode($qrCodeUrl),
            'recovery_codes' => $recoveryCodes,
            'message' => 'Por favor escanea el código QR con tu app de autenticación'
        ]);
    }

    /**
     * Confirmar y activar 2FA
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'recovery_codes' => 'required|array'
        ]);

        $user = $request->user();
        
        if (!$user->two_factor_secret) {
            return response()->json([
                'message' => 'Primero debes generar un código 2FA'
            ], 400);
        }

        $secret = decrypt($user->two_factor_secret);
        
        // Verificar el código
        $valid = $this->google2fa->verifyKey($secret, $request->code);
        
        if (!$valid) {
            return response()->json([
                'message' => 'El código es inválido'
            ], 422);
        }

        // Activar 2FA
        $user->two_factor_confirmed_at = now();
        $user->two_factor_recovery_codes = encrypt(json_encode($request->recovery_codes));
        $user->save();

        return response()->json([
            'message' => '2FA ha sido activado exitosamente'
        ]);
    }

    /**
     * Desactivar 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();
        
        // Verificar contraseña
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Contraseña incorrecta'
            ], 422);
        }

        // Desactivar 2FA
        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return response()->json([
            'message' => '2FA ha sido desactivado'
        ]);
    }

    /**
     * Verificar código 2FA durante login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $user = $request->user();
        
        if (!$user->two_factor_secret || !$user->two_factor_confirmed_at) {
            return response()->json([
                'message' => '2FA no está configurado'
            ], 400);
        }

        $code = $request->code;
        
        // Primero intentar con código normal
        if (strlen($code) === 6) {
            $secret = decrypt($user->two_factor_secret);
            $valid = $this->google2fa->verifyKey($secret, $code);
            
            if ($valid) {
                // Marcar sesión como verificada con 2FA
                session(['2fa_verified' => true]);
                
                return response()->json([
                    'message' => 'Verificación exitosa',
                    'verified' => true
                ]);
            }
        }
        
        // Si no es válido, intentar con código de recuperación
        if (strlen($code) === 10) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            
            if (in_array($code, $recoveryCodes)) {
                // Remover código usado
                $recoveryCodes = array_diff($recoveryCodes, [$code]);
                $user->two_factor_recovery_codes = encrypt(json_encode(array_values($recoveryCodes)));
                $user->save();
                
                // Marcar sesión como verificada
                session(['2fa_verified' => true]);
                
                return response()->json([
                    'message' => 'Verificación exitosa con código de recuperación',
                    'verified' => true,
                    'recovery_used' => true,
                    'codes_remaining' => count($recoveryCodes)
                ]);
            }
        }

        return response()->json([
            'message' => 'Código inválido'
        ], 422);
    }

    /**
     * Regenerar códigos de recuperación
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();
        
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Contraseña incorrecta'
            ], 422);
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        $user->two_factor_recovery_codes = encrypt(json_encode($recoveryCodes));
        $user->save();

        return response()->json([
            'recovery_codes' => $recoveryCodes,
            'message' => 'Nuevos códigos de recuperación generados'
        ]);
    }

    /**
     * Generar códigos de recuperación
     */
    private function generateRecoveryCodes($count = 8)
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::random(10);
        }
        return $codes;
    }

    /**
     * Generar imagen QR en base64
     */
    private function generateQrCode($url)
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($url);
        
        return 'data:image/png;base64,' . base64_encode($qrCode);
    }

    /**
     * Verificar si el usuario necesita 2FA
     */
    public function status(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'enabled' => !is_null($user->two_factor_confirmed_at),
            'required' => $user->hasRole('juez'),
            'verified' => session('2fa_verified', false)
        ]);
    }
}
