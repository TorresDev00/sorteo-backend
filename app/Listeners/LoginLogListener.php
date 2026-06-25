<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Spatie\Activitylog\Models\Activity;

class LoginLogListener
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof Login) {
            activity('registros') // Lo asignamos al módulo de registros
                ->causedBy($event->user) // Guardamos quién fue
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log("Inicio de sesión exitoso: El usuario {$event->user->email} ha ingresado al panel.");
        }

        // CASO 2: El usuario metió mal los datos (Fallo)
        if ($event instanceof Failed) {
            // Intentamos obtener el correo que escribieron en el formulario
            $emailIntentado = $event->credentials['email'] ?? 'Desconocido';

            activity('registros')
                ->causedBy(auth()->user() ?? null) // Será null porque no logró entrar
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'intento_correo' => $emailIntentado,
                ])
                ->log("Intento de login fallido: Se ingresaron credenciales erróneas para la cuenta: {$emailIntentado}.");
        }
    }
}
