<!DOCTYPE html>
<html>
<head>
    <title>Credencial Estudiante</title>
    <style>
        /* Configuración general */
        @page { margin: 0; }
        body { 
            font-family: sans-serif; 
            color: #333; 
            margin: 40px;
        }

        /* LOGO DE FONDO (Marca de agua) */
        .watermark {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    opacity: 0.1;
    /* Solo ponemos la imagen si la variable existe y no está vacía */
    @if(!empty($logoBase64))
        background-image: url('{{ $logoBase64 }}');
    @endif
    background-position: center;
    background-repeat: no-repeat;
    background-size: contain;
}

        /* Contenido por encima */
        .content {
            position: relative;
            z-index: 1;
        }

        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4F46E5; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #4F46E5; text-transform: uppercase; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; }

        .info-box { border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 20px; background-color: rgba(255, 255, 255, 0.9); }
        .row { margin-bottom: 12px; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
        .row:last-child { border-bottom: none; }
        
        .label { font-weight: bold; color: #4F46E5; font-size: 0.9em; display: inline-block; width: 160px; }
        .value { font-size: 1.1em; color: #111; }

        /* Estilo especial para contraseña */
        .password-box { 
            background-color: #f3f4f6; 
            padding: 10px; 
            border-radius: 5px; 
            font-family: monospace; 
            margin-top: 10px;
            border: 1px solid #cbd5e1;
        }

        .footer { text-align: center; margin-top: 50px; font-size: 0.8em; color: #888; }
    </style>
</head>
<body>
    <div class="watermark"></div>

    <div class="content">
        <div class="header">
            <h1>Credencial de Estudiante</h1>
            <p>Feria Científica y Tecnológica</p>
        </div>

        <div class="info-box">
            <h3>Datos Personales</h3>
            
            <div class="row">
                <span class="label">Nombre Completo:</span>
                <span class="value">{{ $estudiante->nombre }} {{ $estudiante->apellidos }}</span>
            </div>

            <div class="row">
                <span class="label">Cédula (Usuario):</span>
                <span class="value">{{ $estudiante->cedula }}</span>
            </div>

            <div class="row">
                <span class="label">Fecha Nacimiento:</span>
                <span class="value">{{ \Carbon\Carbon::parse($estudiante->fecha_nacimiento)->format('d/m/Y') }}</span>
            </div>

            <div class="row">
                <span class="label">Género:</span>
                <span class="value">{{ $estudiante->genero }}</span>
            </div>
        </div>

        <div class="info-box">
            <h3>Datos Académicos</h3>
            
            <div class="row">
                <span class="label">Institución:</span>
                <span class="value">{{ $estudiante->institucion->nombre ?? 'Sin Institución' }}</span>
            </div>

            <div class="row">
                <span class="label">Nivel / Sección:</span>
                <span class="value">{{ $estudiante->nivel }} - {{ $estudiante->seccion ?? 'N/A' }}</span>
            </div>
        </div>
        
        <div class="info-box">
            <h3>Credenciales de Acceso</h3>
            <div class="row">
                <span class="label">Usuario:</span>
                <span class="value">{{ $estudiante->cedula }}</span>
            </div>
            
            <div class="row">
                <span class="label">Contraseña:</span>
                @if(!empty($password))
                    <span class="value" style="font-family: monospace; font-weight: bold; background: #ffffcc; padding: 2px 5px;">{{ $password }}</span>
                    <br><small style="color: #666; font-size: 0.7em;">(Guarde esta contraseña en un lugar seguro)</small>
                @else
                    <span class="value" style="color: #999;">*********** (Oculta por seguridad)</span>
                @endif
            </div>
        </div>

        @if($estudiante->proyectos->count() > 0)
        <div class="info-box">
            <h3>Proyecto Asignado</h3>
            @foreach($estudiante->proyectos as $proyecto)
                <div class="row">
                    <span class="label">Título:</span>
                    <span class="value">{{ $proyecto->titulo }}</span>
                </div>
            @endforeach
        </div>
        @endif

        <div class="footer">
            Documento generado el: {{ date('d/m/Y') }}
        </div>
    </div>
</body>
</html>