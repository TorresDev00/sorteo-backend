<?php

namespace App\Observers;

use App\Models\LiberacionPremio;

class LiberacionPremioObserver
{

    public function created(LiberacionPremio $liberacionPremio): void
    {
        $premio = $liberacionPremio->premio;
        $aDescontar = min($liberacionPremio->cantidad, max(0, $premio->cantidad_disponible));
        
        if ($aDescontar > 0) {
            $premio->decrement('cantidad_disponible', $aDescontar);
        }
    }

    public function updated(LiberacionPremio $liberacionPremio): void
    {
        $cantidadAnterior = $liberacionPremio->getOriginal('cantidad');
        $cantidadNueva = $liberacionPremio->cantidad;
        $diferencia = $cantidadNueva - $cantidadAnterior;

        if ($diferencia === 0) {
            return;
        }

        $premio = $liberacionPremio->premio;

        if ($diferencia > 0) {
            $aDescontar = min($diferencia, max(0, $premio->cantidad_disponible));
            if ($aDescontar > 0) {
                $premio->decrement('cantidad_disponible', $aDescontar);
            }
        } else {
            $premio->increment('cantidad_disponible', abs($diferencia));
        }
    }


    public function deleted(LiberacionPremio $liberacionPremio): void
    {
        $liberacionPremio->premio->increment('cantidad_disponible', $liberacionPremio->cantidad);
    }
}