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
    //    Material::where('materiales.proveedor_id', $id)
    //         ->join('proveedores', 'materiales.proveedor_id', '=', 'proveedores.id_proveedor')
    //         ->select('materiales.*', 'proveedores.nombre_proveedor')
    //         ->get();
    $array_material_kilo =MaterialKilo::join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->join('materiales', 'material_kilos.material_id', '=', 'materiales.id')
            ->select(
                'material_kilos.id_material_kilo',
                'material_kilos.total_kg',
                'proveedores.nombre_proveedor',
                'materiales.descripcion as nombre_material'
            )
            ->orderBy('material_kilos.id_material_kilo', 'desc')
            ->get();

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MainApp\MaterialKilo  $materialKilo
     * @return \Illuminate\Http\Response
     */
    public function destroy(MaterialKilo $materialKilo)
    {
        //
    }
}
