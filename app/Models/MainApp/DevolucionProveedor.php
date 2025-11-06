<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevolucionProveedor extends Model
{
    use HasFactory;

    protected $table = 'devoluciones_proveedores';

    protected $fillable = [
        'codigo_producto',
        'nombre_proveedor',
        'codigo_proveedor',
        'descripcion_producto',
        'fecha_inicio',
        'fecha_fin',
        'np',
        'año',
        'mes',
        'fecha_reclamacion',
        'clasificacion_incidencia',
        'top100fy2',
        'descripcion_motivo',
        'especificacion_motivo_reclamacion_leve',
        'especificacion_motivo_reclamacion_grave',
        'recuperamos_objeto_extraño',
        'descripcion_queja',
        'nombre_tienda',
        'no_queja',
        'origen',
        'lote_sirena',
        'lote_proveedor',
        'informe_a_proveedor',
        'informe',
        'fecha_envio_proveedor',
        'fecha_respuesta_proveedor',
        'tiempo_respuesta',
        'informe_respuesta',
        'tipo_reclamacion',
        'tipo_reclamacion_grave',
        'comentarios',
        'fecha_reclamacion_respuesta',
        'abierto',
        'archivos',
        'archivos_informe'
    ];

    protected $dates = [
        'fecha_inicio',
        'fecha_fin',
        'fecha_reclamacion',
        'fecha_envio_proveedor',
        'fecha_respuesta_proveedor',
        'fecha_reclamacion_respuesta'
    ];

    protected $casts = [
        'año' => 'integer',
        'mes' => 'integer',
        'archivos' => 'array',
        'archivos_informe' => 'array'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'codigo_proveedor', 'id_proveedor');
    }
}
