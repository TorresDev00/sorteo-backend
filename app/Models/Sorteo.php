<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sorteo extends Model
{
    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'activo'       => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function premios(): HasMany
    {
        return $this->hasMany(Premio::class);
    }

    public function liberacionesSemanales(): HasMany
    {
        return $this->hasMany(LiberacionSemanal::class);
    }

    public function registros(): HasMany
    {
        return $this->hasMany(Registro::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
