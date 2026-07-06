<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Distribuidor extends Model
{
    use LogsActivity;
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('distribuidores');
    }

    public function scopePorEstatus(Builder $query, string $estatus): Builder
    {
        return $query->where('estatus_lead', $estatus);
    }
}
