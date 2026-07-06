<?php

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\ListActivities;
use Spatie\Activitylog\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema; // O Filament\Forms\Form dependiendo de tu versión exacta de Filament v5
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    // 1. 📋 CAMBIAR ICONO Y NOMBRE EN EL MENÚ
    // Usamos un icono de historial/reloj o lista para que se asocie con auditoría
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 6;
    

    protected static ?string $navigationLabel = 'Bitácora de Cambios';
    protected static ?string $pluralModelLabel = 'Bitácora de Cambios';
    protected static ?string $modelLabel = 'Registro de Actividad';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha / Hora')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->description(fn($record): string => $record->created_at->diffForHumans()), // Ej: "hace 5 minutos" abajo de la fecha

                TextColumn::make('log_name')
                    ->label('Módulo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'default' => 'gray',
                        'sorteos' => 'warning',
                        'premios', 'productos' => 'success',
                        'registros' => 'info',
                        'distribuidores' => 'danger',
                        'liberaciones_semanales' => 'purple',
                        default => 'primary',
                    })
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Acción Realizada')
                    ->searchable()
                    ->weight('bold'), 
                TextColumn::make('properties.attributes')
                    ->label('Valores Nuevos / Cambios')
                    ->wrap() 
                    ->lineClamp(3) 
                    ->placeholder('Sin propiedades guardadas')
                    ->formatStateUsing(function ($state) {
                        if (blank($state)) return null;
                        return collect($state)
                            ->map(fn($value, $key) => "• " . ucfirst($key) . ": " . (is_array($value) ? json_encode($value) : $value))
                            ->implode("\n");
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                // 5. USUARIO RESPONSABLE
                TextColumn::make('causer.name')
                    ->label('Usuario Responsable')
                    ->placeholder('Sistema / Proceso Automático')
                    ->searchable()
                    ->icon('heroicon-m-user'), 
            ])

            // ORDEN DE LA TABLA
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filtro rápido por tipo de acción (creado, editado, eliminado)
                SelectFilter::make('description')
                    ->label('Tipo de Acción')
                    ->options([
                        'created' => 'Creaciones',
                        'updated' => 'Modificaciones',
                        'deleted' => 'Eliminaciones',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $q, $value) => $q->where('description', 'like', "%{$value}%")
                        );
                    }),

                SelectFilter::make('log_name')
                    ->label('Filtrar por Módulo')
                    ->options([
                        'sorteos' => 'Sorteos',
                        'premios' => 'Premios / Productos',
                        'registros' => 'Registros / Facturas',
                        'distribuidores' => 'Distribuidores',
                        'liberaciones_semanales' => 'Lotes Semanales',
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }
}
