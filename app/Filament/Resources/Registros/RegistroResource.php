<?php

namespace App\Filament\Resources\Registros;

use App\Filament\Resources\Registros\Pages\CreateRegistro;
use App\Filament\Resources\Registros\Pages\EditRegistro;
use App\Filament\Resources\Registros\Pages\ListRegistros;
use App\Filament\Resources\Registros\Pages\ViewRegistro;
use App\Filament\Resources\Registros\Schemas\RegistroForm;
use App\Filament\Resources\Registros\Schemas\RegistroInfolist;
use App\Filament\Resources\Registros\Tables\RegistrosTable;
use App\Models\Registro;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\RegistroResource\Pages;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;

class RegistroResource extends Resource
{
    protected static ?string $model = Registro::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nombre';
    protected static ?string $navigationLabel = 'Participantes';
    protected static ?string $pluralModelLabel = 'Registros de Participantes';
    protected static ?string $modelLabel = 'Participación';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('cedula')->label('Cédula de Identidad')->disabled(),
                TextInput::make('nombre')->label('Nombre Completo')->disabled(),
                TextInput::make('telefono')->label('Teléfono de Contacto')->disabled(),
                TextInput::make('lugar_compra')->label('Establecimiento de Compra')->disabled(),
                TextInput::make('semana')->label('Semana de Participación')->disabled(),
                
                FileUpload::make('factura_imagen')
                    ->label('Evidencia de la Factura')
                    ->image()
                    ->directory('facturas')
                    ->disabled(),

                Select::make('estado')
                    ->label('Estatus de Validación')
                    ->options([
                        'pendiente' => 'Pendiente por Revisar',
                        'preseleccionado' => 'Preseleccionado — Validar Factura', 
                        'verificado' => 'Verificado / Válido',
                        'rechazado' => 'Rechazado / Fraude',
                    ])
                    ->required(),
                Select::make('premio_id')
                    ->label('Asignar Premio Obtenido')
                    ->relationship('premio', 'nombre')
                    ->disabled()
                    ->visible(fn (callable $get) => $get('ganador') === true),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RegistroInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cedula')->label('Cédula')->searchable()->sortable(),
                TextColumn::make('nombre')->label('Participante')->searchable(),
                TextColumn::make('lugar_compra')->label('Comercio'),
                
                // 📸 Visualización directa de la factura cargada desde el teléfono
                ImageColumn::make('factura_imagen')
                    ->label('Factura')
                    ->square()
                    ->disk('public'),

                TextColumn::make('semana')->label('Sem.')->sortable(),
                
                TextColumn::make('estado')
                    ->label('Estatus')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente'       => 'gray',
                        'preseleccionado' => 'warning',   // ← NUEVO: amarillo/naranja
                        'verificado'      => 'success',
                        'rechazado'       => 'danger',
                    }),

                TextColumn::make('ganador')
                    ->label('¿Ganó?')
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'GANADOR' : 'No'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')->options([
                    'pendiente' => 'Pendiente',
                    'preseleccionado' => 'Preseleccionado',
                    'verificado' => 'Verificado',
                    'rechazado' => 'Rechazado',
                ]),
                SelectFilter::make('semana')->label('Filtrar por Semana')->options([
                    1 => 'Semana 1',
                    2 => 'Semana 2',
                    3 => 'Semana 3',
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
            'index' => ListRegistros::route('/'),
            'create' => CreateRegistro::route('/create'),
            'view' => ViewRegistro::route('/{record}'),
            'edit' => EditRegistro::route('/{record}/edit'),
        ];
    }
}
