<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analitica extends Model
{
    use HasFactory;

    protected $fillable = [
        'num_tienda',
        'asesor_externo_nombre',
        'asesor_externo_empresa',
        'fecha_real_analitica',
        'fecha_realizacion',
        'periodicidad',
        'tipo_analitica',
        'proveedor_id',
    ];

    public function tienda()
    {
        return $this->belongsTo(\App\Models\Tienda::class, 'num_tienda', 'num_tienda');
    }

    public function proveedor()
    {
        return $this->belongsTo(\App\Models\MainApp\Proveedor::class, 'proveedor_id', 'id_proveedor');
    }

    // Relaciones con los resultados de analíticas
    public function tendenciaSuperficie()
    {
        return $this->hasOne(\App\Models\TendenciaSuperficie::class);
    }

    public function tendenciaMicro()
    {
        return $this->hasOne(\App\Models\TendenciaMicro::class);
    }
}
