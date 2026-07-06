<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LiberacionPremio extends Model
{
    use LogsActivity;
    protected $table = 'liberacion_premios';

    protected $fillable = [
        'liberacion_semanal_id',
        'premio_id',
        'cantidad',
        'cantidad_entregada',
        'cantidad_reservada',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cantidad', 'cantidad_entregada', 'cantidad_reservada'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('liberaciones_semanales');
    }

    // ═══ AGREGADO ═══════════════════════════════════════
    protected $appends = ['saldo_real'];

    public function getSaldoRealAttribute(): int
    {
        return $this->cantidad - $this->cantidad_entregada - $this->cantidad_reservada;
    }
    // ═════════════════════════════════════════════════════

    public function liberacionSemanal(): BelongsTo
    {
        return $this->belongsTo(LiberacionSemanal::class, 'liberacion_semanal_id');
    }

    public function premio(): BelongsTo
    {
        return $this->belongsTo(Premio::class, 'premio_id');
    }

    public function scopeConSaldoReal(Builder $query): Builder
    {
        return $query->whereRaw('cantidad - cantidad_entregada - cantidad_reservada > 0');
    }

    // El método original saldoReal() NO se toca
    public function saldoReal(): int
    {
        return $this->cantidad - $this->cantidad_entregada - $this->cantidad_reservada;
    }
}