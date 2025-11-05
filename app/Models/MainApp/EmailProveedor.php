<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Model;

class EmailProveedor extends Model
{
    protected $table = 'emails_proveedores';
    protected $primaryKey = 'id_email_proveedor';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_proveedor',
        'id_incidencia_proveedor',
        'id_devolucion_proveedor',
        'email_remitente',
        'emails_destinatarios',
        'emails_bcc',
        'asunto',
        'mensaje',
        'ruta_archivos',
        'enviado'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function incidencia()
    {
        return $this->belongsTo(IncidenciaProveedor::class, 'id_incidencia_proveedor', 'id');
    }

    public function devolucion()
    {
        return $this->belongsTo(DevolucionProveedor::class, 'id_devolucion_proveedor', 'id');
    }
}
