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

class EstudianteController extends Controller
{
    // GET /api/estudiantes?institucion_id=&buscar=
    public function index(Request $request)
    {
        $institucionId = $request->get('institucion_id') ?? optional($request->user())->institucion_id;

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
