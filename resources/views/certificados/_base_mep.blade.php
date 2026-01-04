{{-- resources/views/certificados/_base_mep.blade.php --}}
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>{{ $title ?? 'Certificado' }}</title>

  <style>
    @page { size: letter landscape; margin: 0; }
    body { margin:0; padding:0; font-family: DejaVu Sans, Arial, sans-serif; color:#111; }

    .page{
      width: 11in;
      height: 8.5in;
      position: relative;
      background: #fff;
      overflow: hidden;
    }

    /* SOLO marco gris (sin cuadro interno) */
    .frame{
      position:absolute;
      left: 0.45in;
      top: 0.45in;
      right: 0.45in;
      bottom: 0.45in;
      border: 6px solid #B7B7B7;
      box-sizing: border-box;
      background: #fff;
    }

    /* Contenedor interno para posicionar todo */
    .wrap{
      position:absolute;
      left: 0.55in;
      top: 0.55in;
      right: 0.55in;
      bottom: 0.55in;
    }

    /* Icono */
    .icono{
      position:absolute;
      left: 0.10in;
      top: 0.00in;
    }
    .icono img{
      height: 1.25in;
      width: auto;
      display:block;
    }

    /* Texto centrado: SIN bottom fijo (para que no “corte” ni reserve de más) */
    .content{
      position:absolute;
      left: 0.70in;
      right: 0.70in;
      top: 0.25in;
      text-align:center;
    }

    .titulo{ font-size:22pt; font-weight:800; line-height:1.15; margin:0; }
    .sub{ margin-top:14px; font-size:12pt; font-weight:700; }

    .nombre{
      margin-top:18px;
      font-size:30pt;
      font-weight:800;
      color: var(--accent);
      font-family: DejaVu Serif, "Times New Roman", serif;
      font-style: italic;
      letter-spacing: 0.4px;
      text-transform: uppercase;
    }

    .p{ margin-top:16px; font-size:13pt; line-height:1.4; }

    .etapa{
      margin-top:18px;
      font-size:13pt;
      font-weight:700;
      font-family: DejaVu Serif, "Times New Roman", serif;
      font-style: italic;
    }

    .institucion{ margin-top:18px; font-size:12.5pt; font-weight:500; }

    .proceso{
      margin-top:18px;
      font-size:12pt;
      font-weight:800;
      color: var(--accent);
      font-family: DejaVu Serif, "Times New Roman", serif;
      letter-spacing: 0.5px;
    }

    /* ✅ Firmas MÁS ARRIBA (más cerca del texto) */
    .firmas{
      position:absolute;
      left: 0.85in;
      right: 0.85in;
      bottom: 2.25in; /* sube firmas */
    }
    .firmas table{ width:100%; border-collapse: collapse; table-layout: fixed; }
    .firmas td{
      width:50%;
      text-align:center;
      vertical-align:top;
      padding: 0 18px;
      font-size: 11.5pt;
    }
    .linea{
      border-top: 4px solid var(--accent);
      margin: 0 auto 10px auto;
      width: 62%;
      height: 1px;
    }
    .cargo{ font-weight:800; margin-top:4px; font-size: 11pt; }

    /* ✅ Logos MÁS ARRIBA y DISTRIBUIDOS en toda la parte de abajo */
    .logos{
      position:absolute;
      left: 0.40in;
      right: 0.40in;
      bottom: 1.45in; /* sube logos (quedan debajo de firmas) */
    }
    .logos table{
      width:100%;
      border-collapse: collapse;
      table-layout: fixed;
    }
    .logos td{
      width:14.285%;
      text-align:center;
      vertical-align:middle;
    }
    .logos img{
      height: 42px;  /* más grandes */
      width: auto;
      display:inline-block;
    }
  </style>
</head>

<body>
  @php
    $IMG = str_replace('\\', '/', public_path('img'));
  @endphp

  <div class="page" style="--accent: {{ $accent ?? '#6BB64A' }}">

    <div class="frame">
      <div class="wrap">

        @if(!empty($icon))
          <div class="icono">
            <img src="{{ $icon }}" alt="icono">
          </div>
        @endif

        <div class="content">
          @yield('contenido')
        </div>

        <div class="firmas">
          <table>
            <tr>
              <td>
                <div class="linea"></div>
                <div>{{ $firma1 ?? 'Firma 1' }}</div>
                <div class="cargo">{{ $cargo1 ?? 'Cargo' }}</div>
              </td>
              <td>
                <div class="linea"></div>
                <div>{{ $firma2 ?? 'Firma 2' }}</div>
                <div class="cargo">{{ $cargo2 ?? 'Cargo' }}</div>
              </td>
            </tr>
          </table>
        </div>

        <div class="logos">
          <table>
            <tr>
              <td><img src="{{ $IMG }}/micitt.png" alt="MICITT"></td>
              <td><img src="{{ $IMG }}/mep.png" alt="MEP"></td>
              <td><img src="{{ $IMG }}/logo_ucr.png" alt="UCR"></td>
              <td><img src="{{ $IMG }}/logo_tec.png" alt="TEC"></td>
              <td><img src="{{ $IMG }}/logo_una.png" alt="UNA"></td>
              <td><img src="{{ $IMG }}/logo_uned.png" alt="UNED"></td>
              <td><img src="{{ $IMG }}/logo_utn.png" alt="UTN"></td>
            </tr>
          </table>
        </div>

      </div>
    </div>

  </div>
</body>
</html>
