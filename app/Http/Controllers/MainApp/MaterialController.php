<?php

namespace App\Http\Controllers\MainApp;

use App\Models\MainApp\Material;
use App\Http\Controllers\Controller;
use App\Models\MainApp\Proveedor;
use App\Models\MainApp\MaterialKilo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MaterialController extends Controller
{

    public function index()
    {
        //
    }

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
        $material->factor_conversion = $request->input('factor_conversion');
        $material->save();
        
        // Si se proporcionó un factor de conversión, actualizar material_kilos
        if ($request->input('factor_conversion')) {
            $registros_actualizados = $this->actualizarFactorConversionEnMaterialKilos($material->codigo, $request->input('factor_conversion'));
            return redirect()->back()->with('success', 
                "Material creado correctamente. Se actualizaron {$registros_actualizados} registros en material_kilos con el factor de conversión y se recalculó el total_kg."
            );
        }
        
        return redirect()->back()->with('success', 'Material creado correctamente.');
    }


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
        $material->factor_conversion = $request->input('factor_conversion');
        $material->save();
        
        // Si se proporcionó un factor de conversión, actualizar material_kilos
        if ($request->input('factor_conversion')) {
            $registros_actualizados = $this->actualizarFactorConversionEnMaterialKilos($material->codigo, $request->input('factor_conversion'));
            return redirect()->back()->with('success', 
                "Material actualizado correctamente. Se actualizaron {$registros_actualizados} registros en material_kilos con el nuevo factor de conversión y se recalculó el total_kg (ctd_emdev × factor_conversion)."
            );
        }
        
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
        $query = Material::where('materiales.proveedor_id', $id)
            ->join('proveedores', 'materiales.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select('materiales.*', 'proveedores.nombre_proveedor');

        // Aplicar filtros
        $filtro = request('filtro');
        if ($filtro == 'con_factor') {
            $query->whereNotNull('materiales.factor_conversion')
                  ->where('materiales.factor_conversion', '>', 0);
        } elseif ($filtro == 'sin_factor') {
            $query->whereNull('materiales.factor_conversion');
        } elseif ($filtro == 'factor_cero') {
            $query->where('materiales.factor_conversion', '=', 0);
        }

        $materiales = $query->get();
        $proveedor = Proveedor::where('id_proveedor', $id)->first();

        return view('material.material_list', compact('materiales', 'proveedor'));
    }

    /**
     * Actualiza el factor de conversión en todos los registros de material_kilos 
     * que coincidan con el código de material y calcula total_kg = ctd_emdev * factor_conversion
     */
    private function actualizarFactorConversionEnMaterialKilos($codigo_material, $factor_conversion)
    {
        try {
            $factor_conversion = (float) $factor_conversion;
            
            // Obtener todos los registros de material_kilos para este código de material
            $registros = MaterialKilo::where('codigo_material', $codigo_material)->get();
            
            $registros_actualizados = 0;
            
            foreach ($registros as $registro) {
                // Actualizar factor_conversion y calcular total_kg
                $registro->factor_conversion = $factor_conversion;
                
                // Calcular total_kg = ctd_emdev * factor_conversion
                $ctd_emdev = (float) ($registro->ctd_emdev ?? 0);
                $registro->total_kg = $ctd_emdev * $factor_conversion;
                
                $registro->save();
                $registros_actualizados++;
            }
            
            Log::info("Factor de conversión y total_kg actualizados para material {$codigo_material}. Registros afectados: {$registros_actualizados}");
            
            return $registros_actualizados;
            
        } catch (\Exception $e) {
            Log::error("Error al actualizar factor de conversión para material {$codigo_material}: " . $e->getMessage());
            return 0;
        }
    }
}
