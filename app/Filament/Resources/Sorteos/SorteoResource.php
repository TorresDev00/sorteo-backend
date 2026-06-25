<?php

namespace App\Filament\Resources\Sorteos;

use App\Filament\Resources\Sorteos\Pages\CreateSorteo;
use App\Filament\Resources\Sorteos\Pages\EditSorteo;
use App\Filament\Resources\Sorteos\Pages\ListSorteos;
use App\Filament\Resources\Sorteos\Pages\ViewSorteo;
use App\Filament\Resources\Sorteos\Schemas\SorteoForm;
use App\Filament\Resources\Sorteos\Schemas\SorteoInfolist;
use App\Filament\Resources\Sorteos\Tables\SorteosTable;
use App\Models\Sorteo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class SorteoResource extends Resource
{
    protected static ?string $model = Sorteo::class;
    protected static ?string $navigationLabel = 'Sorteos';
    protected static ?int $navigationSort = 1;
    protected static ?string $pluralModelLabel = 'Sorteos';
    protected static ?string $modelLabel = 'Sorteo';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('nombre')
                    ->label('Nombre de la Campaña')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->required(),
                DatePicker::make('fecha_fin')
                    ->label('Fecha de Fin')
                    ->required(),
                Toggle::make('activo')
                    ->label('Campaña Activa')
                    ->default(true),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SorteoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Campaña')->searchable()->sortable(),
                TextColumn::make('fecha_inicio')->label('Inicio')->date('d/m/Y')->sortable(),
                TextColumn::make('fecha_fin')->label('Fin')->date('d/m/Y')->sortable(),
                IconColumn::make('activo')
                    ->label('Estatus')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([]);
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
            'index' => ListSorteos::route('/'),
            'create' => CreateSorteo::route('/create'),
            'view' => ViewSorteo::route('/{record}'),
            'edit' => EditSorteo::route('/{record}/edit'),
        ];
    }
}
