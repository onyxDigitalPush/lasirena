<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EstadoIncidenciaReclamacion extends Model
{
    protected $table = 'estado_incidencia_reclamacion';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_incidencia_proveedor',
        'id_devolucion_proveedor',
        'id_user',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
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
