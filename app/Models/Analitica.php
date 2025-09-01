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
        'estado_analitica',
        'fecha_cambio_estado',
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

    // Método para verificar si está pendiente
    public function isPendiente()
    {
        return $this->estado_analitica === self::ESTADO_PENDIENTE;
    }

    // Método para verificar si no se ha iniciado
    public function isSinIniciar()
    {
        return $this->estado_analitica === self::ESTADO_SIN_INICIAR;
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
