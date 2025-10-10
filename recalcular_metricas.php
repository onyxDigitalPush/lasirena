<?php
/**
 * Script para recalcular todas las métricas de proveedores
 * Ejecutar: php recalcular_metricas.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "RECALCULANDO MÉTRICAS DE PROVEEDORES\n";
echo "========================================\n\n";

// Obtener todos los años y meses únicos de incidencias
$periodos_incidencias = DB::table('incidencias_proveedores')
    ->select('id_proveedor', 'año', 'mes')
    ->distinct()
    ->get();

echo "Periodos de incidencias encontrados: " . $periodos_incidencias->count() . "\n";

// Obtener todos los años y meses únicos de devoluciones
$periodos_devoluciones = DB::table('devoluciones_proveedores')
    ->select('codigo_proveedor as id_proveedor', 'año', 'mes')
    ->distinct()
    ->get();

echo "Periodos de devoluciones encontrados: " . $periodos_devoluciones->count() . "\n\n";

// Combinar todos los periodos únicos
$todos_periodos = collect();
foreach ($periodos_incidencias as $p) {
    $todos_periodos->push(['id_proveedor' => $p->id_proveedor, 'año' => $p->año, 'mes' => $p->mes]);
}
foreach ($periodos_devoluciones as $p) {
    $todos_periodos->push(['id_proveedor' => $p->id_proveedor, 'año' => $p->año, 'mes' => $p->mes]);
}

// Eliminar duplicados
$todos_periodos = $todos_periodos->unique(function ($item) {
    return $item['id_proveedor'] . '-' . $item['año'] . '-' . $item['mes'];
});

echo "Total de periodos únicos a procesar: " . $todos_periodos->count() . "\n\n";

$procesados = 0;
$errores = 0;

foreach ($todos_periodos as $periodo) {
    try {
        $id_proveedor = $periodo['id_proveedor'];
        $año = $periodo['año'];
        $mes = $periodo['mes'];

        // Contar INCIDENCIAS por tipo (DEV1, ROK1, RET1)
        $metricas_incidencias = DB::table('incidencias_proveedores')
            ->where('id_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->select([
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "DEV1" THEN 1 ELSE 0 END) as dev1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "ROK1" THEN 1 ELSE 0 END) as rok1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RET1" THEN 1 ELSE 0 END) as ret1'),
            ])
            ->first();

        // Contar DEVOLUCIONES por tipo (RG1, RL1)
        $metricas_devoluciones = DB::table('devoluciones_proveedores')
            ->where('codigo_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->select([
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RG1" THEN 1 ELSE 0 END) as rg1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RL1" THEN 1 ELSE 0 END) as rl1'),
            ])
            ->first();

        // Actualizar o crear las métricas
        DB::table('proveedor_metrics')->updateOrInsert(
            [
                'proveedor_id' => $id_proveedor,
                'año' => $año,
                'mes' => $mes
            ],
            [
                'rg1' => $metricas_devoluciones->rg1 ?? 0,
                'rl1' => $metricas_devoluciones->rl1 ?? 0,
                'dev1' => $metricas_incidencias->dev1 ?? 0,
                'rok1' => $metricas_incidencias->rok1 ?? 0,
                'ret1' => $metricas_incidencias->ret1 ?? 0,
                'updated_at' => now(),
                'created_at' => now()
            ]
        );

        $procesados++;
        
        $rg1 = $metricas_devoluciones->rg1 ?? 0;
        $rl1 = $metricas_devoluciones->rl1 ?? 0;
        $dev1 = $metricas_incidencias->dev1 ?? 0;
        $rok1 = $metricas_incidencias->rok1 ?? 0;
        $ret1 = $metricas_incidencias->ret1 ?? 0;
        
        echo "✓ Proveedor $id_proveedor - $año/$mes => RG1=$rg1, RL1=$rl1, DEV1=$dev1, ROK1=$rok1, RET1=$ret1\n";

    } catch (\Exception $e) {
        $errores++;
        echo "✗ Error en proveedor {$periodo['id_proveedor']} - {$periodo['año']}/{$periodo['mes']}: " . $e->getMessage() . "\n";
    }
}

echo "\n========================================\n";
echo "RESUMEN\n";
echo "========================================\n";
echo "Total procesados: $procesados\n";
echo "Total errores: $errores\n";
echo "\n¡Proceso completado!\n";
