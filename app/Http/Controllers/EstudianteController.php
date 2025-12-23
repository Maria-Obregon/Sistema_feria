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
    public function index(Request $request)
    {
        $user = $request->user();
     
        $institucionId = $request->get('institucion_id');

        if (! $user->hasRole('admin')) {
             $institucionId = $user->institucion_id;
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

    public function store(Request $request)
    {
        if (! $request->user()->hasRole('admin')) {
            $request->merge(['institucion_id' => $request->user()->institucion_id]);
        }

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

        $data['institucion_id'] = $data['institucion_id'] ?? optional($request->user())->institucion_id;
        if (!$data['institucion_id']) {
            return response()->json(['message' => 'institucion_id es requerido'], 422);
        }

        $map = ['M'=>'M','Masculino'=>'M','F'=>'F','Femenino'=>'F','Otro'=>'Otro','OTRO'=>'Otro','otro'=>'Otro'];
        if (!isset($map[$data['genero']])) {
            return response()->json(['message' => 'Género inválido. Use M, F u Otro.'], 422);
        }
        $data['genero'] = $map[$data['genero']];

        $fecha = $data['fecha_nacimiento'];
        $carbon = null;
        foreach (['Y-m-d','d/m/Y'] as $fmt) {
            try { $carbon = Carbon::createFromFormat($fmt, $fecha); break; } catch (\Exception $e) {}
        }
        if (!$carbon) { try { $carbon = Carbon::parse($fecha); } catch (\Throwable $e) {} }
        if (!$carbon) { return response()->json(['message'=>'Fecha inválida (use YYYY-MM-DD o DD/MM/YYYY).'], 422); }
        $data['fecha_nacimiento'] = $carbon->format('Y-m-d');

        $inst = Institucion::findOrFail($data['institucion_id']);
        if (!$inst->puedeAgregarEstudiante()) {
            return response()->json(['message'=>'Límite de estudiantes alcanzado para la institución'], 422);
        }

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
                'password'            => $plain,   
                'activo'              => true,
                'institucion_id'      => $data['institucion_id'],
                'identificacion'      => $data['cedula'], 
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

    public function update(Request $request, Estudiante $estudiante)
    {
        $data = $request->validate([
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

        $map = ['M'=>'M','Masculino'=>'M','F'=>'F','Femenino'=>'F','Otro'=>'Otro'];
        if (isset($map[$data['genero']])) {
            $data['genero'] = $map[$data['genero']];
        }

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
            $estudiante->update($data);

            if ($estudiante->usuario) {
                $userData = [
                    'nombre' => $data['nombre'].' '.$data['apellidos'],
                    'identificacion' => $data['cedula']
                ];
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

    public function destroy(Estudiante $estudiante)
    {
        DB::beginTransaction();
        try {
            if ($estudiante->usuario) {
                $estudiante->usuario->delete();
            }
            $estudiante->delete();

            DB::commit();
            return response()->json(['message' => 'Estudiante eliminado correctamente']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message'=>'Error al eliminar', 'error'=>$e->getMessage()], 500);
        }
    }
    public function credencial(Request $request, Estudiante $estudiante)
    {
        $password = $request->query('password');

        $path = public_path('img/Logo.webp'); 
        $logoBase64 = null;
        
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $pdf = Pdf::loadView('pdf.credencial', compact('estudiante', 'password', 'logoBase64'));
        
        $nombreLimpio = str_replace(' ', '_', $estudiante->nombre . '_' . $estudiante->apellidos);
        $nombreLimpio = preg_replace('/[^A-Za-z0-9_]/', '', $nombreLimpio);
        
        return $pdf->download("Credenciales_{$nombreLimpio}.pdf");
    }

    public function vincularProyecto(Request $request, Estudiante $estudiante)
    {
        $request->validate([
            'proyecto_id' => ['required', Rule::exists('proyectos','id')],
        ]);
        $proyecto = Proyecto::where('id', $request->proyecto_id)
            ->where('institucion_id', $estudiante->institucion_id)
            ->firstOrFail();

        $estudiante->proyectos()->syncWithoutDetaching([$proyecto->id]);

        return response()->json([
            'message'   => 'Estudiante ligado al proyecto',
            'estudiante'=> $estudiante->fresh('proyectos:id,titulo'),
        ], 200);
    }

    public function desvincularProyecto(Estudiante $estudiante, Proyecto $proyecto)
    {
        abort_unless($proyecto->institucion_id == $estudiante->institucion_id, 422, 'Proyecto de otra institución');

        $estudiante->proyectos()->detach($proyecto->id);

        return response()->json([
            'message'   => 'Estudiante desvinculado del proyecto',
            'estudiante'=> $estudiante->fresh('proyectos:id,titulo'),
        ], 200);
    }
}
