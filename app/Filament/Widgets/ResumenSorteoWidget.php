<?php

namespace App\Filament\Widgets;

use App\Models\Registro;
use App\Models\Sorteo;
use App\Services\SorteoService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumenSorteoWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $sorteo = Sorteo::where('activo', true)->latest('id')->first();

        if (! $sorteo) {
            return [
                Stat::make('Sin sorteo activo', '—')
                    ->description('No hay ningún sorteo marcado como activo actualmente')
                    ->color('gray'),
            ];
        }

        $registros = Registro::where('sorteo_id', $sorteo->id);

        $totalParticipantes = (clone $registros)->count();
        $perdedores         = (clone $registros)->where('estado', 'pendiente')->count();
        $porRevisar         = (clone $registros)->where('estado', 'preseleccionado')->count();
        $verificados        = (clone $registros)->where('estado', 'verificado')->count();
        $rechazados         = (clone $registros)->where('estado', 'rechazado')->count();
        $ganadoresTotal     = $porRevisar + $verificados;

        $service      = app(SorteoService::class);
        $semanaActual = $service->obtenerSemanaActual($sorteo);
        $loteActual   = $service->obtenerLiberacionActual($sorteo, $semanaActual);

        $premiosDisponibles = $loteActual
            ?->liberacionPremios()
            ->get()
            ->sum(fn ($lp) => $lp->saldoReal()) ?? 0;

        // Tendencia de los últimos 7 días para el mini-gráfico
        $tendencia = collect(range(6, 0))
            ->map(fn ($i) => (clone $registros)
                ->whereDate('created_at', now()->subDays($i))
                ->count())
            ->all();

        return [
            Stat::make('Participantes totales', $totalParticipantes)
                ->description("Semana actual: {$semanaActual}")
                ->descriptionIcon('heroicon-m-users')
                ->chart($tendencia)
                ->color('info'),

            Stat::make('Ganadores', $ganadoresTotal)
                ->description('Preseleccionados + verificados')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),

            Stat::make('Pendientes por revisar', $porRevisar)
                ->description('Facturas esperando validación tuya')
                ->descriptionIcon('heroicon-m-clock')
                ->color($porRevisar > 0 ? 'warning' : 'gray'),

            Stat::make('Perdedores', $perdedores)
                ->description('No resultaron ganadores')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('gray'),

            Stat::make('Rechazados', $rechazados)
                ->description('Factura inválida, premio revocado')
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color('danger'),

            Stat::make('Premios disponibles', $premiosDisponibles)
                ->description("En el lote de la semana {$semanaActual}")
                ->descriptionIcon('heroicon-m-gift')
                ->color('primary'),
        ];
    }
}