<?php

namespace App\Http\Controllers\MainApp;

use App\Models\MainApp\MaterialKilo;
use App\Models\MainApp\ProveedorMetric;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialKiloController extends Controller   
{    public function index()
{
    $array_material_kilo = MaterialKilo::join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
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
        )
        ->orderBy('material_kilos.id', 'asc')
        ->paginate(25); // solo 50 por página

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
        // Obtener filtros - si no se especifican, usar mes y año actuales
        $mes = $request->get('mes', \Carbon\Carbon::now()->month);
        $año = $request->get('año', \Carbon\Carbon::now()->year);
        
        // Query base para totales por proveedor
        $query = MaterialKilo::join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select(
                'proveedores.id_proveedor',
                'proveedores.nombre_proveedor',
                DB::raw('SUM(gp_ls_material_kilos.total_kg) as total_kg_proveedor'),
                DB::raw('COUNT(gp_ls_material_kilos.id) as cantidad_registros')
            );
        
        // Aplicar filtros (ahora siempre están presentes)
        $query->where('material_kilos.año', $año);
        $query->where('material_kilos.mes', $mes);
        
        $totales_por_proveedor = $query->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderByDesc('total_kg_proveedor')
            ->get();
        
        // Obtener métricas existentes para el período filtrado
        $metricas_por_proveedor = ProveedorMetric::where('año', $año)
            ->where('mes', $mes)
            ->get()
            ->keyBy('proveedor_id');
        
        return view('MainApp/material_kilo.total_kg_por_proveedor', compact(
            'totales_por_proveedor', 
            'metricas_por_proveedor'
        ));
    }public function guardarMetricas(Request $request)
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
    }
}
