<?php

namespace App\Filament\Resources\Registros\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class RegistroInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('factura_imagen')
                    ->label('Factura')
                    ->disk('public')
                    ->square()
                    ->url(fn ($record) => asset('storage/' . $record->factura_imagen))
                    ->openUrlInNewTab()
                    ->columnSpanFull(),
                TextEntry::make('cedula')->label('Cédula'),
                TextEntry::make('nombre')->label('Nombre'),
                TextEntry::make('telefono')->label('Teléfono'),
                TextEntry::make('lugar_compra')->label('Lugar de Compra'),
                TextEntry::make('semana')->label('Semana'),
                TextEntry::make('estado')->label('Estatus')->badge(),
                IconEntry::make('ganador')->label('Ganador')->boolean(),
                TextEntry::make('premio.nombre')->label('Premio'),
                TextEntry::make('created_at')->label('Registrado')->dateTime(),
            ]);
    }
}