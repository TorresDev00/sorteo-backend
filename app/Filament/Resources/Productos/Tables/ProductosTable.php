<?php

namespace App\Filament\Resources\Productos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn; // <--- Asegúrate de importar esto

class ProductosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Mostrar el ID del producto
                TextColumn::make('id')
                    ->sortable(),

                // 2. Mostrar el Nombre (y permitir buscar por nombre)
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),

                // 3. Mostrar el Código QR
                TextColumn::make('codigo_qr')
                    ->searchable(),

                // 4. Mostrar la Descripción (limitada a 50 caracteres para que no rompa el diseño)
                TextColumn::make('descripcion')
                    ->limit(50),

                // 5. Mostrar la fecha de creación
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
