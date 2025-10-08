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
        'periodicidad_no_procede',
        'tipo_analitica',
        'detalle_tipo',
        'producto',
        'donde_recoge_muestra',
        'donde_se_recoje_muestra',
        'referencia_superficie',
        'codigo_producto',
        'descripcion_producto',
        'procede',
        'proveedor_id',
        'proveedor_no_procede',
        'estado_analitica',
        'fecha_cambio_estado',
        'archivos',
        'numero_factura',
        // Nuevos campos de resultados microbiológicos
        'E_coli_valor',
        'E_coli_resultado',
        'coliformes_totales_valor',
        'coliformes_totales_resultado',
        'enterococos_valor',
        'enterococos_resultado',
        'amonio_valor',
        'amonio_resultado',
        'nitritos_valor',
        'nitritos_resultado',
        'color_valor',
        'color_resultado',
        'sabor_valor',
        'sabor_resultado',
        'olor_valor',
        'olor_resultado',
        'conductividad_valor',
        'conductividad_resultado',
        'ph_valor',
        'ph_resultado',
        'turbidez_valor',
        'turbidez_resultado',
        'cloro_libre_valor',
        'cloro_libre_resultado',
        'cloro_combinado_valor',
        'cloro_combinado_resultado',
        'cloro_total_valor',
        'cloro_total_resultado',
        'cobre_valor',
        'cobre_resultado',
        'cromo_total_valor',
        'cromo_total_resultado',
        'niquel_valor',
        'niquel_resultado',
        'hierro_valor',
        'hierro_resultado',
        'cloruro_vinilo_valor',
        'cloruro_vinilo_resultado',
        'bisfenol_valor',
        'bisfenol_resultado',
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

    // Métodos para manejar archivos
    public function getArchivosArray()
    {
        if (is_null($this->archivos)) {
            return [];
        }
        
        if (is_string($this->archivos)) {
            $decoded = json_decode($this->archivos, true);
            return is_array($decoded) ? $decoded : [];
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
