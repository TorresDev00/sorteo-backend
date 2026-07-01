<?php

namespace App\Observers;

use App\Models\Registro;

class RegistroObserver
{
    public function updated(Registro $registro): void
    {
        if (! $registro->wasChanged('estado')) {
            return;
        }

        $estadoAnterior = $registro->getOriginal('estado');
        $estadoNuevo    = $registro->estado;

        if ($estadoNuevo === 'verificado' && $estadoAnterior === 'preseleccionado') {
            $registro->liberacionPremio?->decrement('cantidad_reservada');
            $registro->liberacionPremio?->increment('cantidad_entregada');
        }

        if ($estadoNuevo === 'rechazado' && $estadoAnterior === 'preseleccionado') {
            $registro->liberacionPremio?->decrement('cantidad_reservada');
            $registro->updateQuietly([
                'ganador'              => false,
                'premio_id'            => null,
                'liberacion_premio_id' => null,
            ]);
        }
    }
}
