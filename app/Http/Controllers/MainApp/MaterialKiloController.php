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


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MainApp\MaterialKilo  $materialKilo
     * @return \Illuminate\Http\Response
     */
    public function show(MaterialKilo $materialKilo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MainApp\MaterialKilo  $materialKilo
     * @return \Illuminate\Http\Response
     */
    public function edit(MaterialKilo $materialKilo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MainApp\MaterialKilo  $materialKilo
     * @return \Illuminate\Http\Response
     */
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
