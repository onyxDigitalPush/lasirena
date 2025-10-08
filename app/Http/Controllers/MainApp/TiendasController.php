<?php

namespace App\Http\Controllers\MainApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tienda;

class TiendasController extends Controller
{
    public function index(Request $request)
    {
        $query = Tienda::query();
        if ($request->filled('num_tienda')) {
            $query->where('num_tienda', 'like', '%' . $request->num_tienda . '%');
        }
        if ($request->filled('nombre_tienda')) {
            $query->where('nombre_tienda', 'like', '%' . $request->nombre_tienda . '%');
        }
        $tiendas = $query->orderBy('num_tienda')->get();
        return view('MainApp.tiendas.tiendas_list', compact('tiendas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'num_tienda' => 'required',
            'nombre_tienda' => 'required',
            'direccion_tienda' => 'required',
            'telefono' => 'required',
            'cp' => 'nullable|string|max:10',
            'ciudad' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
        ]);
        Tienda::create($request->all());
        return redirect()->back()->with('success', 'Tienda creada correctamente');
    }

    public function edit($id)
    {
        $tienda = Tienda::findOrFail($id);
        return response()->json($tienda);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:tiendas,id',
            'num_tienda' => 'required',
            'nombre_tienda' => 'required',
            'direccion_tienda' => 'required',
            'telefono' => 'required',
            'cp' => 'nullable|string|max:10',
            'ciudad' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
        ]);
        $tienda = Tienda::findOrFail($request->id);
        $tienda->update($request->all());
        return redirect()->back()->with('success', 'Tienda actualizada correctamente');
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:tiendas,id',
        ]);
        $tienda = Tienda::findOrFail($request->id);
        $tienda->delete();
        return redirect()->back()->with('success', 'Tienda eliminada correctamente');
    }

    public function buscar(Request $request)
    {
        $query = Tienda::query();
        if ($request->filled('num_tienda')) {
            $query->where('num_tienda', 'like', '%' . $request->num_tienda . '%');
        }
        if ($request->filled('nombre_tienda')) {
            $query->where('nombre_tienda', 'like', '%' . $request->nombre_tienda . '%');
        }
        $tiendas = $query->orderBy('num_tienda')->get();
        // Devolver solo los datos necesarios para la tabla
        $data = $tiendas->map(function($t) {
            return [
                'num_tienda' => $t->num_tienda,
                'nombre_tienda' => $t->nombre_tienda,
                'direccion_tienda' => $t->direccion_tienda,
                'telefono' => $t->telefono,
                'cp' => $t->cp,
                'ciudad' => $t->ciudad,
                'provincia' => $t->provincia,
                'id' => $t->id
            ];
        });
        return response()->json(['data' => $data]);
    }
}
