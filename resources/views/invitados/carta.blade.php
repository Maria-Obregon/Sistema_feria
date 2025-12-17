{{-- resources/views/invitados/carta.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta de invitación</title>
    <style>
        @page {
            margin: 2.5cm 2.5cm 2cm 2.5cm; /* márgenes del PDF */
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            color: #000044;
            line-height: 1.4;
        }

        /* Encabezado con logos */
        .logos {
            width: 100%;
            margin-bottom: 10px;
        }

        .logos td {
            vertical-align: middle;
        }

        .logos td.izq {
            text-align: left;
        }

        .logos td.centro {
            text-align: center;
        }

        .logos td.der {
            text-align: right;
        }

        /* Títulos centrales (Ministerio, Zona, Circuito...) */
        .encabezado-texto {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 20px;
            font-family: "Monotype Corsiva", "Brush Script MT", "Lucida Handwriting", cursive;
            color: #000066;
        }

        .encabezado-texto div {
            font-size: 16pt;
        }

        /* Cuerpo de la carta */
        .cuerpo {
            margin-top: 25px;
        }

        .derecha {
            text-align: right;
        }

        .subrayado {
            text-decoration: underline;
        }

        .parrafo {
            margin-top: 10px;
            text-align: justify;
        }

        /* Firma */
        .firma {
            margin-top: 80px;
            text-align: center;
        }

        .firma-linea {
            margin-bottom: 3px;
        }

        /* Pie de página con logos (como en tu ejemplo) */
        .pie {
            position: fixed;
            bottom: 1.2cm;
            left: 2.5cm;
            right: 2.5cm;
        }

        .pie table {
            width: 100%;
        }

        .pie td {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>

@php
    use Carbon\Carbon;

    // Fecha "hoy" (descarga de la carta), en español y hora de Costa Rica
    $hoy = Carbon::now('America/Costa_Rica')->locale('es');

    // Fecha de la feria, también en español
    $fechaFeria = $invitado->feria->fecha
        ? Carbon::parse($invitado->feria->fecha, 'America/Costa_Rica')->locale('es')
        : null;
@endphp

    {{-- ===== Encabezado con logos ===== --}}
    <table class="logos">
        <tr>
            <td class="izq">
                <img src="{{ public_path('img/mep.png') }}" alt="MEP" height="60">
            </td>
            <td class="centro">
                <img src="{{ public_path('img/micitt.png') }}" alt="MICITT" height="65">
            </td>
            <td class="der">
                <img src="{{ public_path('img/Logo.jpg') }}" alt="PRONAFECYT" height="70">
            </td>
        </tr>
    </table>

    {{-- ===== Textos centrales tipo encabezado ===== --}}
    <div class="encabezado-texto">
        <div>Ministerio de Educación Pública.</div>
        <div>Zona Norte Norte.</div>
        <div>
            Circuito
            {{ $invitado->feria->circuito->nombre ?? '________' }}
        </div>
    </div>

    {{-- ===== Cuerpo ===== --}}
    <div class="cuerpo">

        {{-- Fecha a la derecha: SIEMPRE la fecha de hoy --}}
        <p class="derecha">
            {{ $hoy->isoFormat('D [de] MMMM [de] YYYY') }}
        </p>

        {{-- Datos del destinatario a la izquierda --}}
        <p>
            {{ $invitado->institucion ?? '________________________' }}<br>
            {{ strtoupper($invitado->nombre) }}<br>
            {{ $invitado->puesto ?? '________________________' }}<br>
            S.E.
        </p>

        <p class="parrafo">
            Estimado(a) señor(a):
        </p>

        <p class="parrafo">
            Reciba un cordial saludo de parte del Comité Organizador de la Feria de Ciencia y
            Tecnología {{ $invitado->feria->anio }}.
        </p>

        {{-- Párrafo con fecha y hora de la feria --}}
        <p class="parrafo">
            Asimismo sirva la presente para comunicarle nuestro deseo de contar con su presencia en
            calidad de
            <span class="subrayado">
                {{ $invitado->tipo_invitacion === 'dedicado' ? 'Invitado(a) dedicado(a)' : 'Invitado(a) especial' }}
            </span>.
            Esta se realizará el día
            @if ($fechaFeria)
                {{ $fechaFeria->isoFormat('D [de] MMMM [de] YYYY') }}
            @else
                ____________________
            @endif
            , a las
            @if ($invitado->feria->hora_inicio)
                {{ Carbon::parse($invitado->feria->hora_inicio)->format('H:i') }}
            @else
                ______
            @endif
            horas, en {{ $invitado->feria->lugar_realizacion ?? '________________________' }}.
        </p>

        <p class="parrafo">
            Se le solicita respetuosamente confirmar su asistencia con 8 días de anticipación, por medio del
            correo electrónico {{ $invitado->feria->correo_notif ?? '________________' }} o al teléfono
            {{ $invitado->feria->telefono_fax ?? '________________' }}.
        </p>

        <p class="parrafo">
            En espera de una respuesta positiva, y con el fin de contar con su honorable presencia, se despide,
        </p>

        {{-- Firma --}}
        <div class="firma">
            <div class="firma-linea">_____________________________</div>
            <div>Coordinador(a)</div>
        </div>
    </div>

    {{-- ===== Pie de página con logos (como la tira de abajo) ===== --}}
    <div class="pie">
        <table>
            <tr>
                <td><img src="{{ public_path('img/mep.png') }}" height="30"></td>
                <td><img src="{{ public_path('img/logo_ucr.png') }}" height="60"></td>
                <td><img src="{{ public_path('img/logo_tec.png') }}" height="60"></td>
                <td><img src="{{ public_path('img/logo_una.png') }}" height="40"></td>
                <td><img src="{{ public_path('img/logo_uned.png') }}" height="60"></td>
                <td><img src="{{ public_path('img/logo_utn.png') }}" height="60"></td>
            </tr>
        </table>
    </div>

</body>
</html>
