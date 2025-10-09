@component('mail::message')
# {{ $esReset ? 'Contraseña restablecida' : 'Bienvenido/a' }}

Hola **{{ $usuario->nombre }}**,

@if($esReset)
Tu contraseña ha sido **restablecida**.
@else
Se ha creado una cuenta para ti en la plataforma.
@endif

**Usuario (correo):** {{ $usuario->email }}  
**Contraseña:** {{ $passwordPlano }}

> Te recomendamos cambiar tu contraseña al iniciar sesión.

@component('mail::button', ['url' => config('app.url') . '/login'])
Ir a iniciar sesión
@endcomponent

Gracias,  
**{{ config('app.name') }}**
@endcomponent
