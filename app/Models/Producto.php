<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    // Esto le permite a Filament hacer inserciones masivas en estos campos
    protected $fillable = [
        'nombre',
        'codigo_qr',
        'descripcion',
    ];
}