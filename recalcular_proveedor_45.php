<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== RECALCULANDO MÉTRICAS PROVEEDOR 45 (2025) ===" . PHP_EOL . PHP_EOL;

// Obtener todos los períodos con material_kilos del proveedor 45
$periodos = DB::table('material_kilos')
    ->select('proveedor_id as id_proveedor', 'año', 'mes')
    ->where('proveedor_id', 45)
    ->where('año', 2025)
    ->groupBy('proveedor_id', 'año', 'mes')
    ->orderBy('mes')
    ->get();

echo "Periodos encontrados en material_kilos: " . $periodos->count() . PHP_EOL;

foreach ($periodos as $periodo) {
    $id = $periodo->id_proveedor;
    $año = $periodo->año;
    $mes = $periodo->mes;
    
    echo PHP_EOL . "Procesando Mes {$mes}/{$año}..." . PHP_EOL;
    
    // Contar RG1 en incidencias_proveedores
    $rg1 = DB::table('incidencias_proveedores')
        ->where('id_proveedor', $id)
        ->where('año', $año)
        ->where('mes', $mes)
        ->where('clasificacion_incidencia', 'RG1')
        ->count();
    
    // Contar RL1 en AMBAS tablas
    $rl1_inc = DB::table('incidencias_proveedores')
        ->where('id_proveedor', $id)
        ->where('año', $año)
        ->where('mes', $mes)
        ->where('clasificacion_incidencia', 'RL1')
        ->count();
    
    $rl1_dev = DB::table('devoluciones_proveedores')
        ->where('codigo_proveedor', $id)
        ->where('año', $año)
        ->where('mes', $mes)
        ->where('clasificacion_incidencia', 'RL1')
        ->count();
    
    $rl1 = $rl1_inc + $rl1_dev;
    
    // Contar DEV1 en AMBAS tablas
    $dev1_inc = DB::table('incidencias_proveedores')
        ->where('id_proveedor', $id)
        ->where('año', $año)
        ->where('mes', $mes)
        ->where('clasificacion_incidencia', 'DEV1')
        ->count();
    
    $dev1_dev = DB::table('devoluciones_proveedores')
        ->where('codigo_proveedor', $id)
        ->where('año', $año)
        ->where('mes', $mes)
        ->where('clasificacion_incidencia', 'DEV1')
        ->count();
    
    $dev1 = $dev1_inc + $dev1_dev;
    
    // Contar ROK1 y RET1
    $rok1 = DB::table('devoluciones_proveedores')
        ->where('codigo_proveedor', $id)
        ->where('año', $año)
        ->where('mes', $mes)
        ->where('clasificacion_incidencia', 'ROK1')
        ->count();
    
    $ret1 = DB::table('devoluciones_proveedores')
        ->where('codigo_proveedor', $id)
        ->where('año', $año)
        ->where('mes', $mes)
        ->where('clasificacion_incidencia', 'RET1')
        ->count();
    
    echo "  Conteo: RG1={$rg1}, RL1={$rl1} (inc:{$rl1_inc} + dev:{$rl1_dev}), DEV1={$dev1} (inc:{$dev1_inc} + dev:{$dev1_dev}), ROK1={$rok1}, RET1={$ret1}" . PHP_EOL;
    
    // Actualizar proveedor_metrics
    DB::table('proveedor_metrics')->updateOrInsert(
        [
            'proveedor_id' => $id,
            'año' => $año,
            'mes' => $mes
        ],
        [
            'rg1' => $rg1,
            'rl1' => $rl1,
            'dev1' => $dev1,
            'rok1' => $rok1,
            'ret1' => $ret1,
            'updated_at' => now()
        ]
    );
    
    echo "  ✓ Métricas actualizadas en proveedor_metrics" . PHP_EOL;
}

echo PHP_EOL . "=== MÉTRICAS FINALES EN proveedor_metrics ===" . PHP_EOL;
$metricas_finales = DB::table('proveedor_metrics')
    ->where('proveedor_id', 45)
    ->where('año', 2025)
    ->orderBy('mes')
    ->get(['mes', 'rg1', 'rl1', 'dev1', 'rok1', 'ret1']);

foreach ($metricas_finales as $m) {
    echo "Mes {$m->mes}: RG1={$m->rg1} | RL1={$m->rl1} | DEV1={$m->dev1} | ROK1={$m->rok1} | RET1={$m->ret1} | TOTAL=" . ($m->rg1 + $m->rl1 + $m->dev1 + $m->rok1 + $m->ret1) . PHP_EOL;
}

echo PHP_EOL . "¡Recálculo completado!" . PHP_EOL;
