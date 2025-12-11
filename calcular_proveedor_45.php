<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CÁLCULO MANUAL PROVEEDOR 45 (2025) ===" . PHP_EOL . PHP_EOL;

// Obtener total KG
$total_kg = DB::table('material_kilos')
    ->where('proveedor_id', 45)
    ->where('año', 2025)
    ->sum('total_kg');

echo "Total KG suministrado en 2025: " . number_format($total_kg, 2, ',', '.') . " kg" . PHP_EOL . PHP_EOL;

// Obtener métricas
$metricas = DB::table('proveedor_metrics')
    ->where('proveedor_id', 45)
    ->where('año', 2025)
    ->get(['mes', 'rg1', 'rl1', 'dev1', 'rok1', 'ret1']);

echo "Métricas registradas por mes:" . PHP_EOL;
foreach ($metricas as $m) {
    echo "  Mes {$m->mes}: RG1={$m->rg1} | RL1={$m->rl1} | DEV1={$m->dev1} | ROK1={$m->rok1} | RET1={$m->ret1}" . PHP_EOL;
}
echo PHP_EOL;

// Mostrar detalle de registros originales
echo "=== DETALLE DE REGISTROS ORIGINALES ===" . PHP_EOL . PHP_EOL;

echo "Tabla incidencias_proveedores (id_proveedor=45):" . PHP_EOL;
$incidencias = DB::table('incidencias_proveedores')
    ->where('id_proveedor', 45)
    ->where('año', 2025)
    ->get(['id', 'mes', 'clasificacion_incidencia', 'descripcion_incidencia', 'fecha_incidencia']);
foreach ($incidencias as $inc) {
    echo "  ID {$inc->id} | Mes {$inc->mes} | {$inc->clasificacion_incidencia} | {$inc->descripcion_incidencia}" . PHP_EOL;
}
echo "  Total registros: " . $incidencias->count() . PHP_EOL . PHP_EOL;

echo "Tabla devoluciones_proveedores (codigo_proveedor=45):" . PHP_EOL;
$devoluciones = DB::table('devoluciones_proveedores')
    ->where('codigo_proveedor', 45)
    ->where('año', 2025)
    ->get(['id', 'mes', 'clasificacion_incidencia', 'descripcion_queja', 'fecha_inicio']);
foreach ($devoluciones as $dev) {
    $desc = $dev->descripcion_queja ?? 'N/A';
    $desc_corta = strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
    echo "  ID {$dev->id} | Mes {$dev->mes} | {$dev->clasificacion_incidencia} | {$desc_corta}" . PHP_EOL;
}
echo "  Total registros: " . $devoluciones->count() . PHP_EOL . PHP_EOL;

echo "RESUMEN DE CONTEO:" . PHP_EOL;
$rg1_count = $incidencias->where('clasificacion_incidencia', 'RG1')->count();
$rl1_inc_count = $incidencias->where('clasificacion_incidencia', 'RL1')->count();
$rl1_dev_count = $devoluciones->where('clasificacion_incidencia', 'RL1')->count();
$dev1_inc_count = $incidencias->where('clasificacion_incidencia', 'DEV1')->count();
$dev1_dev_count = $devoluciones->where('clasificacion_incidencia', 'DEV1')->count();
$rok1_count = $devoluciones->where('clasificacion_incidencia', 'ROK1')->count();
$ret1_count = $devoluciones->where('clasificacion_incidencia', 'RET1')->count();

echo "  RG1:  {$rg1_count} (en incidencias_proveedores)" . PHP_EOL;
echo "  RL1:  {$rl1_inc_count} (en incidencias_proveedores) + {$rl1_dev_count} (en devoluciones_proveedores) = " . ($rl1_inc_count + $rl1_dev_count) . PHP_EOL;
echo "  DEV1: {$dev1_inc_count} (en incidencias_proveedores) + {$dev1_dev_count} (en devoluciones_proveedores) = " . ($dev1_inc_count + $dev1_dev_count) . PHP_EOL;
echo "  ROK1: {$rok1_count} (en devoluciones_proveedores)" . PHP_EOL;
echo "  RET1: {$ret1_count} (en devoluciones_proveedores)" . PHP_EOL;
echo "  TOTAL INCIDENCIAS: " . ($rg1_count + $rl1_inc_count + $rl1_dev_count + $dev1_inc_count + $dev1_dev_count + $rok1_count + $ret1_count) . PHP_EOL;
echo PHP_EOL;

// Calcular promedios
$avg_rg1 = $metricas->avg('rg1');
$avg_rl1 = $metricas->avg('rl1');
$avg_dev1 = $metricas->avg('dev1');
$avg_rok1 = $metricas->avg('rok1');
$avg_ret1 = $metricas->avg('ret1');

echo "Promedios (para año completo):" . PHP_EOL;
echo "  RG1:  " . number_format($avg_rg1, 2) . PHP_EOL;
echo "  RL1:  " . number_format($avg_rl1, 2) . PHP_EOL;
echo "  DEV1: " . number_format($avg_dev1, 2) . PHP_EOL;
echo "  ROK1: " . number_format($avg_rok1, 2) . PHP_EOL;
echo "  RET1: " . number_format($avg_ret1, 2) . PHP_EOL;
echo PHP_EOL;

// Calcular indicadores (por millón de KG)
$rg_ind = $avg_rg1 * 1000000 / $total_kg;
$rl_ind = $avg_rl1 * 1000000 / $total_kg;
$dev_ind = $avg_dev1 * 1000000 / $total_kg;
$rok_ind = $avg_rok1 * 1000000 / $total_kg;
$ret_ind = $avg_ret1 * 1000000 / $total_kg;
$total_ind = $rg_ind + $rl_ind + $dev_ind + $rok_ind + $ret_ind;

echo "Indicadores por Millón de KG (ppm):" . PHP_EOL;
echo "  RG:    " . number_format($rg_ind, 2) . " ppm" . PHP_EOL;
echo "  RL:    " . number_format($rl_ind, 2) . " ppm" . PHP_EOL;
echo "  DEV:   " . number_format($dev_ind, 2) . " ppm" . PHP_EOL;
echo "  ROK:   " . number_format($rok_ind, 2) . " ppm" . PHP_EOL;
echo "  RET:   " . number_format($ret_ind, 2) . " ppm" . PHP_EOL;
echo "  TOTAL: " . number_format($total_ind, 2) . " ppm" . PHP_EOL;
echo PHP_EOL;

// Calcular valores ponderados
$rg_pond = $rg_ind * 0.30;
$rl_pond = $rl_ind * 0.05;
$dev_pond = $dev_ind * 0.20;
$rok_pond = $rok_ind * 0.10;
$ret_pond = $ret_ind * 0.35;
$total_pond = $rg_pond + $rl_pond + $dev_pond + $rok_pond + $ret_pond;

echo "Valores Ponderados:" . PHP_EOL;
echo "  RG  (30%): " . number_format($rg_pond, 2) . " puntos" . PHP_EOL;
echo "  RL   (5%): " . number_format($rl_pond, 2) . " puntos" . PHP_EOL;
echo "  DEV (20%): " . number_format($dev_pond, 2) . " puntos" . PHP_EOL;
echo "  ROK (10%): " . number_format($rok_pond, 2) . " puntos" . PHP_EOL;
echo "  RET (35%): " . number_format($ret_pond, 2) . " puntos" . PHP_EOL;
echo "  TOTAL:     " . number_format($total_pond, 2) . " puntos" . PHP_EOL;
echo PHP_EOL;

echo "=== RESUMEN PARA LA TABLA ===" . PHP_EOL;
echo "ID Proveedor: 45" . PHP_EOL;
echo "Nombre: ALIMENTBARNA SL" . PHP_EOL;
echo "Total KG: " . number_format($total_kg, 2, ',', '.') . " kg" . PHP_EOL;
echo PHP_EOL;
echo "Valores por Millón de KG:" . PHP_EOL;
echo "  RG\tRL\tDEV\tROK\tRET\tTOTAL" . PHP_EOL;
echo "  " . number_format($rg_ind, 2) . "\t" . number_format($rl_ind, 2) . "\t" . number_format($dev_ind, 2) . "\t" . number_format($rok_ind, 2) . "\t" . number_format($ret_ind, 2) . "\t" . number_format($total_ind, 2) . PHP_EOL;
echo PHP_EOL;
echo "Valores Ponderados:" . PHP_EOL;
echo "  RG\tRL\tDEV\tROK\tRET\tTOTAL" . PHP_EOL;
echo "  " . number_format($rg_pond, 2) . "\t" . number_format($rl_pond, 2) . "\t" . number_format($dev_pond, 2) . "\t" . number_format($rok_pond, 2) . "\t" . number_format($ret_pond, 2) . "\t" . number_format($total_pond, 2) . PHP_EOL;
