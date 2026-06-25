<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiberacionSemanal extends Model
{
    protected $table = 'liberaciones_semanales';

    protected $guarded = [];

    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class);
    }
    public function liberacionPremios()
    {
        return $this->hasMany(LiberacionPremio::class);
    }
}
