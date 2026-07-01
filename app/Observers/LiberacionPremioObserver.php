<?php

namespace App\Observers;

use App\Models\LiberacionPremio;

class LiberacionPremioObserver
{

    public function created(LiberacionPremio $liberacionPremio): void
    {
        $premio = $liberacionPremio->premio;
        $premio->decrement('cantidad_disponible', $liberacionPremio->cantidad);
    }

    public function updated(LiberacionPremio $liberacionPremio): void
    {
        $cambio = $liberacionPremio->cantidad - $liberacionPremio->getOriginal('cantidad');
        
        if ($cambio !== 0) {
            $liberacionPremio->premio->decrement('cantidad_disponible', $cambio);
        }
    }

    public function deleted(LiberacionPremio $liberacionPremio): void
    {
        $liberacionPremio->premio->increment('cantidad_disponible', $liberacionPremio->cantidad);
    }
}