<?php

namespace App\Services;

use App\Models\LiberacionPremio;
use App\Models\LiberacionSemanal;
use App\Models\Registro;
use App\Models\Sorteo;
use Illuminate\Support\Facades\DB;

class SorteoService
{
    private const ORDEN_POR_TIPO = [
        'merch' => 1,
        'experiencia_de_marca' => 2,
        'electrodomesticos' => 3,
    ];

    private const FACTOR_ESTIMADO_PRIMERA_SEMANA = 50;
    private const MARGEN_SEGURIDAD_HISTORICO = 1.15;

    public function obtenerSemanaActual(Sorteo $sorteo): int
    {
        $diasTranscurridos = $sorteo->fecha_inicio
            ->startOfDay()
            ->diffInDays(now()->startOfDay());

        return (int) floor($diasTranscurridos / 7) + 1;
    }

    public function obtenerLiberacionActual(Sorteo $sorteo, int $semana): ?LiberacionSemanal
    {
        return LiberacionSemanal::where('sorteo_id', $sorteo->id)
            ->where('semana', $semana)
            ->first();
    }

    public function procesarParticipacion(
        Sorteo $sorteo,
        array $datosValidados,
        string $pathFactura
    ): Registro {
        $semana            = $this->obtenerSemanaActual($sorteo);
        $liberacionSemanal = $this->obtenerLiberacionActual($sorteo, $semana);

        $registro = Registro::create([
            ...$datosValidados,
            'sorteo_id'      => $sorteo->id,
            'factura_imagen' => $pathFactura,
            'semana'         => $semana,
            'estado'         => 'pendiente',
            'ganador'        => false,
            'fecha_registro' => now(),
        ]);

        if (! $liberacionSemanal) {
            return $registro;
        }

        $this->evaluarPremiosDelLote($registro, $liberacionSemanal);

        return $registro->fresh();
    }

    private function evaluarPremiosDelLote(Registro $registro, LiberacionSemanal $lote): void
    {
        DB::transaction(function () use ($registro, $lote) {
            $sinEvaluar = Registro::where('sorteo_id', $lote->sorteo_id)
                ->where('semana', $lote->semana)
                ->sinEvaluar()
                ->count();

            $participantesEsperados = $this->estimarParticipantesEsperados($lote);
            $proyectadosRestantes   = max($participantesEsperados - $sinEvaluar, 1);

            $liberacionPremios = $lote->liberacionPremios()
                ->conSaldoReal()
                ->lockForUpdate()
                ->with('premio')
                ->get()
                ->sortBy(fn (LiberacionPremio $lp) => self::ORDEN_POR_TIPO[$lp->premio->tipo] ?? 99);

            foreach ($liberacionPremios as $liberacionPremio) {
                $cuposReales  = $liberacionPremio->saldoReal();
                $probabilidad = $cuposReales / $proyectadosRestantes;

                $tiroAzar = mt_rand() / mt_getrandmax();

                if ($tiroAzar > $probabilidad) {
                    continue;
                }

                if ($liberacionPremio->saldoReal() <= 0) {
                    continue;
                }

                $registro->updateQuietly([
                    'estado'               => 'preseleccionado',
                    'ganador'              => true,
                    'premio_id'            => $liberacionPremio->premio_id,
                    'liberacion_premio_id' => $liberacionPremio->id,
                ]);

                $liberacionPremio->increment('cantidad_reservada');

                return;
            }
        });
    }

    private function estimarParticipantesEsperados(LiberacionSemanal $lote): int
    {
        $promedioHistorico = Registro::where('sorteo_id', $lote->sorteo_id)
            ->where('semana', '<', $lote->semana)
            ->select('semana')
            ->groupBy('semana')
            ->selectRaw('count(*) as total')
            ->pluck('total')
            ->avg();

        if ($promedioHistorico) {
            return (int) ceil($promedioHistorico * self::MARGEN_SEGURIDAD_HISTORICO);
        }

        $totalPremiosLiberados = $lote->liberacionPremios()->sum('cantidad');

        return max($totalPremiosLiberados * self::FACTOR_ESTIMADO_PRIMERA_SEMANA, 1);
    }
}