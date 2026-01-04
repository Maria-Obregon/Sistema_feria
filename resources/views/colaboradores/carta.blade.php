{{-- resources/views/colaboradores/carta.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta de agradecimiento</title>
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

        .parrafo {
            margin-top: 10px;
            text-align: justify;
        }

        /* Firma (igual que en invitados) */
        .firma {
            margin-top: 45px;
            text-align: center;
        }

        .firma-linea {
            margin-bottom: 1px;
        }

        /* Pie de página con logos (como en invitados) */
         /* Pie de página con logos (como en tu ejemplo) */
        .pie {
            position: fixed;
            bottom: 0.0001cm;
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
    $fechaFeria = $colaborador->feria->fecha
        ? Carbon::parse($colaborador->feria->fecha, 'America/Costa_Rica')->locale('es')
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
            Circuito {{ $colaborador->feria->circuito->nombre ?? '________' }}
        </div>
    </div>

    {{-- ===== Cuerpo ===== --}}
    <div class="cuerpo">

        {{-- Fecha a la derecha: SIEMPRE la fecha de hoy --}}
        <p class="derecha">
            {{ $hoy->isoFormat('D [de] MMMM [de] YYYY') }}
        </p>

        {{-- Datos del colaborador a la izquierda --}}
        <p>
            {{ $colaborador->institucion ?? '________________________' }}<br>
            {{ strtoupper($colaborador->nombre) }}<br>
            {{ $colaborador->funcion ?? '________________________' }}<br>
            S.E.
        </p>

        <p class="parrafo">
            Estimado(a) señor(a):
        </p>

        <p class="parrafo">
            El Comité Organizador de la Feria de Ciencia y Tecnología {{ $colaborador->feria->anio }}
            desea expresar su más sincero agradecimiento por su disposición para colaborar en la
            realización de esta actividad.
        </p>

        <p class="parrafo">
            Su apoyo en las tareas de organización, logística y atención de participantes resulta
            fundamental para el buen desarrollo de la feria, la cual se llevará a cabo el día
            @if ($fechaFeria)
                {{ $fechaFeria->isoFormat('D [de] MMMM [de] YYYY') }}
            @else
                ____________________
            @endif
            @if ($colaborador->feria->hora_inicio)
                , a las {{ Carbon::parse($colaborador->feria->hora_inicio)->format('H:i') }} horas,
            @else
                , a las ______ horas,
            @endif
            en {{ $colaborador->feria->lugar_realizacion ?? '________________________' }}.
        </p>

        <p class="parrafo">
            Reconocemos el tiempo, la dedicación y el compromiso que usted aporta en beneficio del
            estudiantado y de la promoción de la ciencia y la tecnología. Su colaboración contribuye
            de manera significativa al éxito de esta feria.
        </p>

        @if (!empty($colaborador->mensaje_agradecimiento))
            <p class="parrafo">
                Mensaje especial del comité organizador:<br>
                {{ $colaborador->mensaje_agradecimiento }}
            </p>
        @endif

        <p class="parrafo">
            Sin otro particular, reiteramos nuestro agradecimiento y quedamos a su disposición para
            futuras actividades.
        </p>

        {{-- Firma, bien separada del texto --}}
        <div class="firma">
            <div class="firma-linea">_____________________________</div>
            <div>Coordinador(a)</div>
        </div>
    </div>

    {{-- ===== Pie de página con logos (tira inferior) ===== --}}
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
