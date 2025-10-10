<?php
/**
 * Script de depuración para verificar métricas del proveedor 45
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "DIAGNÓSTICO PROVEEDOR 45\n";
echo "==========================================\n\n";

$proveedor_id = 45;

// Verificar devoluciones
echo "--- DEVOLUCIONES (tabla devoluciones_proveedores) ---\n";
$devoluciones = DB::table('devoluciones_proveedores')
    ->where('codigo_proveedor', $proveedor_id)
    ->select('id', 'codigo_proveedor', 'clasificacion_incidencia', 'año', 'mes', 'nombre_proveedor')
    ->get();

if ($devoluciones->count() > 0) {
    foreach ($devoluciones as $dev) {
        echo "  ID: {$dev->id} | Código: {$dev->codigo_proveedor} | Clasificación: {$dev->clasificacion_incidencia} | {$dev->año}/{$dev->mes}\n";
    }
} else {
    echo "  ❌ No hay devoluciones para este proveedor\n";
}

// Verificar incidencias
echo "\n--- INCIDENCIAS (tabla incidencias_proveedores) ---\n";
$incidencias = DB::table('incidencias_proveedores')
    ->where('id_proveedor', $proveedor_id)
    ->select('id', 'id_proveedor', 'clasificacion_incidencia', 'año', 'mes', 'nombre_proveedor')
    ->get();

if ($incidencias->count() > 0) {
    foreach ($incidencias as $inc) {
        echo "  ID: {$inc->id} | ID Prov: {$inc->id_proveedor} | Clasificación: {$inc->clasificacion_incidencia} | {$inc->año}/{$inc->mes}\n";
    }
} else {
    echo "  ❌ No hay incidencias para este proveedor\n";
}

// Verificar métricas calculadas
echo "\n--- MÉTRICAS CALCULADAS (tabla proveedor_metrics) ---\n";
$metricas = DB::table('proveedor_metrics')
    ->where('proveedor_id', $proveedor_id)
    ->select('proveedor_id', 'año', 'mes', 'rg1', 'rl1', 'dev1', 'rok1', 'ret1')
    ->orderBy('año', 'desc')
    ->orderBy('mes', 'desc')
    ->get();

if ($metricas->count() > 0) {
    foreach ($metricas as $met) {
        echo "  {$met->año}/{$met->mes} => RG1={$met->rg1} | RL1={$met->rl1} | DEV1={$met->dev1} | ROK1={$met->rok1} | RET1={$met->ret1}\n";
    }
} else {
    echo "  ❌ No hay métricas calculadas para este proveedor\n";
}

// Simular lo que hace el controlador
echo "\n==========================================\n";
echo "SIMULACIÓN DEL CONTROLADOR\n";
echo "==========================================\n";

foreach ([9, 10] as $mes) {
    echo "\n--- Consultando MES $mes ---\n";
    
    $metrica_mes = DB::table('proveedor_metrics')
        ->where('proveedor_id', $proveedor_id)
        ->where('año', 2025)
        ->where('mes', $mes)
        ->first();
    
    if ($metrica_mes) {
        echo "✅ Encontrada: RG1={$metrica_mes->rg1} | RL1={$metrica_mes->rl1} | DEV1={$metrica_mes->dev1} | ROK1={$metrica_mes->rok1} | RET1={$metrica_mes->ret1}\n";
    } else {
        echo "❌ No hay métricas para este mes\n";
    }
}

// Verificar consulta con keyBy como en el controlador
echo "\n==========================================\n";
echo "SIMULACIÓN CON keyBy() (como en controlador)\n";
echo "==========================================\n";

$metricas_por_proveedor = DB::table('proveedor_metrics')
    ->where('año', 2025)
    ->where('mes', 10) // Octubre
    ->get()
    ->keyBy('proveedor_id');

echo "Total proveedores con métricas en Oct 2025: " . $metricas_por_proveedor->count() . "\n";

if (isset($metricas_por_proveedor[45])) {
    $m = $metricas_por_proveedor[45];
    echo "✅ Proveedor 45 encontrado: RG1={$m->rg1} | RL1={$m->rl1} | DEV1={$m->dev1} | ROK1={$m->rok1} | RET1={$m->ret1}\n";
} else {
    echo "❌ Proveedor 45 NO encontrado en el resultado\n";
}

echo "\n==========================================\n";
echo "CONCLUSIÓN\n";
echo "==========================================\n";
echo "Si ves 0 en la página, verifica:\n";
echo "1. ¿Qué MES tienes seleccionado en el filtro?\n";
echo "2. El proveedor 45 tiene datos en SEPTIEMBRE (9) y OCTUBRE (10)\n";
echo "3. Si filtras por ENERO (1), verás 0 porque no hay datos\n";
echo "==========================================\n";
