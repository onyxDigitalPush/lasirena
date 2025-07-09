<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\MainApp\ProveedorMetric;

class SincronizarMetricasProveedores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metricas:sincronizar {--año=} {--mes=} {--proveedor=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar métricas de proveedores basadas en incidencias reales';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $año = $this->option('año') ?? date('Y');
        $mes = $this->option('mes');
        $proveedor = $this->option('proveedor');

        $this->info("Sincronizando métricas para año: {$año}");
        
        if ($mes) {
            $this->info("Mes específico: {$mes}");
        }
        
        if ($proveedor) {
            $this->info("Proveedor específico: {$proveedor}");
        }

        // Obtener combinaciones únicas de proveedor/año/mes de métricas existentes
        $query = ProveedorMetric::where('año', $año);
        
        if ($mes) {
            $query->where('mes', $mes);
        }
        
        if ($proveedor) {
            $query->where('proveedor_id', $proveedor);
        }
        
        $metricas_existentes = $query->get();
        
        $actualizadas = 0;
        
        foreach ($metricas_existentes as $metrica) {
            // Calcular métricas reales desde incidencias
            $metricas_incidencias = DB::table('incidencias_proveedores')
                ->where('id_proveedor', $metrica->proveedor_id)
                ->where('año', $metrica->año)
                ->where('mes', $metrica->mes)
                ->select([
                    DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RG1" THEN 1 ELSE 0 END) as rg1'),
                    DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RL1" THEN 1 ELSE 0 END) as rl1'),
                    DB::raw('SUM(CASE WHEN clasificacion_incidencia = "DEV1" THEN 1 ELSE 0 END) as dev1'),
                    DB::raw('SUM(CASE WHEN clasificacion_incidencia = "ROK1" THEN 1 ELSE 0 END) as rok1'),
                    DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RET1" THEN 1 ELSE 0 END) as ret1'),
                ])
                ->first();

            // Calcular métricas reales desde devoluciones (solo RG1 y RL1)
            $metricas_devoluciones = DB::table('devoluciones_proveedores')
                ->where('nombre_proveedor', function($query) use ($metrica) {
                    $query->select('nombre_proveedor')
                          ->from('material_kilos')
                          ->join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
                          ->where('proveedores.id_proveedor', $metrica->proveedor_id)
                          ->limit(1);
                })
                ->where('año', $metrica->año)
                ->where('mes', $metrica->mes)
                ->select([
                    DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RG1" THEN 1 ELSE 0 END) as rg1'),
                    DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RL1" THEN 1 ELSE 0 END) as rl1'),
                ])
                ->first();

            // Sumar métricas de incidencias y devoluciones
            $rg1_total = ($metricas_incidencias->rg1 ?? 0) + ($metricas_devoluciones->rg1 ?? 0);
            $rl1_total = ($metricas_incidencias->rl1 ?? 0) + ($metricas_devoluciones->rl1 ?? 0);

            // Actualizar métricas
            $metrica->update([
                'rg1' => $rg1_total,
                'rl1' => $rl1_total,
                'dev1' => $metricas_incidencias->dev1 ?? 0,
                'rok1' => $metricas_incidencias->rok1 ?? 0,
                'ret1' => $metricas_incidencias->ret1 ?? 0,
            ]);
            
            $actualizadas++;
            
            $inc_rg1 = $metricas_incidencias->rg1 ?? 0;
            $dev_rg1 = $metricas_devoluciones->rg1 ?? 0;
            $inc_rl1 = $metricas_incidencias->rl1 ?? 0;
            $dev_rl1 = $metricas_devoluciones->rl1 ?? 0;
            $dev1 = $metricas_incidencias->dev1 ?? 0;
            $rok1 = $metricas_incidencias->rok1 ?? 0;
            $ret1 = $metricas_incidencias->ret1 ?? 0;
            
            $this->line("Proveedor {$metrica->proveedor_id} - {$metrica->año}/{$metrica->mes}: RG1={$rg1_total} (Inc:{$inc_rg1} + Dev:{$dev_rg1}), RL1={$rl1_total} (Inc:{$inc_rl1} + Dev:{$dev_rl1}), DEV1={$dev1}, ROK1={$rok1}, RET1={$ret1}");
        }
        
        $this->info("Se actualizaron {$actualizadas} registros de métricas.");
        
        return 0;
    }
}
