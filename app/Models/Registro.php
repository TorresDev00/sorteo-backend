<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    public function premio()
    {
        return $this->belongsTo(Premio::class);
    }
    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class);
    }
}
