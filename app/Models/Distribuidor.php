<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Distribuidor extends Model
{
    protected $table = 'distribuidores';

    protected $fillable = [
        'nombre_comercial',
        'email',
        'telefono',
        'estado_ubicacion',
        'mensaje',
        'estatus_lead',
        'notas_administrador',
    ];

    public function scopePorEstatus(Builder $query, string $estatus): Builder
    {
        return $query->where('estatus_lead', $estatus);
    }
}
