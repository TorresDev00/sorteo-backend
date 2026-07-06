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
use Filament\Actions\ActionGroup;

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
                    ->required()
                    ->rules([
                        function (callable $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $sorteoId = $get('sorteo_id');
                                if (! $sorteoId) {
                                    $fail('Seleccioná un sorteo antes de asignar la semana.');
                                    return;
                                }

                                $existe = \App\Models\LiberacionSemanal::where('sorteo_id', $sorteoId)
                                    ->where('semana', $value)
                                    ->exists();

                                if ($existe) {
                                    $fail("La semana {$value} ya fue liberada para este sorteo. Elegí otro número de semana.");
                                }
                            };
                        },
                    ])
                    ->helperText('No podés repetir el mismo número de semana para el mismo sorteo.'),
                Textarea::make('notas')
                    ->label('Notas de esta Entrega')
                    ->columnSpanFull(),

                // 🌟 REPEATER: Relación dinámica con el detalle de premios semanal
                Repeater::make('liberacionPremios')
                    ->relationship('liberacionPremios')
                    ->label('Distribución de Premios del Lote')
                    ->schema([
                        Select::make('premio_id')
                            ->label('Premio')
                            ->options(function () {
                                return \App\Models\Premio::where('cantidad_disponible', '>', 0)
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id')
                                    ->map(fn($nombre, $id) => "{$nombre} (Stock: " . \App\Models\Premio::find($id)->cantidad_disponible . ")");
                            })
                            ->required()
                            ->reactive()
                            ->searchable(),

                        TextInput::make('cantidad')
                            ->label('Cantidad a liberar')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->rules([
                                function (callable $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $premioId = $get('premio_id');
                                        if (! $premioId) {
                                            $fail('Seleccioná un premio primero.');
                                            return;
                                        }

                                        $premio = \App\Models\Premio::find($premioId);
                                        if (! $premio) {
                                            $fail('El premio seleccionado no existe.');
                                            return;
                                        }

                                        $maximo = $premio->cantidad_disponible;

                                        if ($value > $maximo) {
                                            $fail("No hay suficiente stock. Stock disponible: {$maximo}. Intentaste asignar: {$value}.");
                                        }
                                    };
                                },
                            ])
                            ->helperText(function (callable $get): string {
                                $premioId = $get('premio_id');
                                if (! $premioId) return 'Seleccioná un premio para ver el stock disponible.';

                                $premio = \App\Models\Premio::find($premioId);
                                if (! $premio) return 'Premio no encontrado.';

                                return "Stock total: {$premio->cantidad_total} | Disponible: {$premio->cantidad_disponible}";
                            })
                            ->hint(function (callable $get): string {
                                $premioId = $get('premio_id');
                                if (! $premioId) return '';

                                $premio = \App\Models\Premio::find($premioId);
                                if (! $premio) return '';

                                $maximo = $premio->cantidad_disponible;
                                return "Máximo: {$maximo}";
                            }),

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
            ->modifyQueryUsing(fn($query) => $query
                ->withSum('liberacionPremios as total_liberados', 'cantidad')
                ->withSum('liberacionPremios as total_entregados', 'cantidad_entregada'))
            ->columns([
                TextColumn::make('sorteo.nombre')
                    ->label('Sorteo'),

                TextColumn::make('semana')
                    ->label('Semana de Campaña')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('total_liberados')
                    ->label('Premios en Lote')
                    ->numeric()
                    ->alignCenter(),

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
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Ver detalle'),

                    EditAction::make()
                        ->label('Editar lote'),

                    Action::make('trasladarSobrantes')
                        ->label('Cerrar semana y trasladar sobrantes')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->modalHeading('Trasladar premios sin ganar a la siguiente semana')
                        ->modalDescription('Esto moverá todo el saldo sin ganar de este lote hacia el lote de la semana siguiente. Si el lote de la semana siguiente no existe todavía, se creará automáticamente. Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, trasladar')
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

                            $resumenTexto = collect($resumen)->map(fn($r) => "{$r['premio']}: +{$r['trasladado']}")->join(', ');

                            activity('liberaciones_semanales')
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->log("Admin " . auth()->user()->name . " trasladó sobrantes de la semana {$record->semana} a la semana {$siguienteSemana}: {$resumenTexto}");

                            Notification::make()
                                ->title('Sobrantes trasladados a la semana ' . $siguienteSemana)
                                ->body(collect($resumen)->map(fn($r) => "{$r['premio']}: +{$r['trasladado']}")->join(' | '))
                                ->success()
                                ->send();
                        }),

                    Action::make('eliminarLote')
                        ->label('Eliminar lote')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->modalHeading('¿Eliminar este lote semanal?')
                        ->modalDescription(fn(LiberacionSemanal $record) => self::descripcionEliminar($record))
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->action(function (LiberacionSemanal $record) {
                            $pendientes = \App\Models\Registro::whereIn(
                                'liberacion_premio_id',
                                $record->liberacionPremios()->pluck('id')
                            )->where('estado', 'preseleccionado')->count();

                            if ($pendientes > 0) {
                                Notification::make()
                                    ->title('No se puede eliminar')
                                    ->body("Hay {$pendientes} registro(s) preseleccionados esperando validación en este lote. Resuélvelos primero (verifícalos o recházalos) antes de eliminar el lote.")
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $semana = $record->semana;
                            $sorteoNombre = $record->sorteo?->nombre ?? 'sorteo desconocido';
                            $adminNombre = auth()->user()->name;

                            $record->delete();

                            activity('liberaciones_semanales')
                                ->causedBy(auth()->user())
                                ->withProperties(['semana' => $semana, 'sorteo' => $sorteoNombre])
                                ->log("Admin {$adminNombre} eliminó el lote de la semana {$semana} ({$sorteoNombre})");

                            Notification::make()
                                ->title('Lote eliminado')
                                ->success()
                                ->send();
                        }),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            ->filters([
                //
            ]);
    }

    private static function descripcionEliminar(LiberacionSemanal $record): string
    {
        $totalRegistros = \App\Models\Registro::whereIn(
            'liberacion_premio_id',
            $record->liberacionPremios()->pluck('id')
        )->count();

        if ($totalRegistros === 0) {
            return 'Esta acción eliminará el lote y todos sus premios asociados. No se puede deshacer.';
        }

        return "Este lote tiene {$totalRegistros} registro(s) de participantes vinculados. Al eliminarlo, esos registros conservarán su historial pero perderán la referencia a su premio. Esta acción no se puede deshacer.";
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
