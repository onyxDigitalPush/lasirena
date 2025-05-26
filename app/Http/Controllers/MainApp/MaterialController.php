<?php

namespace App\Http\Controllers\MainApp;

use App\Models\MainApp\Material;
use App\Http\Controllers\Controller;
use App\Models\MainApp\Proveedor;
use Illuminate\Http\Request;

class MaterialController extends Controller
{

    public function index()
    {
        //
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


    public function store(Request $request)
    {
        $material = new Material();
        $material->descripcion = $request->input('descripcion');
        $material->codigo = $request->input('codigo');
        $material->jerarquia = $request->input('jerarquia');
        $material->proveedor_id = $request->input('id_proveedor');
        $material->save();
        return redirect()->back()->with('success', 'Material creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MainApp\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function show(Material $material)
    {
        //
    }

    public function edit($id)
    {
        $material = Material::find($id);

        if (!$material) {
            // Si es AJAX, devolvemos error en formato JSON
            if (request()->ajax()) {
                return response()->json(['error' => 'Material no encontrado.'], 404);
            }
            // Si no es AJAX, redirige con mensaje
            return redirect()->route('materiales.list', ['id' => $material->proveedor_id])->with('error', 'Material no encontrado.');
        }

        // Si es una petición AJAX, devolver JSON
        if (request()->ajax()) {
            return response()->json($material);
        }

        // Si no es AJAX, devuelve vista normalmente
        return view('material.material_edit', compact('material'));
    }
    public function update(Request $request)
    {
        $material = Material::find($request->input('id'));
        if (!$material) {
            return redirect()->back()->with('error', 'Material no encontrado.');
        }
        $material->descripcion = $request->input('descripcion');
        $material->codigo = $request->input('codigo');
        $material->jerarquia = $request->input('jerarquia');
        $material->proveedor_id = $request->input('proveedor_id');
        $material->save();
        return redirect()->back()->with('success', 'Material actualizado correctamente.');
    }

    public function destroy(Request $request)
    {
        $material = Material::find($request->input('id_material'));

        if (!$material) {
            return redirect()->back()->with('error', 'Material no encontrado.');
        }

        $material->delete();

        return redirect()->back()->with('success', 'Material eliminado correctamente.');
    }

    public function list($id)
    {
        $materiales = Material::where('materiales.proveedor_id', $id)
            ->join('proveedores', 'materiales.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select('materiales.*', 'proveedores.nombre_proveedor')
            ->get();
        $proveedor = Proveedor::where('id_proveedor', $id)->first();

        return view('material.material_list', compact('materiales', 'proveedor'));
    }
}
