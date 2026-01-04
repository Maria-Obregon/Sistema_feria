{{-- resources/views/certificados/cert_invitado.blade.php --}}
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
  'title'  => 'Certificado - Invitado',
  'accent' => $accent,
  'icon'   => $icon,
  'firma1' => 'Firma 1', 'cargo1' => 'Cargo',
  'firma2' => 'Firma 2', 'cargo2' => 'Cargo',
])

@section('contenido')
  <div class="titulo">El Programa Nacional de Ferias de<br> Ciencia y Tecnología</div>
  <div class="sub">Certifica la participación de:</div>

  <div class="nombre">{{ strtoupper($invitado->nombre) }}</div>

  <div class="p">
    Por su valiosa participación en calidad de invitado(a) especial<br>
    en la Feria de Ciencia y Tecnología.
  </div>

  <div class="etapa">En la etapa: <b>{{ $etapa }}</b></div>

  <div class="institucion">
    {{ $institucionLinea ?: 'Nombre de la institución, Circuito, Regional' }}
  </div>

  <div class="proceso">PROCESO FERIA DE CIENCIA Y TECNOLOGÍA {{ $anio }}</div>
@endsection
