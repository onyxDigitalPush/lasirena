<?php

namespace App\Http\Controllers\MainApp;

use App\Models\MainApp\MaterialKilo;
use App\Models\MainApp\ProveedorMetric;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaterialKiloController extends Controller   
{    public function index()
{
    $query = MaterialKilo::join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
        ->join('materiales', 'material_kilos.codigo_material', '=', 'materiales.codigo')
        ->select(
            'material_kilos.id',
            'material_kilos.total_kg',
            'proveedores.nombre_proveedor',
            'materiales.descripcion as nombre_material',
            'material_kilos.ctd_emdev',
            'material_kilos.umb',
            'material_kilos.ce',
            'material_kilos.valor_emdev',
            'material_kilos.factor_conversion',
            'material_kilos.codigo_material',
            'material_kilos.mes',
        );

    // Aplicar ordenamiento según el filtro
    $orden = request('orden');
    if ($orden == 'total_kg_desc') {
        $query->orderBy('material_kilos.total_kg', 'desc');
    } elseif ($orden == 'total_kg_asc') {
        $query->orderBy('material_kilos.total_kg', 'asc');
    } else {
        $query->orderBy('material_kilos.id', 'asc');
    }

    $array_material_kilo = $query->paginate(25);

    return view('MainApp/material_kilo.material_kilo_list', compact('array_material_kilo'));
}



    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(MaterialKilo $materialKilo)
    {
        //
    }

  
    public function edit(MaterialKilo $materialKilo)
    {
        //
    }


    public function update(Request $request, MaterialKilo $materialKilo)
    {
        //
    }


    public function destroy(Request $request)
    {
        $materialKilo = MaterialKilo::findOrFail($request->input('id_material_kilo'));
        $materialKilo->delete();
        return redirect()->route('material_kilo.index')->with('success', 'Material Kilo eliminado correctamente.');

    }    public function totalKgPorProveedor(Request $request)
    {
        // Obtener filtros con valores por defecto (Enero del año actual)
        $mes = $request->get('mes', 1); // Por defecto enero (mes 1)
        $año = $request->get('año', date('Y')); // Por defecto año actual
        
        // Query base para totales por proveedor
        $query = MaterialKilo::join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select(
                'proveedores.id_proveedor',
                'proveedores.nombre_proveedor',
                DB::raw('SUM(gp_ls_material_kilos.total_kg) as total_kg_proveedor'),
                DB::raw('COUNT(gp_ls_material_kilos.id) as cantidad_registros')
            );
        
        // Aplicar filtros
        $query->where('material_kilos.año', $año);
        
        // Si mes está seleccionado, filtrar por mes específico
        if ($mes) {
            $query->where('material_kilos.mes', $mes);
        }
        
        $totales_por_proveedor = $query->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderByDesc('total_kg_proveedor')
            ->get();
        
        // Obtener métricas existentes para el período filtrado
        $metricas_query = ProveedorMetric::where('año', $año);
        
        // Si mes está seleccionado, filtrar métricas por mes específico
        if ($mes) {
            $metricas_query->where('mes', $mes);
        }
        
        $metricas_por_proveedor = $metricas_query->get()->keyBy('proveedor_id');
        
        return view('MainApp/material_kilo.total_kg_por_proveedor', compact(
            'totales_por_proveedor', 
            'metricas_por_proveedor',
            'mes',
            'año'
        ));
    }

    public function guardarMetricas(Request $request)
    {
        try {
            $request->validate([
                'metricas' => 'required|array',
                'año' => 'required|integer',
                'mes' => 'required|integer|between:1,12'
            ]);

            $metricas = $request->input('metricas');
            $año = $request->input('año');
            $mes = $request->input('mes');

            foreach ($metricas as $proveedor_id => $datos) {
                ProveedorMetric::updateOrCreate(
                    [
                        'proveedor_id' => $proveedor_id,
                        'año' => $año,
                        'mes' => $mes
                    ],
                    [
                        'rg1' => $datos['rg1'] ?? null,
                        'rl1' => $datos['rl1'] ?? null,
                        'dev1' => $datos['dev1'] ?? null,
                        'rok1' => $datos['rok1'] ?? null,
                        'ret1' => $datos['ret1'] ?? null
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Métricas guardadas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las métricas: ' . $e->getMessage()
            ], 500);
        }
    }    public function evaluacionContinuaProveedores(Request $request)
    {
        $mes = $request->get('mes', 1); // Por defecto enero (mes 1)
        $año = $request->get('año', \Carbon\Carbon::now()->year);

        // Obtener totales por proveedor para el mes y año específicos
        $query = DB::table('material_kilos')
            ->join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select(
                'proveedores.id_proveedor',
                'proveedores.nombre_proveedor',
                DB::raw('SUM(gp_ls_material_kilos.total_kg) as total_kg_proveedor'),
                DB::raw('COUNT(gp_ls_material_kilos.id) as cantidad_registros')
            )
            ->where('material_kilos.año', $año);

        // Si mes está seleccionado, filtrar por mes específico
        if ($mes) {
            $query->where('material_kilos.mes', $mes);
        }

        $totales_por_proveedor = $query->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderBy('total_kg_proveedor', 'desc')
            ->get();

        // Obtener métricas existentes para los proveedores
        $metricas_por_proveedor = [];
        if ($totales_por_proveedor->isNotEmpty()) {
            $proveedores_ids = $totales_por_proveedor->pluck('id_proveedor')->toArray();
            $metricas_query = ProveedorMetric::whereIn('proveedor_id', $proveedores_ids)
                ->where('año', $año);

            // Si mes está seleccionado, filtrar por mes específico
            if ($mes) {
                $metricas_query->where('mes', $mes);
                $metricas = $metricas_query->get();
                
                foreach ($metricas as $metrica) {
                    $metricas_por_proveedor[$metrica->proveedor_id] = $metrica;
                }
            } else {
                // Si no hay mes específico, calcular promedio de todas las métricas del año
                $metricas = $metricas_query->get();
                $metricas_agrupadas = $metricas->groupBy('proveedor_id');
                
                foreach ($metricas_agrupadas as $proveedor_id => $metricas_proveedor) {
                    $promedio = new \stdClass();
                    $promedio->proveedor_id = $proveedor_id;
                    $promedio->rg1 = $metricas_proveedor->avg('rg1');
                    $promedio->rl1 = $metricas_proveedor->avg('rl1');
                    $promedio->dev1 = $metricas_proveedor->avg('dev1');
                    $promedio->rok1 = $metricas_proveedor->avg('rok1');
                    $promedio->ret1 = $metricas_proveedor->avg('ret1');
                    
                    $metricas_por_proveedor[$proveedor_id] = $promedio;
                }
            }
        }

        // Calcular indicadores y ponderados para cada proveedor
        foreach ($totales_por_proveedor as $proveedor) {
            $metricas = isset($metricas_por_proveedor[$proveedor->id_proveedor]) 
                ? $metricas_por_proveedor[$proveedor->id_proveedor] 
                : null;            if ($metricas && $proveedor->total_kg_proveedor > 0) {
                // Cálculos de indicadores (valores * 1000000 / total_kg)
                $proveedor->rg_ind1 = ($metricas->rg1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
                $proveedor->rl_ind1 = ($metricas->rl1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
                $proveedor->dev_ind1 = ($metricas->dev1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
                $proveedor->rok_ind1 = ($metricas->rok1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
                $proveedor->ret_ind1 = ($metricas->ret1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
                $proveedor->total_ind1 = $proveedor->rg_ind1 + $proveedor->rl_ind1 + $proveedor->dev_ind1 + $proveedor->rok_ind1 + $proveedor->ret_ind1;

                // Cálculos de ponderados (usando los valores por millón * porcentajes)
                $proveedor->rg_pond1 = $proveedor->rg_ind1 * 0.30; // RGind1 * 30%
                $proveedor->rl_pond1 = $proveedor->rl_ind1 * 0.05; // RLind1 * 5%
                $proveedor->dev_pond1 = $proveedor->dev_ind1 * 0.20; // DEVind1 * 20%
                $proveedor->rok_pond1 = $proveedor->rok_ind1 * 0.10; // ROKind1 * 10%
                $proveedor->ret_pond1 = $proveedor->ret_ind1 * 0.35; // RETind1 * 35%
                $proveedor->total_pond1 = $proveedor->rg_pond1 + $proveedor->rl_pond1 + $proveedor->dev_pond1 + $proveedor->rok_pond1 + $proveedor->ret_pond1;
            } else {
                // Si no hay métricas o total_kg es 0, inicializar en 0
                $proveedor->rg_ind1 = 0;
                $proveedor->rl_ind1 = 0;
                $proveedor->dev_ind1 = 0;
                $proveedor->rok_ind1 = 0;
                $proveedor->ret_ind1 = 0;
                $proveedor->total_ind1 = 0;
                $proveedor->rg_pond1 = 0;
                $proveedor->rl_pond1 = 0;
                $proveedor->dev_pond1 = 0;
                $proveedor->rok_pond1 = 0;
                $proveedor->ret_pond1 = 0;
                $proveedor->total_pond1 = 0;
            }
        }

        return view('MainApp/material_kilo.evaluacion_continua_proveedores', compact(
            'totales_por_proveedor', 
            'metricas_por_proveedor',
            'mes',
            'año'
        ));
    }
}
