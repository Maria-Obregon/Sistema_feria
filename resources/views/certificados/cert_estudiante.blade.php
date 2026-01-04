// resources/views/certificados/cert_estudiante.blade.php
@php
  $mapEtapa = ['institucional'=>'Institucional','circuital'=>'Circuital','regional'=>'Regional'];
  $etapa = $mapEtapa[$feria->tipo_feria ?? ''] ?? ($feria->etapa_label ?? 'Institucional/Circuital/Regional');
  $anio  = $feria->anio ?? date('Y');

  $accent = '#F68E1D'; // naranja (plantilla estudiantado/tutor)
  $icon   = public_path('img/plantilla_estudiante.png');

  $tituloProyecto = $proyecto->titulo ?? 'Nombre del proyecto';
  $categoria = $proyecto->categoria->nombre ?? 'Nombre de la categoría del proyecto';
@endphp

@extends('certificados._base_mep', [
  'title'  => 'Certificado de participación - Estudiante',
  'accent' => $accent,
  'icon'   => $icon,
  'firma1' => 'Firma 1', 'cargo1' => 'Cargo',
  'firma2' => 'Firma 2', 'cargo2' => 'Cargo',
])

@section('contenido')
  <div class="encabezado">El Programa Nacional de Ferias de<br> Ciencia y Tecnología</div>

  <div class="sub">Certifica la participación de:</div>

  <div class="nombre">{{ strtoupper($estudiante->nombre_completo ?? $estudiante->nombre) }}</div>

  <div class="texto">
    Por su valiosa participación en calidad de <span class="fuerte">estudiante</span> con el proyecto:
  </div>

  <div class="texto" style="margin-top:0.12in; font-weight:800;">
    {{ $tituloProyecto }}
  </div>

  <div class="texto" style="margin-top:0.14in;">
    En la categoría: <span class="fuerte">{{ $categoria }}</span>
  </div>

  <div class="texto" style="margin-top:0.14in;">
    En la etapa: <span class="fuerte">{{ $etapa }}</span>
  </div>

  <div class="proceso">PROCESO FERIA DE CIENCIA Y TECNOLOGÍA {{ $anio }}</div>
@endsection
