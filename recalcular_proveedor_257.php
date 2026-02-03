<?php

/**
 * Script para recalcular métricas del proveedor 257
 * Ejecutar: php recalcular_proveedor_257.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$id_proveedor = 257;
$año = 2025;
$mes = 10; // Octubre

echo "=== RECALCULANDO MÉTRICAS: Proveedor {$id_proveedor} - {$año}/{$mes} ===" . PHP_EOL;
echo PHP_EOL;

// Verificar devoluciones en la base de datos
echo "1. Consultando devoluciones_proveedores..." . PHP_EOL;
$devoluciones = DB::table('devoluciones_proveedores')
    ->where('codigo_proveedor', $id_proveedor)
    ->where('año', $año)
    ->where('mes', $mes)
    ->get(['id', 'clasificacion_incidencia', 'descripcion_producto', 'descripcion_motivo']);

echo "   Total registros encontrados: " . $devoluciones->count() . PHP_EOL;
foreach ($devoluciones as $dev) {
    echo "   - ID {$dev->id}: {$dev->clasificacion_incidencia} - {$dev->descripcion_producto} - {$dev->descripcion_motivo}" . PHP_EOL;
}
echo PHP_EOL;

// Contar por clasificación en devoluciones_proveedores
echo "2. Contando incidencias en devoluciones_proveedores..." . PHP_EOL;
$metricas_devoluciones = DB::table('devoluciones_proveedores')
    ->where('codigo_proveedor', $id_proveedor)
    ->where('año', $año)
    ->where('mes', $mes)
    ->select([
        DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RG1" THEN 1 ELSE 0 END) as rg1'),
        DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RL1" THEN 1 ELSE 0 END) as rl1'),
        DB::raw('SUM(CASE WHEN clasificacion_incidencia = "DEV1" THEN 1 ELSE 0 END) as dev1'),
        DB::raw('SUM(CASE WHEN clasificacion_incidencia = "ROK1" THEN 1 ELSE 0 END) as rok1'),
        DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RET1" THEN 1 ELSE 0 END) as ret1'),
    ])
    ->first();

echo "   RG1:  " . ($metricas_devoluciones->rg1 ?? 0) . PHP_EOL;
echo "   RL1:  " . ($metricas_devoluciones->rl1 ?? 0) . PHP_EOL;
echo "   DEV1: " . ($metricas_devoluciones->dev1 ?? 0) . PHP_EOL;
echo "   ROK1: " . ($metricas_devoluciones->rok1 ?? 0) . PHP_EOL;
echo "   RET1: " . ($metricas_devoluciones->ret1 ?? 0) . PHP_EOL;
echo PHP_EOL;

// Contar por clasificación en incidencias_proveedores
echo "3. Contando incidencias en incidencias_proveedores..." . PHP_EOL;
$metricas_incidencias = DB::table('incidencias_proveedores')
    ->where('id_proveedor', $id_proveedor)
    ->where('año', $año)
    ->where('mes', $mes)
    ->select([
        DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RG1" THEN 1 ELSE 0 END) as rg1'),
        DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RL1" THEN 1 ELSE 0 END) as rl1'),
        DB::raw('SUM(CASE WHEN clasificacion_incidencia = "DEV1" THEN 1 ELSE 0 END) as dev1'),
        DB::raw('SUM(CASE WHEN clasificacion_incidencia = "ROK1" THEN 1 ELSE 0 END) as rok1'),
        DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RET1" THEN 1 ELSE 0 END) as ret1'),
    ])
    ->first();

echo "   RG1:  " . ($metricas_incidencias->rg1 ?? 0) . PHP_EOL;
echo "   RL1:  " . ($metricas_incidencias->rl1 ?? 0) . PHP_EOL;
echo "   DEV1: " . ($metricas_incidencias->dev1 ?? 0) . PHP_EOL;
echo "   ROK1: " . ($metricas_incidencias->rok1 ?? 0) . PHP_EOL;
echo "   RET1: " . ($metricas_incidencias->ret1 ?? 0) . PHP_EOL;
echo PHP_EOL;

// Calcular totales sumando ambas tablas
$rg1_total = ($metricas_devoluciones->rg1 ?? 0) + ($metricas_incidencias->rg1 ?? 0);
$rl1_total = ($metricas_devoluciones->rl1 ?? 0) + ($metricas_incidencias->rl1 ?? 0);
$dev1_total = ($metricas_devoluciones->dev1 ?? 0) + ($metricas_incidencias->dev1 ?? 0);
$rok1_total = ($metricas_devoluciones->rok1 ?? 0) + ($metricas_incidencias->rok1 ?? 0);
$ret1_total = ($metricas_devoluciones->ret1 ?? 0) + ($metricas_incidencias->ret1 ?? 0);

echo "4. TOTALES (incidencias + devoluciones):" . PHP_EOL;
echo "   RG1:  {$rg1_total}" . PHP_EOL;
echo "   RL1:  {$rl1_total}" . PHP_EOL;
echo "   DEV1: {$dev1_total}" . PHP_EOL;
echo "   ROK1: {$rok1_total}" . PHP_EOL;
echo "   RET1: {$ret1_total}" . PHP_EOL;
echo PHP_EOL;

// Actualizar tabla gp_ls_proveedor_metrics
echo "5. Actualizando gp_ls_proveedor_metrics..." . PHP_EOL;
DB::table('proveedor_metrics')->updateOrInsert(
    [
        'proveedor_id' => $id_proveedor,
        'año' => $año,
        'mes' => $mes
    ],
    [
        'rg1' => $rg1_total,
        'rl1' => $rl1_total,
        'dev1' => $dev1_total,
        'rok1' => $rok1_total,
        'ret1' => $ret1_total,
        'updated_at' => now()
    ]
);

echo "   ✓ Métricas actualizadas correctamente" . PHP_EOL;
echo PHP_EOL;

// Verificar resultado final
echo "6. Verificando resultado en gp_ls_proveedor_metrics..." . PHP_EOL;
$metrica_final = DB::table('proveedor_metrics')
    ->where('proveedor_id', $id_proveedor)
    ->where('año', $año)
    ->where('mes', $mes)
    ->first();

if ($metrica_final) {
    echo "   RG1:  " . $metrica_final->rg1 . PHP_EOL;
    echo "   RL1:  " . $metrica_final->rl1 . PHP_EOL;
    echo "   DEV1: " . $metrica_final->dev1 . PHP_EOL;
    echo "   ROK1: " . $metrica_final->rok1 . PHP_EOL;
    echo "   RET1: " . $metrica_final->ret1 . PHP_EOL;
    echo PHP_EOL;
    echo "✅ PROCESO COMPLETADO" . PHP_EOL;
} else {
    echo "   ❌ ERROR: No se encontró el registro en gp_ls_proveedor_metrics" . PHP_EOL;
}
