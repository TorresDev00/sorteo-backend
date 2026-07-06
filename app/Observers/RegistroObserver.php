<?php

namespace App\Observers;

use App\Models\Registro;
use Illuminate\Support\Facades\Log;

class RegistroObserver
{
    public function updated(Registro $registro): void
    {
        if (! $registro->wasChanged('estado')) {
            return;
        }

        $anterior = $registro->getOriginal('estado');
        $nuevo    = $registro->estado;

        match (true) {
            // Preseleccionado -> Verificado: confirma la entrega
            $anterior === 'preseleccionado' && $nuevo === 'verificado' =>
                $this->confirmarEntrega($registro),

            // Preseleccionado -> Rechazado: nunca se entregó, solo libera la reserva
            $anterior === 'preseleccionado' && $nuevo === 'rechazado' =>
                $this->liberarReserva($registro),

            // Verificado -> Rechazado: el premio YA se había entregado, hay que revertirlo
            $anterior === 'verificado' && $nuevo === 'rechazado' =>
                $this->revertirEntrega($registro),

            // Verificado -> Preseleccionado: revertir una verificación hecha por error
            $anterior === 'verificado' && $nuevo === 'preseleccionado' =>
                $this->revertirEntregaAReserva($registro),

            default => $this->registrarTransicionNoManejada($registro, $anterior, $nuevo),
        };
    }

    private function confirmarEntrega(Registro $registro): void
    {
        $lp = $registro->liberacionPremio;

        if (! $lp) {
            $this->advertirSinLiberacionPremio($registro);
            return;
        }

        $lp->decrement('cantidad_reservada');
        $lp->increment('cantidad_entregada');
    }

    private function liberarReserva(Registro $registro): void
    {
        $registro->liberacionPremio?->decrement('cantidad_reservada');
        $this->limpiarPremio($registro);
    }

    private function revertirEntrega(Registro $registro): void
    {
        $lp = $registro->liberacionPremio;

        if ($lp) {
            $lp->decrement('cantidad_entregada');
        } else {
            $this->advertirSinLiberacionPremio($registro);
        }

        $this->limpiarPremio($registro);
    }

    private function revertirEntregaAReserva(Registro $registro): void
    {
        $lp = $registro->liberacionPremio;

        if (! $lp) {
            $this->advertirSinLiberacionPremio($registro);
            return;
        }

        $lp->decrement('cantidad_entregada');
        $lp->increment('cantidad_reservada');
    }

    private function limpiarPremio(Registro $registro): void
    {
        $registro->updateQuietly([
            'ganador'              => false,
            'premio_id'            => null,
            'liberacion_premio_id' => null,
        ]);
    }

    private function advertirSinLiberacionPremio(Registro $registro): void
    {
        Log::warning("RegistroObserver: registro {$registro->id} cambió de estado pero no tiene liberacion_premio_id — no se pudo ajustar el inventario. Revisar manualmente.");
    }

    private function registrarTransicionNoManejada(Registro $registro, ?string $anterior, string $nuevo): void
    {
        Log::warning("RegistroObserver: transición no contemplada en registro {$registro->id}: {$anterior} → {$nuevo}. Verifica si el inventario necesita ajuste manual.");
    }
}