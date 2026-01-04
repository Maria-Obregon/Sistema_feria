// resources/views/certificados/cert_colaborador.blade.php
@php
  $mapEtapa = ['institucional'=>'Institucional','circuital'=>'Circuital','regional'=>'Regional'];
  $etapa = $mapEtapa[$feria->tipo_feria ?? ''] ?? 'Institucional/Circuital/Regional';
  $anio  = $feria->anio ?? date('Y');

  $institucionLinea = trim(
    (optional($feria->institucion)->nombre ?? '') .
    (optional($feria->circuito)->nombre ? ' · '.optional($feria->circuito)->nombre : '') .
    (optional(optional($feria->institucion)->regional)->nombre ? ' · '.optional(optional($feria->institucion)->regional)->nombre : '')
  );

  $accent = '#6BB64A';
  $icon   = public_path('img/cert_invitado_icon.png');
@endphp

@extends('certificados._base_mep', [
  'title'  => 'Certificado de participación - Colaborador',
  'accent' => $accent,
  'icon'   => $icon,
  'firma1' => 'Firma 1', 'cargo1' => 'Cargo',
  'firma2' => 'Firma 2', 'cargo2' => 'Cargo',
])

@section('contenido')
  <div class="encabezado">El Programa Nacional de Ferias de<br> Ciencia y Tecnología</div>

  <div class="sub">Certifica la participación de:</div>

  <div class="nombre">{{ strtoupper($colaborador->nombre) }}</div>

  <div class="texto">
    Por su valiosa participación en el proceso de:
    <div class="bloque-linea"><span class="valor">Organización</span></div>
  </div>

  <div class="texto" style="margin-top:0.15in;">
    En la etapa: <span class="fuerte">{{ $etapa }}</span>
  </div>

  <div class="texto" style="margin-top:0.14in;">
    {{ $institucionLinea ?: 'Nombre de la institución, Circuito, Regional' }}
  </div>

  <div class="proceso">PROCESO FERIA DE CIENCIA Y TECNOLOGÍA {{ $anio }}</div>
@endsection
