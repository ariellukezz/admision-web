<x-mail::message>
# Recuperación de contraseña

Hemos recibido una solicitud para restablecer tu contraseña.

<x-mail::button :url="$actionUrl">
Restablecer contraseña
</x-mail::button>

El enlace expirará en 60 minutos.

Si no solicitaste este cambio, puedes ignorarlo.

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>