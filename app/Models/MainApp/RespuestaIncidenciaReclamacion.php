<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Model;

class RespuestaIncidenciaReclamacion extends Model
{
    protected $table = 'respuesta_incidencia_devolucion';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_incidencia_proveedor',
        'id_devolucion_proveedor',
        'fecha_respuesta',
        'descripcion',
        'persona_contacto',
        'telefono',
        'email',
        'rutas_archivos',
    ];

    public function incidencia()
    {
        return $this->belongsTo(IncidenciaProveedor::class, 'id_incidencia_proveedor', 'id');
    }

    public function devolucion()
    {
        return $this->belongsTo(DevolucionProveedor::class, 'id_devolucion_proveedor', 'id');
    }
}
