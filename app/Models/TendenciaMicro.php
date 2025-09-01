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
        'analitica_id',
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
        'estado_analitica',
        'fecha_cambio_estado',
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

    protected $casts = [
        'fecha_cambio_estado' => 'datetime',
    ];

    // Constantes para los estados
    const ESTADO_SIN_INICIAR = 'sin_iniciar';
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_REALIZADA = 'realizada';

    // Método para verificar si está realizada
    public function isRealizada()
    {
        return $this->estado_analitica === self::ESTADO_REALIZADA;
    }

    // Método para cambiar estado y registrar fecha
    public function cambiarEstado($nuevoEstado)
    {
        $this->estado_analitica = $nuevoEstado;
        if ($nuevoEstado === self::ESTADO_REALIZADA) {
            $this->fecha_cambio_estado = now();
        }
        $this->save();
    }

    // Relación con la tienda
    public function tienda()
    {
        return $this->belongsTo('App\Models\Tienda', 'tienda_id');
    }

    // Relación con el proveedor
    public function proveedor()
    {
        return $this->belongsTo(\App\Models\MainApp\Proveedor::class, 'proveedor_id', 'id_proveedor');
    }
    // Relación con la analítica
    public function analitica()
    {
        return $this->belongsTo(\App\Models\Analitica::class);
    }
}
