<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carnet de colaborador</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Times New Roman", serif;
            color: #000033;
        }

        .contenedor {
            width: 18cm;
            height: 13cm;
            border: 2px solid #000033;
            box-sizing: border-box;
            padding: 15px 20px;
            position: relative;
        }

        /* ====== ENCABEZADO SUPERIOR ====== */
        .barra-superior {
            width: 100%;
            border-bottom: 1px solid #000033;
            padding-bottom: 4px;
        }

        .tabla-encabezado {
            width: 100%;
        }

        .tabla-encabezado td {
            vertical-align: middle;
        }

        .logo-izq {
            text-align: left;
        }

        .logo-der {
            text-align: right;
        }

        .titulo-ministerio {
            text-align: center;
            font-family: "Monotype Corsiva","Brush Script MT","Lucida Handwriting",cursive;
            font-size: 18pt;
            font-weight: bold;
        }

        .sublinea {
            text-align: center;
            font-family: "Monotype Corsiva","Brush Script MT","Lucida Handwriting",cursive;
            font-size: 15pt;
        }

        .caja-circuito {
            margin: 6px auto 0 auto;
            display: inline-block;
            padding: 2px 20px;
            border: 1px solid #000033;
            font-size: 12pt;
        }

        /* ====== CUERPO CENTRAL ====== */
        .zona-central {
            margin-top: 25px;
            text-align: center;
        }

        .tipo-colaborador {
            font-family: "Monotype Corsiva","Brush Script MT","Lucida Handwriting",cursive;
            font-size: 28pt;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .caja-nombre {
            width: 9cm;
            height: 4cm;
            margin: 0 auto;
            border: 1px solid #000033;
            display: table;
        }

        .caja-nombre-inner {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            font-family: "Monotype Corsiva","Brush Script MT","Lucida Handwriting",cursive;
            font-size: 26pt;
            font-weight: bold;
        }

        /* Logos inferiores laterales */
        .logo-inf-izq,
        .logo-inf-der {
            position: absolute;
            bottom: 25px;
        }

        .logo-inf-izq {
            left: 25px;
        }

        .logo-inf-der {
            right: 25px;
        }

        /* ====== BARRA DE CUADRITOS INFERIOR ====== */
        .barra-inferior {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 5px;
            text-align: center;
        }

        .barra-inferior-inner {
            margin: 0 10px;
            border-top: 3px dashed #000033;
            height: 0;
        }
    </style>
</head>
<body>
    <div class="contenedor">

        {{-- ===== Encabezado superior con logos y textos ===== --}}
        <div class="barra-superior">
            <table class="tabla-encabezado">
                <tr>
                    <td class="logo-izq" style="width: 20%;">
                        <img src="{{ public_path('img/mep.png') }}" alt="MEP" height="40">
                    </td>
                    <td style="width: 60%;">
                        <div class="titulo-ministerio">Ministerio de Educación Pública.</div>
                        <div class="sublinea">Zona Norte Norte.</div>
                        <div class="caja-circuito">
                            Circuito {{ $colaborador->feria->circuito->nombre ?? '____' }}
                        </div>
                    </td>
                    <td class="logo-der" style="width: 20%;">
                        <img src="{{ public_path('img/micitt.png') }}" alt="MICITT" height="40">
                    </td>
                </tr>
            </table>
        </div>

        {{-- ===== Zona central: rol y nombre ===== --}}
        <div class="zona-central">
            <div class="tipo-colaborador">
                Colaborador(a)
            </div>

            <div class="caja-nombre">
                <div class="caja-nombre-inner">
                    {{ strtoupper($colaborador->nombre) }}
                </div>
            </div>
        </div>

        {{-- Logos inferiores laterales --}}
        <div class="logo-inf-izq">
            <img src="{{ public_path('img/Logo.jpg') }}" alt="PRONAFECYT" height="80">
        </div>

        <div class="logo-inf-der">
            <img src="{{ public_path('img/Logo.jpg') }}" alt="PRONAFECYT" height="80">
        </div>

        {{-- Barra inferior de cuadritos (simulada con línea punteada) --}}
        <div class="barra-inferior">
            <div class="barra-inferior-inner"></div>
        </div>

    </div>
</body>
</html>
