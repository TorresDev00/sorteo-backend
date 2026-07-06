<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Registro extends Model
{
    use LogsActivity;
    protected $fillable = [
        'sorteo_id',
        'cedula',
        'nombre',
        'telefono',
        'direccion',
        'lugar_compra',
        'factura_imagen',
        'semana',
        'estado',
        'ganador',
        'premio_id',
        'liberacion_premio_id',
        'fecha_registro',
    ];

    protected $casts = [
        'ganador'        => 'boolean',
        'semana'         => 'integer',
        'fecha_registro' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cedula', 'nombre', 'telefono', 'estado', 'semana', 'ganador', 'premio_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('registros');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return match ($eventName) {
            'created' => "Nuevo registro de {$this->nombre} (Cédula: {$this->cedula}) — estado: {$this->estado}",
            'updated' => $this->wasChanged('estado')
                ? "Registro de {$this->nombre} cambió estado a '{$this->estado}'"
                : "Registro de {$this->nombre} actualizado",
            'deleted' => "Registro de {$this->nombre} eliminado (estaba en estado: {$this->estado})",
            default   => $eventName,
        };
    }

    public function sorteo(): BelongsTo
    {
        return $this->belongsTo(Sorteo::class);
    }

    public function premio(): BelongsTo
    {
        return $this->belongsTo(Premio::class);
    }

    public function liberacionPremio(): BelongsTo
    {
        return $this->belongsTo(LiberacionPremio::class);
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePreseleccionados(Builder $query): Builder
    {
        return $query->where('estado', 'preseleccionado');
    }

    public function scopeVerificados(Builder $query): Builder
    {
        return $query->where('estado', 'verificado');
    }

    public function scopeRechazados(Builder $query): Builder
    {
        return $query->where('estado', 'rechazado');
    }

    public function scopeGanadores(Builder $query): Builder
    {
        return $query->where('ganador', true);
    }

    /**
     * Registros que ya pasaron por el algoritmo esta semana (ganadores + no-ganadores).
     * NO filtrar por ganador=false: 'preseleccionado' siempre tiene ganador=true y debe
     * contarse aquí para que el cálculo de cupos disponibles sea correcto.
     */
    public function scopeSinEvaluar(Builder $query): Builder
    {
        return $query->whereIn('estado', ['pendiente', 'preseleccionado']);
    }
}
