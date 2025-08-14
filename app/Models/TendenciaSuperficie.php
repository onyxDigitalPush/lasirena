<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TendenciaSuperficie extends Model
{
    use HasFactory;

    protected $table = 'tendencias_superficie';

    protected $fillable = [
        'tienda_id', 'proveedor_id', 'fecha_muestra', 'anio', 'mes', 'semana',
        'codigo_centro', 'descripcion_centro', 'provincia', 'numero_muestras',
        'numero_factura', 'codigo_referencia', 'referencias',
        'aerobios_mesofilos_30c_valor','aerobios_mesofilos_30c_result',
        'enterobacterias_valor','enterobacterias_result',
        'listeria_monocytogenes_valor','listeria_monocytogenes_result',
        'accion_correctiva','repeticion_n1','repeticion_n2'
    ];

    public function tienda()
    {
        return $this->belongsTo(\App\Models\Tienda::class, 'tienda_id', 'id');
    }

    public function proveedor()
    {
        return $this->belongsTo(\App\Models\MainApp\Proveedor::class, 'proveedor_id', 'id_proveedor');
    }
}
