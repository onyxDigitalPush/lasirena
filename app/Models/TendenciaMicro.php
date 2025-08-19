<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TendenciaMicro extends Model
{
    use HasFactory;

    protected $table = 'tendencias_micro';

    protected $fillable = [
        'tienda_id',
        'proveedor_id',
        'fecha_toma_muestras',
        'anio',
        'mes',
        'semana',
        'codigo',
        'nombre',
        'provincia',
        'numero_muestra',
        'numero_factura',
        'codigo_producto',
        'nombre_producto',
        'codigo_proveedor',
        'nombre_proveedor',
        'te_proveedor',
        'lote',
        'tipo',
        'referencia',
        'aerobiotico_valor',
        'aerobiotico_resultado',
        'entero_valor',
        'entero_resultado',
        'ecoli_valor',
        'ecoli_resultado',
        's_valor',
        's_resultado',
        'salmonella_valor',
        'salmonella_resultado'
    ];

    protected $dates = [
        'fecha_toma_muestras'
    ];

    // Relación con la tienda
    public function tienda()
    {
        return $this->belongsTo('App\Models\Tienda', 'tienda_id');
    }

    // Relación con el proveedor
    public function proveedor()
    {
        return $this->belongsTo('App\Models\Proveedor', 'proveedor_id', 'id_proveedor');
    }
}
