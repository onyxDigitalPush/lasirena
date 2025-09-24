<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TendenciaSuperficie extends Model
{
    use HasFactory;

    protected $table = 'tendencias_superficie';

    protected $fillable = [
        'tienda_id', 'analitica_id', 'proveedor_id', 'fecha_muestra', 'anio', 'mes', 'semana',
        'codigo_centro', 'descripcion_centro', 'provincia', 'numero_muestras',
        'numero_factura', 'codigo_referencia', 'referencias',
        'aerobios_mesofilos_30c_valor','aerobios_mesofilos_30c_result',
        'enterobacterias_valor','enterobacterias_result',
        'listeria_monocytogenes_valor','listeria_monocytogenes_result',
        'accion_correctiva','repeticion_n1','repeticion_n2',
        'estado_analitica', 'fecha_cambio_estado', 'archivos', 'procede'
    ];

    protected $casts = [
        'fecha_cambio_estado' => 'datetime',
        'archivos' => 'array',
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

    public function tienda()
    {
        return $this->belongsTo(\App\Models\Tienda::class, 'tienda_id', 'id');
    }

    public function proveedor()
    {
        return $this->belongsTo(\App\Models\MainApp\Proveedor::class, 'proveedor_id', 'id_proveedor');
    }

    public function analitica()
    {
        return $this->belongsTo(\App\Models\Analitica::class);
    }

    // Método para obtener archivos como array
    public function getArchivosArray()
    {
        if (is_null($this->archivos)) {
            return [];
        }
        
        return is_array($this->archivos) ? $this->archivos : [];
    }

    public function addArchivo($archivo)
    {
        $archivos = $this->getArchivosArray();
        
        // Filtrar arrays vacíos antes de agregar
        $archivos = array_filter($archivos, function($item) {
            return !empty($item) && is_array($item);
        });
        
        $archivos[] = $archivo;
        $this->archivos = array_values($archivos); // Reindexar
        return $this;
    }

    public function removeArchivo($nombreArchivo)
    {
        $archivos = $this->getArchivosArray();
        $archivos = array_filter($archivos, function($archivo) use ($nombreArchivo) {
            return is_array($archivo) && isset($archivo['nombre']) && $archivo['nombre'] !== $nombreArchivo;
        });
        $this->archivos = array_values($archivos);
        return $this;
    }

    public function hasArchivos()
    {
        $archivos = $this->getArchivosArray();
        return !empty($archivos);
    }
}
