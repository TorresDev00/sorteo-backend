<?php

namespace App\Filament\Resources\Productos\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput; // Importamos los componentes de formulario
use Filament\Forms\Components\Textarea;

class ProductoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Campo Nombre
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),

                // 2. Campo Código QR
                TextInput::make('codigo_qr')
                    ->required()
                    ->unique(table: 'productos', ignoreRecord: true)
                    ->maxLength(255),

                // 3. Campo Descripción
                Textarea::make('descripcion')
                    ->columnSpanFull(),
            ]);
    }
}