<?php

namespace App\Http\Controllers\MainApp;

use App\Models\MainApp\MaterialKilo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialKiloController extends Controller
{

    public function index()
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
        ->paginate(50); // solo 50 por página

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

    }
}
