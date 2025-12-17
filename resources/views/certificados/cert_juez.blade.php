<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de participación - Juez</title>
    <style>
        @page { margin: 1.5cm; }

        body {
            font-family: "Times New Roman", serif;
            color: #003366;
        }

        .marco {
            border: 7px solid #00427A;
            padding: 35px 45px 25px 45px;
            position: relative;
            min-height: 18cm;
            box-sizing: border-box;
        }

        .logo-esquina {
            position: absolute;
            top: 25px;
            left: 25px;
        }

        .titulo {
            text-align: center;
            margin-top: 10px;
            font-family: "Monotype Corsiva","Brush Script MT","Lucida Handwriting",cursive;
            font-size: 22pt;
            line-height: 1.2;
        }

        .subtitulo {
            text-align: center;
            margin-top: 15px;
            font-family: "Monotype Corsiva","Brush Script MT","Lucida Handwriting",cursive;
            font-size: 18pt;
        }

        .nombre {
            text-align: center;
            margin-top: 18px;
            font-family: "Monotype Corsiva","Brush Script MT","Lucida Handwriting",cursive;
            font-size: 24pt;
            font-weight: bold;
        }

        .parrafo {
            text-align: center;
            margin-top: 18px;
            font-family: "Monotype Corsiva","Brush Script MT","Lucida Handwriting",cursive;
            font-size: 18pt;
        }

        .etapa {
            margin-top: 26px;
            text-align: center;
            font-family: "Monotype Corsiva","Brush Script MT","Lucida Handwriting",cursive;
            font-size: 18pt;
        }

        .firma {
            position: absolute;
            left: 60px;
            bottom: 90px;
            text-align: left;
        }

        .firma-linea {
            border-top: 1px solid #003366;
            width: 240px;
            margin-bottom: 4px;
        }

        .firma-texto {
            font-size: 11pt;
        }

        .franja-derecha {
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 100%;
            background: linear-gradient(to bottom,#3CB54A 33%,#0078BE 66%,#F7941D 100%);
            clip-path: polygon(0 15%,100% 0,100% 100%,0 100%);
        }

        .franja-inferior {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 40px;
            height: 35px;
            background: linear-gradient(
                to right,
                #3CB54A 0%, #3CB54A 33%,
                #0078BE 33%, #0078BE 66%,
                #F7941D 66%, #F7941D 100%
            );
            clip-path: polygon(0 100%,35% 0,100% 0,100% 100%);
        }

        .logos-inferiores {
            position: absolute;
            left: 45px;
            right: 45px;
            bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logos-inferiores img {
            height: 32px;
        }
    </style>
</head>
<body>
@php
    $mapEtapa = [
        'institucional' => 'Institucional',
        'circuital'     => 'Circuital',
        'regional'      => 'Regional',
    ];

    $etapa = $mapEtapa[$feria->tipo_feria ?? ''] ?? 'Institucional / Circuital / Regional';
    $anio  = $feria->anio ?? date('Y');

    $institucionLinea = trim(
        (optional($feria->institucion)->nombre ?? '') .
        (optional($feria->circuito)->nombre
            ? ' · ' . optional($feria->circuito)->nombre
            : ''
        ) .
        (optional(optional($feria->institucion)->regional)->nombre
            ? ' · ' . optional(optional($feria->institucion)->regional)->nombre
            : ''
        )
    );
@endphp

<div class="marco">

    <div class="logo-esquina">
        <img src="{{ public_path('img/cert_juez_icon.png') }}" alt="Ícono juez" height="110">
    </div>

    <div class="titulo">
        El Programa Nacional de Ferias de Ciencia<br>
        y Tecnología
    </div>

    <div class="subtitulo">
        Certifica la participación de:
    </div>

    <div class="nombre">
        {{ strtoupper($juez->nombre_completo ?? $juez->nombre) }}
    </div>

    <div class="parrafo">
        Por su valiosa participación en el proceso de<br>
        <span style="font-weight:bold;">Juzgamiento</span>
        de la Feria de Ciencia y Tecnología.
    </div>

    <div class="etapa">
        En la etapa:
        <span style="font-weight:bold;">{{ $etapa }}</span>
    </div>

    <div class="parrafo" style="margin-top: 24px;">
        {{ $institucionLinea ?: 'Nombre de la institución, Circuito, Regional' }}
    </div>

    <div class="parrafo" style="margin-top: 8px; font-size: 14pt;">
        PROCESO FERIA DE CIENCIA Y TECNOLOGÍA {{ $anio }}
    </div>

    <div class="firma">
        <div class="firma-linea"></div>
        <div class="firma-texto">COORDINADOR(A).</div>
    </div>

    <div class="franja-derecha"></div>
    <div class="franja-inferior"></div>

    <div class="logos-inferiores">
        <img src="{{ public_path('img/logo_micitt.png') }}" alt="MICITT">
        <img src="{{ public_path('img/logo_gobierno.png') }}" alt="Gobierno de Costa Rica">
        <img src="{{ public_path('img/mep.png') }}" alt="MEP">
        <img src="{{ public_path('img/logo_ucr.png') }}" alt="UCR">
        <img src="{{ public_path('img/logo_tec.png') }}" alt="TEC">
        <img src="{{ public_path('img/logo_una.png') }}" alt="UNA">
        <img src="{{ public_path('img/logo_uned.png') }}" alt="UNED">
        <img src="{{ public_path('img/logo_utn.png') }}" alt="UTN">
    </div>

</div>
</body>
</html>
