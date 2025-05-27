<?php

namespace App\Http\Controllers\MainApp;

use App\Models\MainApp\Proveedor;
use App\Models\MainApp\Material;
use App\Models\MainApp\MaterialKilo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MainApp\Project;
use Illuminate\Support\Facades\Log;

class ProveedorController extends Controller
{

    public function index()
    {
        $array_proveedores = Proveedor::select('id_proveedor', 'nombre_proveedor')
            ->orderBy('id_proveedor', 'desc')
            ->get();
        return view('proveedores.proveedor_list', compact('array_proveedores'));
    }
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
        $proveedor = new Proveedor();
        $proveedor->id_proveedor = $request->id_proveedor;
        $proveedor->nombre_proveedor = $request->nombre_proveedor;
        $proveedor->save();
        return redirect()->back()->with('success', 'Proveedor creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MainApp\Proveedor  $proveedor
     * @return \Illuminate\Http\Response
     */
    public function show(Proveedor $proveedor)
    {
        //
    }

    public function edit($proveedor)
    {
        $proveedor = Proveedor::find((int) $proveedor);
        if (!$proveedor) {
            // Si es AJAX, devolvemos error en formato JSON
            if (request()->ajax()) {
                return response()->json(['error' => 'Proveedor no encontrado.'], 404);
            }
            // Si no es AJAX, redirige con mensaje
            return redirect()->route('proveedores.index')->with('error', 'Proveedor no encontrado.');
        }
        // Si es una petición AJAX, devolver JSON
        if (request()->ajax()) {
            return response()->json($proveedor);
        }
        // Si no es AJAX, devuelve vista normalmente
        return view('proveedores.proveedor_edit', compact('proveedor'));
    }
    public function update(Request $request)
    {
        //
        $proveedor = Proveedor::find($request->input('codigo_proveedor_old'));
        if (!$proveedor) {
            return redirect()->back()->with('error', 'Proveedor no encontrado.');
        }

        $proveedor->id_proveedor = $request->input('id_proveedor');
        $proveedor->nombre_proveedor = $request->input('nombre_proveedor_edit');
        $proveedor->save();
        return redirect()->back()->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MainApp\Proveedor  $proveedor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Proveedor $proveedor)
    {
        //
    }
    public function importarCSV(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt',
        ]);

        $archivo = $request->file('archivo');
        $path = $archivo->getRealPath();

        $delimitadores = [',', ';', "\t"];
        $delimitadorDetectado = null;

        // 1. Leer la línea 4 para detectar el delimitador
        $lineas = [];
        $handle = fopen($path, 'r');
        if ($handle) {
            for ($i = 0; $i < 5; $i++) {
                $linea = fgets($handle);
                if ($linea === false) break;
                $lineas[] = $linea;
            }
            fclose($handle);
        }

        $linea4 = $lineas[3] ?? '';

        // Detectar delimitador con más ocurrencias en la línea 4
        $maxCount = 0;
        foreach ($delimitadores as $delim) {
            $count = substr_count($linea4, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $delimitadorDetectado = $delim;
            }
        }

        if (!$delimitadorDetectado) {
            return back()->withErrors(['archivo' => 'No se pudo detectar el delimitador del archivo.']);
        }

        $cabeceras = [];
        $datos = [];

        // 2. Leer el archivo con el delimitador detectado
        if (($handle = fopen($path, 'r')) !== false) {
            $fila = 0;
            while (($linea = fgetcsv($handle, 1000, $delimitadorDetectado)) !== false) {
                $fila++;

                // Saltar filas vacías
                if (empty(array_filter($linea))) {
                    continue;
                }

                if ($fila == 4) {
                    $cabeceras = $linea;
                    // Limpiar cabeceras para evitar problemas con caracteres especiales
                    $cabeceras = array_map(function ($header) {
                        $header = trim($header);
                        // Normalizar caracteres con tilde o especiales
                        $header = strtr($header, [
                            'á' => 'a',
                            'é' => 'e',
                            'í' => 'i',
                            'ó' => 'o',
                            'ú' => 'u',
                            'ñ' => 'n',
                            'Á' => 'a',
                            'É' => 'e',
                            'Í' => 'i',
                            'Ó' => 'o',
                            'Ú' => 'u',
                            'Ñ' => 'n'
                        ]);
                        $header = strtolower($header);
                        $header = preg_replace('/\s+/', '_', $header);       // Reemplaza espacios por _
                        $header = preg_replace('/[^a-z0-9_]/', '', $header); // Elimina otros caracteres

                        return $header;
                    }, $cabeceras);

                    continue;
                }

                // Leer datos solo si ya tenemos cabeceras y las columnas coinciden
                if ($fila > 4 && count($cabeceras) === count($linea)) {
                    // Limpiar valores para evitar caracteres problemáticos
                    $linea = array_map(function ($value) {
                        $value = trim($value);
                        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
                        return $value;
                    }, $linea);

                    $datos[] = array_combine($cabeceras, $linea);
                }
            }
            fclose($handle);
        }

        // 3. Procesar e insertar los datos en la base de datos
        foreach ($datos as $fila) {
            $proveedorId = $fila['proveedor'] ?? '';
            $nombreProveedor = $fila['nombre_del_proveedor'] ?? '';
            $materialCodigo = $fila['material'] ?? '';
            $jerarquia = $fila['jerarqua_product'] ?? '';
            $descripcionMaterial = $fila['descripcin_de_material'] ?? '';
            $mes = $fila['mes'] ?? '';
            $totalKgRaw = $fila['total_kg'] ?? '';

            // Convertir total_kg a float (soportar coma decimal y punto miles)
            $totalKg = floatval(str_replace(',', '.', str_replace('.', '', $totalKgRaw)));

            if (empty($proveedorId) || empty($materialCodigo)) {
                // Saltar filas sin info crítica
                continue;
            }

            // Buscar o crear proveedor
            $proveedor = Proveedor::firstOrCreate(
                ['id_proveedor' => $proveedorId],
                ['nombre_proveedor' => $nombreProveedor]
            );

            // Buscar o crear material
            $material = Material::firstOrCreate(
                ['codigo' => $materialCodigo],
                [
                    'jerarquia' => $jerarquia,
                    'descripcion' => $descripcionMaterial,
                    'proveedor_id' => $proveedor->id_proveedor,
                ]
            );

            // Insertar o actualizar kilos (descomenta y adapta según tu modelo)
            /*
        $año = date('Y');
        MaterialKilo::updateOrCreate(
            [
                'material_id' => $material->id,
                'proveedor_id' => $proveedor->id_proveedor,
                'mes' => $mes,
                'año' => $año,
            ],
            [
                'total_kg' => $totalKg,
            ]
        );
        */
        }

        return back()->with('success', 'Archivo importado correctamente.');
    }
}
