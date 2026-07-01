<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class LiberacionSemanal extends Model
{
    protected $table = 'liberaciones_semanales';

    protected $fillable = [
        'sorteo_id',
        'semana',
        'fecha_liberacion',
        'notas',
    ];

    public function sorteo(): BelongsTo
    {
        return $this->belongsTo(Sorteo::class);
    }

    public function liberacionPremios(): HasMany
    {
        return $this->hasMany(LiberacionPremio::class);
    }

    public function totalDisponibleReal(): int
    {
        return (int) $this->liberacionPremios()
            ->selectRaw('SUM(cantidad) - SUM(cantidad_entregada) - SUM(cantidad_reservada) as saldo')
            ->value('saldo');
    }

    public function trasladarSobrantesA(LiberacionSemanal $siguienteLote): array
    {
        $resumen = [];

        DB::transaction(function () use ($siguienteLote, &$resumen) {
            $this->liberacionPremios()
                ->lockForUpdate()
                ->with('premio')
                ->get()
                ->each(function (LiberacionPremio $lp) use ($siguienteLote, &$resumen) {
                    $saldo = $lp->saldoReal();

                    if ($saldo <= 0) {
                        return;
                    }

                    $lp->decrement('cantidad', $saldo);

                    $destino = $siguienteLote->liberacionPremios()
                        ->where('premio_id', $lp->premio_id)
                        ->lockForUpdate()
                        ->first();

                    if ($destino) {
                        $destino->increment('cantidad', $saldo);
                    } else {
                        LiberacionPremio::create([
                            'liberacion_semanal_id' => $siguienteLote->id,
                            'premio_id'             => $lp->premio_id,
                            'cantidad'              => $saldo,
                            'cantidad_entregada'    => 0,
                            'cantidad_reservada'    => 0,
                        ]);
                    }

                    $resumen[] = [
                        'premio'     => $lp->premio->nombre,
                        'trasladado' => $saldo,
                    ];
                });
        });

        return $resumen;
    }
}
