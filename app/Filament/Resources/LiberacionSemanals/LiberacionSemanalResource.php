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
use App\Models\Premio;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
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
                            ->required()
                            ->columnSpan(2), // Le damos más espacio al nombre del premio

                        TextInput::make('cantidad')
                        ->label('Cantidad a liberar')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(fn (callable $get) => static::stockDisponible($get) ?: 1)
                        ->hint(fn (callable $get) => $get('premio_id')
                            ? 'Stock : ' . static::stockDisponible($get)
                            : 'Selecciona un premio primero')
                        ->helperText('No puedes asignar más de lo que hay en inventario.'),

                        // 🌟 Nuevo Campo: Muestra el conteo en tiempo real de los ganadores de la Landing
                        TextInput::make('cantidad_entregada')
                            ->label('Cant. Entregada')
                            ->numeric()
                            ->disabled() // 🔒 Bloqueado: Solo lectura para control administrativo
                            ->dehydrated(false) // Evita enviar el campo vacío al actualizar
                            ->placeholder('0')
                            ->columnSpan(1),
                    ])
                    ->columns(4) // Cambiamos a 4 columnas para que quepan perfectamente alineados en una sola fila
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
            // 🛠️ Paso A: Cargamos la suma de las relaciones antes de pintar las columnas
            ->modifyQueryUsing(fn($query) => $query->withSum('liberacionPremios as total_liberados', 'cantidad')
                ->withSum('liberacionPremios as total_entregados', 'cantidad_entregada'))
            ->columns([
                TextColumn::make('sorteo.nombre')
                    ->label('Sorteo'),

                TextColumn::make('semana')
                    ->label('Semana de Campaña')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                // 🌟 Nueva Columna: Total de Premios Disponibles puestos en juego esta semana
                TextColumn::make('total_liberados')
                    ->label('Premios en Lote')
                    ->numeric()
                    ->alignCenter(),

                // 🌟 Nueva Columna: Cuántos de esos premios ya se llevó la gente
                TextColumn::make('total_entregados')
                    ->label('Entregados')
                    ->numeric()
                    ->alignCenter()
                    ->badge()
                    ->color(fn($state, $record) => $state >= $record->total_liberados ? 'success' : 'info'),

                TextColumn::make('fecha_liberacion')
                    ->label('Fecha del Despacho')
                    ->dateTime('d/m/Y H:i A'),

                TextColumn::make('notas')
                    ->label('Notas')
                    ->limit(30),
            ])
            ->defaultSort('semana', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('trasladarSobrantes')
                    ->label('Cerrar semana y trasladar sobrantes')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Trasladar premios sin ganar a la siguiente semana')
                    ->modalDescription('Esto moverá todo el saldo sin ganar de este lote hacia el lote de la semana siguiente. Si el lote de la semana siguiente no existe todavía, se creará automáticamente. Esta acción no se puede deshacer.')
                    ->action(function (LiberacionSemanal $record) {
                        $siguienteSemana = $record->semana + 1;

                        $siguienteLote = LiberacionSemanal::firstOrCreate(
                            ['sorteo_id' => $record->sorteo_id, 'semana' => $siguienteSemana],
                            [
                                'fecha_liberacion' => now(),
                                'notas'            => "Semana {$siguienteSemana} — incluye sobrantes trasladados de la semana {$record->semana}",
                            ]
                        );

                        $resumen = $record->trasladarSobrantesA($siguienteLote);

                        if (empty($resumen)) {
                            Notification::make()
                                ->title('Nada que trasladar')
                                ->body('Todos los premios de esta semana ya se agotaron o están reservados.')
                                ->warning()
                                ->send();
                            return;
                        }

                        Notification::make()
                            ->title('Sobrantes trasladados a la semana ' . $siguienteSemana)
                            ->body(collect($resumen)->map(fn($r) => "{$r['premio']}: +{$r['trasladado']}")->join(' | '))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    private static function stockDisponible(callable $get): int
    {
        return Premio::find($get('premio_id'))?->cantidad_disponible ?? 0;
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
