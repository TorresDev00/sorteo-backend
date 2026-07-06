<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Premio extends Model
{
    use LogsActivity;
    protected $fillable = [
        'sorteo_id',
        'nombre',
        'tipo',
        'cantidad_total',
        'cantidad_disponible',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('premios');
    }

    public function sorteo(): BelongsTo
    {
        return $this->belongsTo(Sorteo::class);
    }

    public function liberacionPremios(): HasMany
    {
        return $this->hasMany(LiberacionPremio::class);
    }

    public function registros(): HasMany
    {
        return $this->hasMany(Registro::class);
    }
}
