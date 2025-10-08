<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultadoAgua extends Model
{
    protected $table = 'resultados_agua';
    
    protected $fillable = [
        'analitica_id',
        'tienda_id',
        'fecha_muestra',
        'donde_se_recoje_muestra',
        'numero_muestras',
        'numero_factura',
        'producto',
        'estado_analitica',
        // Resultados microbiológicos
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
        'archivos',
    ];

    protected $casts = [
        'archivos' => 'array',
        'fecha_muestra' => 'date',
    ];

    /**
     * Relación con la analítica principal
     */
    public function analitica()
    {
        return $this->belongsTo(Analitica::class, 'analitica_id');
    }

    /**
     * Relación con la tienda
     */
    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda_id');
    }

    /**
     * Obtener el array de archivos, asegurando que sea siempre un array
     */
    public function getArchivosArray()
    {
        if (empty($this->archivos)) {
            return [];
        }
        
        if (is_array($this->archivos)) {
            return $this->archivos;
        }
        
        if (is_string($this->archivos)) {
            $decoded = json_decode($this->archivos, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return [];
    }
}
