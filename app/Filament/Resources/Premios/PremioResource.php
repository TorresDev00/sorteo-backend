<?php

namespace App\Filament\Resources\Premios;

use App\Filament\Resources\Premios\Pages\CreatePremio;
use App\Filament\Resources\Premios\Pages\EditPremio;
use App\Filament\Resources\Premios\Pages\ListPremios;
use App\Filament\Resources\Premios\Pages\ViewPremio;
use App\Filament\Resources\Premios\Schemas\PremioForm;
use App\Filament\Resources\Premios\Schemas\PremioInfolist;
use App\Filament\Resources\Premios\Tables\PremiosTable;
use App\Models\Premio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class PremioResource extends Resource
{
    protected static ?string $model = Premio::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-gift';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nombre';
    protected static ?string $navigationLabel = 'Premios';
    protected static ?string $pluralModelLabel = 'Inventario de Premios';
    protected static ?string $modelLabel = 'Premio';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('sorteo_id')
                    ->label('Sorteo Asociado')
                    ->relationship('sorteo', 'nombre')
                    ->required(),
                TextInput::make('nombre')
                    ->label('Artículo / Premio')
                    ->required(),
                Select::make('tipo')
                    ->label('Categoría de Premio')
                    ->options([
                        'electrodomesticos' => 'Electrodomésticos',
                        'merch' => 'Merchandising',
                        'experiencia_de_marca' => 'Experiencia de Marca',
                    ])
                    ->required(),
                TextInput::make('cantidad_total')
                    ->label('Stock Total Inicial')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set) => $set('cantidad_disponible', $state)),
                TextInput::make('cantidad_disponible')
                    ->label('Stock Disponible Actual')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PremioInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sorteo.nombre')->label('Sorteo')->sortable(),
                TextColumn::make('nombre')->label('Premio')->searchable(),
                TextColumn::make('tipo')->label('Tipo')->badge()->color('info'),
                TextColumn::make('cantidad_total')->label('Masa Total')->sortable(),
                TextColumn::make('cantidad_disponible')
                    ->label('Disponible')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo')->label('Por Categoría')->options([
                    'electrodomesticos' => 'Electrodomésticos',
                    'merch' => 'Merchandising',
                    'experiencia_de_marca' => 'Experiencia de Marca',
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
            'index' => ListPremios::route('/'),
            'create' => CreatePremio::route('/create'),
            'view' => ViewPremio::route('/{record}'),
            'edit' => EditPremio::route('/{record}/edit'),
        ];
    }
}
