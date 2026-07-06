<?php

namespace App\Filament\Widgets;

use App\Models\LiberacionPremio;
use App\Models\Sorteo;
use App\Services\SorteoService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PremiosSemanaActualWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '30s';
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Inventario de premios — semana actual';
    }

    public function table(Table $table): Table
    {
        $sorteo = Sorteo::where('activo', true)->latest('id')->first();

        if (! $sorteo) {
            return $table->query(LiberacionPremio::query()->whereRaw('1 = 0'));
        }

        $service      = app(SorteoService::class);
        $semanaActual = $service->obtenerSemanaActual($sorteo);
        $lote         = $service->obtenerLiberacionActual($sorteo, $semanaActual);

        $query = $lote
            ? LiberacionPremio::query()->where('liberacion_semanal_id', $lote->id)
            : LiberacionPremio::query()->whereRaw('1 = 0');

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('premio.nombre')->label('Premio'),

                Tables\Columns\TextColumn::make('premio.tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'merch' => 'info',
                        'experiencia_de_marca' => 'warning',
                        'electrodomesticos' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('cantidad')->label('Liberados')->alignCenter(),
                Tables\Columns\TextColumn::make('cantidad_entregada')->label('Entregados')->alignCenter(),
                Tables\Columns\TextColumn::make('cantidad_reservada')->label('Reservados')->alignCenter(),

                Tables\Columns\TextColumn::make('saldo')
                    ->label('Disponible')
                    ->state(fn (LiberacionPremio $record) => $record->saldoReal())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}