<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Premio extends Model
{
    protected $fillable = [
        'sorteo_id',
        'nombre',
        'tipo',
        'cantidad_total',
        'cantidad_disponible',
    ];
    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class);
    }
}
