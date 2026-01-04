// resources/views/certificados/cert_juez.blade.php
@php
  $mapEtapa = ['institucional'=>'Institucional','circuital'=>'Circuital','regional'=>'Regional'];
  $etapa = $mapEtapa[$feria->tipo_feria ?? ''] ?? 'Institucional/Circuital/Regional';
  $anio  = $feria->anio ?? date('Y');

  $institucionLinea = trim(
    (optional($feria->institucion)->nombre ?? '') .
    (optional($feria->circuito)->nombre ? ' · '.optional($feria->circuito)->nombre : '') .
    (optional(optional($feria->institucion)->regional)->nombre ? ' · '.optional(optional($feria->institucion)->regional)->nombre : '')
  );

  $accent = '#6BB64A'; // verde (plantilla asesoría/juzgamiento/organización)
  $icon   = public_path('img/cert_juez_icon.png');
@endphp

@extends('certificados._base_mep', [
  'title'  => 'Certificado de participación - Juez',
  'accent' => $accent,
  'icon'   => $icon,
  // si querés, aquí ponés nombres/cargos reales:
  'firma1' => 'Firma 1', 'cargo1' => 'Cargo',
  'firma2' => 'Firma 2', 'cargo2' => 'Cargo',
])

@section('contenido')
  <div class="encabezado">El Programa Nacional de Ferias de<br> Ciencia y Tecnología</div>

  <div class="sub">Certifica la participación de:</div>

  <div class="nombre">{{ strtoupper($juez->nombre_completo ?? $juez->nombre) }}</div>

  <div class="texto">
    Por su valiosa participación en el proceso de:
    <div class="bloque-linea"><span class="valor">Juzgamiento</span></div>
  </div>

  <div class="texto" style="margin-top:0.15in;">
    En la etapa: <span class="fuerte">{{ $etapa }}</span>
  </div>

  <div class="texto" style="margin-top:0.14in;">
    {{ $institucionLinea ?: 'Nombre de la institución, Circuito, Regional' }}
  </div>

  <div class="proceso">PROCESO FERIA DE CIENCIA Y TECNOLOGÍA {{ $anio }}</div>
@endsection
