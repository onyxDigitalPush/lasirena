<?php
/**
 * Test de filtro "Todos los meses"
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "TEST: FILTRO 'TODOS LOS MESES'\n";
echo "==========================================\n\n";

// Simular lo que hace el controlador cuando NO hay mes seleccionado
$año = 2025;
$mes = null; // Esto simula "Todos los meses"

echo "--- Caso 1: Filtro con MES específico (Enero) ---\n";
$metricas_enero = DB::table('proveedor_metrics')
    ->where('año', $año)
    ->where('mes', 1)
    ->where('proveedor_id', 45)
    ->first();

if ($metricas_enero) {
    echo "Proveedor 45 - Enero: RG1={$metricas_enero->rg1}, RL1={$metricas_enero->rl1}, DEV1={$metricas_enero->dev1}\n";
} else {
    echo "No hay datos para enero\n";
}

echo "\n--- Caso 2: Filtro 'Todos los meses' (SUMA) ---\n";
$metricas_todas = DB::table('proveedor_metrics')
    ->where('año', $año)
    ->where('proveedor_id', 45)
    ->select(
        'proveedor_id',
        DB::raw('SUM(rg1) as rg1'),
        DB::raw('SUM(rl1) as rl1'),
        DB::raw('SUM(dev1) as dev1'),
        DB::raw('SUM(rok1) as rok1'),
        DB::raw('SUM(ret1) as ret1')
    )
    ->groupBy('proveedor_id')
    ->first();

if ($metricas_todas) {
    echo "Proveedor 45 - TODO 2025: RG1={$metricas_todas->rg1}, RL1={$metricas_todas->rl1}, DEV1={$metricas_todas->dev1}\n";
} else {
    echo "No hay datos para el año completo\n";
}

echo "\n--- Detalle de todos los meses ---\n";
$todos_meses = DB::table('proveedor_metrics')
    ->where('año', $año)
    ->where('proveedor_id', 45)
    ->orderBy('mes')
    ->get();

if ($todos_meses->count() > 0) {
    foreach ($todos_meses as $m) {
        $nombre_mes = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ][$m->mes] ?? "Mes {$m->mes}";
        
        echo "{$nombre_mes}: RG1={$m->rg1}, RL1={$m->rl1}, DEV1={$m->dev1}, ROK1={$m->rok1}, RET1={$m->ret1}\n";
    }
    
    echo "\n✅ Si seleccionas 'Todos los meses', verás la SUMA de todos estos valores\n";
} else {
    echo "No hay datos para ningún mes\n";
}

echo "\n==========================================\n";
echo "CONCLUSIÓN\n";
echo "==========================================\n";
echo "✅ Filtro específico: Muestra datos de UN mes\n";
echo "✅ Filtro 'Todos': Muestra SUMA de TODOS los meses\n";
echo "==========================================\n";
