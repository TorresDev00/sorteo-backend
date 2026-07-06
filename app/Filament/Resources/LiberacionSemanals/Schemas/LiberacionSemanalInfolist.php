<?php

namespace App\Filament\Resources\LiberacionSemanals\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class LiberacionSemanalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema 
            ->components([
                TextEntry::make('sorteo.nombre')
                    ->label('Sorteo'),

                TextEntry::make('semana')
                    ->label('Semana')
                    ->badge()
                    ->color('warning'),

                TextEntry::make('fecha_liberacion')
                    ->label('Fecha de Liberación')
                    ->dateTime('d/m/Y H:i A'),

                TextEntry::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),

                RepeatableEntry::make('liberacionPremios')
                    ->label('Premios liberados esta semana')
                    ->schema([
                        TextEntry::make('premio.nombre')
                            ->label('Premio'),

                        TextEntry::make('cantidad')
                            ->label('Cantidad liberada'),

                        TextEntry::make('cantidad_entregada')
                            ->label('Entregados'),

                        TextEntry::make('cantidad_reservada')
                            ->label('Reservados'),

                        TextEntry::make('saldoReal')
                            ->label('Disponible')
                            ->formatStateUsing(fn ($record) => $record->saldoReal()),
                    ])
                    ->columns(5)
                    ->columnSpanFull(),
            ]);
    }
}