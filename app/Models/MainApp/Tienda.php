<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    use HasFactory;

    protected $fillable = [
        'num_tienda',
        'nombre_tienda',
        'direccion_tienda',
        'responsable',
        'email_responsable',
        'telefono',
    ];
}
