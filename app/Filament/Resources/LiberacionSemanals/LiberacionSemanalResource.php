<?php

namespace App\Filament\Resources\LiberacionSemanals;

use App\Filament\Resources\LiberacionSemanals\Pages\CreateLiberacionSemanal;
use App\Filament\Resources\LiberacionSemanals\Pages\EditLiberacionSemanal;
use App\Filament\Resources\LiberacionSemanals\Pages\ListLiberacionSemanals;
use App\Filament\Resources\LiberacionSemanals\Pages\ViewLiberacionSemanal;
use App\Filament\Resources\LiberacionSemanals\Schemas\LiberacionSemanalForm;
use App\Filament\Resources\LiberacionSemanals\Schemas\LiberacionSemanalInfolist;
use App\Filament\Resources\LiberacionSemanals\Tables\LiberacionSemanalsTable;
use App\Models\LiberacionSemanal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;

class LiberacionSemanalResource extends Resource
{
    protected static ?string $model = LiberacionSemanal::class;
    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $recordTitleAttribute = 'semana';
    protected static ?string $navigationLabel = 'Lotes Semanales';
    protected static ?string $pluralModelLabel = 'Liberaciones Semanales';
    protected static ?string $modelLabel = 'Liberación Semanal';

    public static function form(Schema $form): Schema
    {
       return $form
            ->schema([
                Select::make('sorteo_id')
                    ->label('Sorteo Activo')
                    ->relationship('sorteo', 'nombre')
                    ->required(),
                TextInput::make('semana')
                    ->label('Número de Semana (Ej: 1)')
                    ->numeric()
                    ->required(),
                Textarea::make('notas')
                    ->label('Notas de esta Entrega')
                    ->columnSpanFull(),

                // 🌟 REPEATER: Relación dinámica con el detalle de premios semanal
                Repeater::make('liberacionPremios')
                    ->relationship('liberacionPremios')
                    ->label('Distribución de Premios del Lote')
                    ->schema([
                        Select::make('premio_id')
                            ->label('Seleccionar Premio del Inventario')
                            ->relationship('premio', 'nombre')
                            ->required(),
                        TextInput::make('cantidad')
                            ->label('Cantidad a Liberar esta semana')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LiberacionSemanalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sorteo.nombre')->label('Sorteo'),
                TextColumn::make('semana')
                    ->label('Semana de Campaña')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('fecha_liberacion')
                    ->label('Fecha del Despacho')
                    ->dateTime('d/m/Y H:i A'),
                TextColumn::make('notas')->label('Notas'),
            ])
            ->defaultSort('semana', 'desc');
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
            'index' => ListLiberacionSemanals::route('/'),
            'create' => CreateLiberacionSemanal::route('/create'),
            'view' => ViewLiberacionSemanal::route('/{record}'),
            'edit' => EditLiberacionSemanal::route('/{record}/edit'),
        ];
    }
}
