<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Premio extends Model
{
    protected $fillable = [
        'sorteo_id',
        'nombre',
        'tipo',
        'cantidad_total',
        'cantidad_disponible',
    ];

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
