<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de participación - Estudiante</title>
    <style>
        @page {
            margin: 1.5cm;
        }

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

        .texto-centro {
            text-align: center;
            margin-top: 32px;
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

        /* Firma */
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

        /* Franjas de colores (derecha y parte inferior) */
        .franja-derecha {
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 100%;
            background: linear-gradient(to bottom, #3CB54A 33%, #0078BE 66%, #F7941D 100%);
            clip-path: polygon(0 15%, 100% 0, 100% 100%, 0 100%);
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
            clip-path: polygon(0 100%, 35% 0, 100% 0, 100% 100%);
        }

        /* Logos inferiores */
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
<div class="marco">

    {{-- Icono de certificado de estudiante (naranja) --}}
    <div class="logo-esquina">
        <img src="{{ public_path('img/cert_estudiante_icon.png') }}" alt="Ícono estudiante" height="110">
    </div>

    {{-- Texto superior --}}
    <div class="titulo">
        El Programa Nacional de Ferias de Ciencia<br>
        y Tecnología
    </div>

    <div class="subtitulo">
        Certifica la participación de:
    </div>

    {{-- Nombre estudiante --}}
    <div class="nombre">
        {{ strtoupper($estudiante->nombre_completo) }}
    </div>

    {{-- Texto central --}}
    <div class="parrafo">
        Por su valiosa participación en calidad de estudiante con el proyecto:
    </div>

    <div class="parrafo" style="margin-top: 12px; font-weight:bold;">
        “{{ $proyecto->titulo ?? '________________________' }}”
    </div>

    <div class="parrafo" style="margin-top: 24px;">
        En la categoría:
        <span style="font-weight:bold;">
            {{ $proyecto->categoria->nombre ?? '________________' }}
        </span>
    </div>

    <div class="etapa">
        En la etapa:
        <span style="font-weight:bold;">
            {{ $feria->etapa_label ?? '________________' }}
        </span>
    </div>

    {{-- Firma --}}
    <div class="firma">
        <div class="firma-linea"></div>
        <div class="firma-texto">COORDINADOR(A).</div>
    </div>

    {{-- Franjas --}}
    <div class="franja-derecha"></div>
    <div class="franja-inferior"></div>

    {{-- Logos inferiores --}}
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
