<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiberacionPremio extends Model
{
    // 1. Le indicamos a Laravel el nombre exacto de la tabla en tu BD
    protected $table = 'liberacion_premios';

    // 2. Habilitamos los campos para escritura masiva
    protected $fillable = [
        'liberacion_semanal_id',
        'premio_id',
        'cantidad',
        'cantidad_entregada',
    ];

    // 3. Relación: Un detalle de liberación pertenece a una cabecera semanal
    public function liberacionSemanal(): BelongsTo
    {
        return $this->belongsTo(LiberacionSemanal::class, 'liberacion_semanal_id');
    }

    // 4. Relación: Un detalle de liberación pertenece a un premio del inventario
    public function premio(): BelongsTo
    {
        return $this->belongsTo(Premio::class, 'premio_id');
    }
}