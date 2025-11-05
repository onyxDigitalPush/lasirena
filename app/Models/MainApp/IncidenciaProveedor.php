<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidenciaProveedor extends Model
{
    use HasFactory;

    protected $table = 'incidencias_proveedores';

    protected $fillable = [
        'id_proveedor',
        'nombre_proveedor',
        'año',
        'mes',
        'clasificacion_incidencia',
        'origen',
        'fecha_incidencia',
        'numero_inspeccion_sap',
        'resolucion_almacen',
        'cantidad_devuelta',
        'kg_un',
        'pedido_sap_devolucion',
        'resolucion_tienda',
        'retirada_tiendas',
        'cantidad_afectada',
        'descripcion_incidencia',
        'codigo',
        'producto',
        'lote_sirena',
        'lote_proveedor',
        'fcp',
        'informe_a_proveedor',
        'numero_informe',
        'fecha_envio_proveedor',
        'fecha_respuesta_proveedor',
        'informe_respuesta',
        'comentarios',
        'dias_respuesta_proveedor',
        'dias_sin_respuesta_informe',
        'tiempo_respuesta',
        'fecha_reclamacion_respuesta1',
        'fecha_reclamacion_respuesta2',
        'fecha_decision_destino_producto',
        'tipo_incidencia',
        'archivos',
        'estado'
    ];

    protected $dates = [
        'fecha_incidencia',
        'fcp',
        'fecha_envio_proveedor',
        'fecha_respuesta_proveedor',
        'fecha_reclamacion_respuesta1',
        'fecha_reclamacion_respuesta2',
        'fecha_decision_destino_producto'
    ];

    protected $casts = [
        'cantidad_devuelta' => 'decimal:2',
        'cantidad_afectada' => 'decimal:2',
        'archivos' => 'array',
        'año' => 'integer',
        'mes' => 'integer'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }
}
