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
        'telefono',
        'cp',
        'ciudad',
        'provincia',
    ];

    public function analiticas()
    {
        return $this->hasMany(\App\Models\Analitica::class, 'num_tienda', 'num_tienda');
    }
    
}
