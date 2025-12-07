<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Proyecto;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class EstudianteController extends Controller
{
    // GET /api/estudiantes?institucion_id=&buscar=
    // GET /api/estudiantes
    public function index(Request $request)
    {
        $user = $request->user();
        
        // 1. Iniciamos con el filtro que venga del frontend (el select de arriba)
        $institucionId = $request->get('institucion_id');

        
        if (! $user->hasAnyRole(['admin', 'comite_institucional'])) {
             // Si es un usuario normal (profe/estudiante), solo ve lo suyo
             $institucionId = $institucionId ?? $user->institucion_id;
        }

        $q = Estudiante::with(['institucion:id,nombre', 'proyectos:id,titulo'])
            ->when($institucionId, fn($qq) => $qq->where('institucion_id', $institucionId))
            ->when($request->filled('buscar'), function ($qq) use ($request) {
                $b = $request->buscar;
                $qq->where(function ($w) use ($b) {
                    $w->where('cedula', 'like', "%{$b}%")
                      ->orWhere('nombre', 'like', "%{$b}%")
                      ->orWhere('apellidos', 'like', "%{$b}%");
                });
            })
            ->orderBy('apellidos');

        return $q->paginate($request->integer('per_page', 10));
    }

    // POST /api/estudiantes  (solo creación; SIN ligarlo)
    public function store(Request $request)
    {
        $data = $request->validate([
            'cedula'           => ['required','string','max:25','unique:estudiantes,cedula'],
            'nombre'           => ['required','string','max:100'],
            'apellidos'        => ['required','string','max:150'],
            'fecha_nacimiento' => ['required','string'],
            'genero'           => ['required','string'],
            'telefono'         => ['nullable','string','max:20'],
            'email'            => ['nullable','email','max:120'],
            'nivel'            => ['required','string','max:50'],
            'seccion'          => ['nullable','string','max:10'],
            'institucion_id'   => ['nullable', Rule::exists('instituciones','id')],
        ]);

        // Institución
        $data['institucion_id'] = $data['institucion_id'] ?? optional($request->user())->institucion_id;
        if (!$data['institucion_id']) {
            return response()->json(['message' => 'institucion_id es requerido'], 422);
        }

        // Normalizar género
        $map = ['M'=>'M','Masculino'=>'M','F'=>'F','Femenino'=>'F','Otro'=>'Otro','OTRO'=>'Otro','otro'=>'Otro'];
        if (!isset($map[$data['genero']])) {
            return response()->json(['message' => 'Género inválido. Use M, F u Otro.'], 422);
        }
        $data['genero'] = $map[$data['genero']];

        // Normalizar fecha (YYYY-MM-DD o DD/MM/YYYY)
        $fecha = $data['fecha_nacimiento'];
        $carbon = null;
        foreach (['Y-m-d','d/m/Y'] as $fmt) {
            try { $carbon = Carbon::createFromFormat($fmt, $fecha); break; } catch (\Exception $e) {}
        }
        if (!$carbon) { try { $carbon = Carbon::parse($fecha); } catch (\Throwable $e) {} }
        if (!$carbon) { return response()->json(['message'=>'Fecha inválida (use YYYY-MM-DD o DD/MM/YYYY).'], 422); }
        $data['fecha_nacimiento'] = $carbon->format('Y-m-d');

        // Límite
        $inst = Institucion::findOrFail($data['institucion_id']);
        if (!$inst->puedeAgregarEstudiante()) {
            return response()->json(['message'=>'Límite de estudiantes alcanzado para la institución'], 422);
        }

        // Preparar email único
        $email = $data['email'] ?: ($data['cedula'].'@alumnos.local');
        if (Usuario::where('email',$email)->exists()) {
            $email = $data['cedula'].'+'.Str::lower(Str::random(4)).'@alumnos.local';
        }

        $plain = Str::upper(Str::random(10));

        DB::beginTransaction();
        try {
            $user = Usuario::create([
                'nombre'              => $data['nombre'].' '.$data['apellidos'],
                'email'               => $email,
                'password'            => $plain,       // cast hashed en el modelo
                'activo'              => true,
                'institucion_id'      => $data['institucion_id'],
                'identificacion'      => $data['cedula'], // username = cédula
                'tipo_identificacion' => 'NACIONAL',
            ]);
            $user->assignRole('estudiante');

            $est = Estudiante::create($data + [
                'usuario_id' => $user->id,
                'activo'     => true,
            ]);

            DB::commit();

            return response()->json([
                'message'        => 'Estudiante creado correctamente',
                'estudiante'     => $est,
                'usuario'        => ['id' => $user->id, 'username' => $data['cedula']],
                'plain_password' => $plain,
                'credencial_url' => url("/api/estudiantes/{$est->id}/credencial"),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message'=>'Error al crear estudiante','error'=>$e->getMessage()], 500);
        }
    }

    // PUT /api/estudiantes/{estudiante}
    public function update(Request $request, Estudiante $estudiante)
    {
        $data = $request->validate([
            // Ignoramos el ID actual para que no diga "la cédula ya existe" si es la misma
            'cedula'         => ['required','string','max:25', Rule::unique('estudiantes','cedula')->ignore($estudiante->id)],
            'nombre'         => ['required','string','max:100'],
            'apellidos'      => ['required','string','max:150'],
            'institucion_id' => ['required', Rule::exists('instituciones','id')],
            'fecha_nacimiento' => ['required','string'],
            'genero'         => ['required','string'],
            'telefono'       => ['nullable','string','max:20'],
            'email'          => ['nullable','email','max:120'],
            'nivel'          => ['required','string','max:50'],
            'seccion'        => ['nullable','string','max:10'],
        ]);

        // 1. Normalizar género
        $map = ['M'=>'M','Masculino'=>'M','F'=>'F','Femenino'=>'F','Otro'=>'Otro'];
        if (isset($map[$data['genero']])) {
            $data['genero'] = $map[$data['genero']];
        }

        // 2. Normalizar fecha
        $fecha = $data['fecha_nacimiento'];
        $carbon = null;
        foreach (['Y-m-d','d/m/Y'] as $fmt) {
            try { $carbon = Carbon::createFromFormat($fmt, $fecha); break; } catch (\Exception $e) {}
        }
        if (!$carbon) { try { $carbon = Carbon::parse($fecha); } catch (\Throwable $e) {} }
        
        if ($carbon) {
            $data['fecha_nacimiento'] = $carbon->format('Y-m-d');
        }

        DB::beginTransaction();
        try {
            // Actualizar Estudiante
            $estudiante->update($data);

            // Actualizar Usuario asociado (importante para login y nombre)
            if ($estudiante->usuario) {
                $userData = [
                    'nombre' => $data['nombre'].' '.$data['apellidos'],
                    'identificacion' => $data['cedula']
                ];
                // Si cambió el email, actualizamos el usuario también
                if (!empty($data['email'])) {
                    $userData['email'] = $data['email'];
                }
                $estudiante->usuario->update($userData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Estudiante actualizado correctamente',
                'estudiante' => $estudiante
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message'=>'Error al actualizar', 'error'=>$e->getMessage()], 500);
        }
    }

    // DELETE /api/estudiantes/{estudiante}
    public function destroy(Estudiante $estudiante)
    {
        DB::beginTransaction();
        try {
            // Borrar usuario asociado primero para limpiar la BD
            if ($estudiante->usuario) {
                $estudiante->usuario->delete();
            }
            
            // Borrar estudiante
            $estudiante->delete();

            DB::commit();
            return response()->json(['message' => 'Estudiante eliminado correctamente']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message'=>'Error al eliminar', 'error'=>$e->getMessage()], 500);
        }
    }

    // GET /api/estudiantes/{estudiante}/credencial
    public function credencial(Request $request, Estudiante $estudiante)
    {
        $password = $request->query('password');

        // --- LÓGICA PARA LA IMAGEN BASE64 (A prueba de errores) ---
        $path = public_path('img/Logo.webp'); // <--- Verifica que esta ruta sea real
        $logoBase64 = null;
        
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        // -----------------------------------------------------------

        $pdf = Pdf::loadView('pdf.credencial', compact('estudiante', 'password', 'logoBase64'));
        
        $nombreLimpio = str_replace(' ', '_', $estudiante->nombre . '_' . $estudiante->apellidos);
        // Limpiar caracteres especiales del nombre de archivo
        $nombreLimpio = preg_replace('/[^A-Za-z0-9_]/', '', $nombreLimpio);
        
        return $pdf->download("Credenciales_{$nombreLimpio}.pdf");
    }

    // POST /api/estudiantes/{estudiante}/vincular-proyecto
    public function vincularProyecto(Request $request, Estudiante $estudiante)
    {
        $request->validate([
            'proyecto_id' => ['required', Rule::exists('proyectos','id')],
        ]);

        // Coherencia: proyecto de la misma institución
        $proyecto = Proyecto::where('id', $request->proyecto_id)
            ->where('institucion_id', $estudiante->institucion_id)
            ->firstOrFail();

        $estudiante->proyectos()->syncWithoutDetaching([$proyecto->id]);

        return response()->json([
            'message'   => 'Estudiante ligado al proyecto',
            'estudiante'=> $estudiante->fresh('proyectos:id,titulo'),
        ], 200);
    }

    // DELETE /api/estudiantes/{estudiante}/desvincular-proyecto/{proyecto}
    public function desvincularProyecto(Estudiante $estudiante, Proyecto $proyecto)
    {
        // misma institución
        abort_unless($proyecto->institucion_id == $estudiante->institucion_id, 422, 'Proyecto de otra institución');

        $estudiante->proyectos()->detach($proyecto->id);

        return response()->json([
            'message'   => 'Estudiante desvinculado del proyecto',
            'estudiante'=> $estudiante->fresh('proyectos:id,titulo'),
        ], 200);
    }
}
