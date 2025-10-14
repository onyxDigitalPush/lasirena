<?php
/**
 * Script para recalcular TODAS las métricas de proveedores
 * Ejecutar: php recalcular_metricas_completo.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\MainApp\ProveedorMetric;

echo "🔄 Recalculando TODAS las métricas de proveedores...\n\n";

// Primero, borrar todas las métricas existentes
echo "📋 Limpiando métricas antiguas...\n";
DB::table('proveedor_metrics')->truncate();
echo "✅ Métricas antiguas eliminadas\n\n";

// Obtener todos los períodos únicos de incidencias
echo "📊 Obteniendo períodos de incidencias...\n";
$periodos_incidencias = DB::table('incidencias_proveedores')
    ->select('id_proveedor as proveedor_id', 'año', 'mes')
    ->distinct()
    ->get();

echo "   Encontrados: " . $periodos_incidencias->count() . " períodos en incidencias\n";

// Obtener todos los períodos únicos de devoluciones
echo "📊 Obteniendo períodos de devoluciones...\n";
$periodos_devoluciones = DB::table('devoluciones_proveedores')
    ->select(DB::raw('CAST(codigo_proveedor AS UNSIGNED) as proveedor_id'), 'año', 'mes')
    ->whereRaw('codigo_proveedor REGEXP "^[0-9]+$"')
    ->distinct()
    ->get();

echo "   Encontrados: " . $periodos_devoluciones->count() . " períodos en devoluciones\n\n";

// Combinar todos los períodos únicos
$todos_periodos = collect();
foreach ($periodos_incidencias as $periodo) {
    $todos_periodos->push([
        'proveedor_id' => $periodo->proveedor_id,
        'año' => $periodo->año,
        'mes' => $periodo->mes
    ]);
}

foreach ($periodos_devoluciones as $periodo) {
    $todos_periodos->push([
        'proveedor_id' => $periodo->proveedor_id,
        'año' => $periodo->año,
        'mes' => $periodo->mes
    ]);
}

// Eliminar duplicados
$periodos_unicos = $todos_periodos->unique(function ($item) {
    return $item['proveedor_id'] . '-' . $item['año'] . '-' . $item['mes'];
});

$total = $periodos_unicos->count();
echo "🎯 Total de períodos únicos a procesar: $total\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$procesados = 0;
$errores = 0;

foreach ($periodos_unicos as $periodo) {
    $proveedor_id = $periodo['proveedor_id'];
    $año = $periodo['año'];
    $mes = $periodo['mes'];
    
    try {
        // Contar INCIDENCIAS por tipo
        $metricas_incidencias = DB::table('incidencias_proveedores')
            ->where('id_proveedor', $proveedor_id)
            ->where('año', $año)
            ->where('mes', $mes)
            ->select([
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "DEV1" THEN 1 ELSE 0 END) as dev1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "ROK1" THEN 1 ELSE 0 END) as rok1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RET1" THEN 1 ELSE 0 END) as ret1'),
            ])
            ->first();
        
        // Contar DEVOLUCIONES por tipo
        $metricas_devoluciones = DB::table('devoluciones_proveedores')
            ->where('codigo_proveedor', $proveedor_id)
            ->where('año', $año)
            ->where('mes', $mes)
            ->select([
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RG1" THEN 1 ELSE 0 END) as rg1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RL1" THEN 1 ELSE 0 END) as rl1'),
            ])
            ->first();
        
        // Crear las métricas
        $metrica = ProveedorMetric::create([
            'proveedor_id' => $proveedor_id,
            'año' => $año,
            'mes' => $mes,
            'rg1' => $metricas_devoluciones->rg1 ?? 0,
            'rl1' => $metricas_devoluciones->rl1 ?? 0,
            'dev1' => $metricas_incidencias->dev1 ?? 0,
            'rok1' => $metricas_incidencias->rok1 ?? 0,
            'ret1' => $metricas_incidencias->ret1 ?? 0,
        ]);
        
        $procesados++;
        
        // Mostrar progreso cada 10 registros
        if ($procesados % 10 == 0 || $procesados == $total) {
            $porcentaje = round(($procesados / $total) * 100, 1);
            echo "⏳ Progreso: $procesados / $total ($porcentaje%)\n";
        }
        
        // Mostrar detalles si hay métricas
        $suma = ($metrica->rg1 + $metrica->rl1 + $metrica->dev1 + $metrica->rok1 + $metrica->ret1);
        if ($suma > 0) {
            echo "   ✓ Proveedor $proveedor_id - $año/$mes: ";
            echo "RG1=$metrica->rg1, RL1=$metrica->rl1, DEV1=$metrica->dev1, ROK1=$metrica->rok1, RET1=$metrica->ret1\n";
        }
        
    } catch (\Exception $e) {
        $errores++;
        echo "   ❌ Error en proveedor $proveedor_id - $año/$mes: " . $e->getMessage() . "\n";
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Recalculación completada!\n";
echo "📊 Estadísticas:\n";
echo "   • Total procesados: $procesados\n";
echo "   • Errores: $errores\n";
echo "   • Éxito: " . ($procesados - $errores) . "\n";

// Mostrar resumen del proveedor 1422 específicamente
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 RESUMEN PROVEEDOR 1422:\n\n";

$metricas_1422 = DB::table('proveedor_metrics')
    ->where('proveedor_id', 1422)
    ->orderBy('año', 'DESC')
    ->orderBy('mes', 'DESC')
    ->get();

if ($metricas_1422->count() > 0) {
    echo "Período          | RG1 | RL1 | DEV1 | ROK1 | RET1\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    foreach ($metricas_1422 as $metrica) {
        $mes_nombre = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ][$metrica->mes] ?? $metrica->mes;
        
        printf("%-15s | %3d | %3d | %4d | %4d | %4d\n",
            "$mes_nombre $metrica->año",
            $metrica->rg1,
            $metrica->rl1,
            $metrica->dev1,
            $metrica->rok1,
            $metrica->ret1
        );
    }
    
    // Totales del año 2025
    $totales_2025 = DB::table('proveedor_metrics')
        ->where('proveedor_id', 1422)
        ->where('año', 2025)
        ->select([
            DB::raw('SUM(rg1) as total_rg1'),
            DB::raw('SUM(rl1) as total_rl1'),
            DB::raw('SUM(dev1) as total_dev1'),
            DB::raw('SUM(rok1) as total_rok1'),
            DB::raw('SUM(ret1) as total_ret1'),
        ])
        ->first();
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    printf("%-15s | %3d | %3d | %4d | %4d | %4d\n",
        "TOTAL 2025",
        $totales_2025->total_rg1,
        $totales_2025->total_rl1,
        $totales_2025->total_dev1,
        $totales_2025->total_rok1,
        $totales_2025->total_ret1
    );
} else {
    echo "⚠️  No se encontraron métricas para el proveedor 1422\n";
}

echo "\n✅ ¡Proceso completado! Recarga la página en el navegador.\n";
