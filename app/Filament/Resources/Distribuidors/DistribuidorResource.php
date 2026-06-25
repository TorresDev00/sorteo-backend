<?php

namespace App\Filament\Resources\Distribuidors;

use App\Filament\Resources\Distribuidors\Pages\CreateDistribuidor;
use App\Filament\Resources\Distribuidors\Pages\EditDistribuidor;
use App\Filament\Resources\Distribuidors\Pages\ListDistribuidors;
use App\Filament\Resources\Distribuidors\Pages\ViewDistribuidor;
use App\Filament\Resources\Distribuidors\Schemas\DistribuidorForm;
use App\Filament\Resources\Distribuidors\Schemas\DistribuidorInfolist;
use App\Filament\Resources\Distribuidors\Tables\DistribuidorsTable;
use App\Models\Distribuidor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;

class DistribuidorResource extends Resource
{
    protected static ?string $model = Distribuidor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'nombre_comercial';
    protected static ?string $navigationLabel = 'Distribuidores';
    protected static ?string $pluralModelLabel = 'Distribuidores';
    protected static ?string $modelLabel = 'Distribuidor';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('nombre_comercial')->label('Razón Comercial')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('telefono')->label('Teléfono Máster')->required(),
                TextInput::make('estado_ubicacion')->label('Estado / Región')->required(),
                Textarea::make('mensaje')->label('Mensaje del Lead')->disabled(),

                Select::make('estatus_lead')
                    ->label('Estatus Comercial')
                    ->options([
                        'nuevo' => 'Nuevo Lead',
                        'contactado' => 'En Negociación / Contactado',
                        'rechazado' => 'No Califica / Rechazado',
                    ])
                    ->required(),
                Textarea::make('notas_administrador')->label('Notas de Auditoría Interna'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DistribuidorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_comercial')->label('Comercio / Aliado')->searchable(),
                TextColumn::make('estado_ubicacion')->label('Ubicación Geográfica')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('telefono')->label('Contacto'),
                TextColumn::make('estatus_lead')
                    ->label('Estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'nuevo' => 'info',
                        'contactado' => 'warning',
                        'rechazado' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('estatus_lead')->options([
                    'nuevo' => 'Nuevo',
                    'contactado' => 'Contactado',
                    'rechazado' => 'Rechazado',
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDistribuidors::route('/'),
            'create' => CreateDistribuidor::route('/create'),
            'view' => ViewDistribuidor::route('/{record}'),
            'edit' => EditDistribuidor::route('/{record}/edit'),
        ];
    }
}
