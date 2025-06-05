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
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

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


    public function destroy(Request $request)
    {
        //  $user = User::findOrFail($request->input('id'));
        // $user->delete();
        // return redirect()->back()->with('success', 'Usuario eliminado correctamente.');

    }    public function importarArchivo(Request $request)
    {
        // Aumentar límites para procesar archivos grandes
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300); // 5 minutos
        
        Log::info('=== INICIO IMPORTAR ARCHIVO ===');
        Log::info('Método: ' . $request->method());
        Log::info('Content-Type: ' . $request->header('Content-Type'));
        Log::info('Archivos recibidos: ' . json_encode($request->allFiles()));
        Log::info('Todos los inputs: ' . json_encode($request->all()));
        
        // Verificar límites de PHP
        Log::info('Configuración PHP:');
        Log::info('- upload_max_filesize: ' . ini_get('upload_max_filesize'));
        Log::info('- post_max_size: ' . ini_get('post_max_size'));
        Log::info('- max_file_uploads: ' . ini_get('max_file_uploads'));
        Log::info('- memory_limit: ' . ini_get('memory_limit'));
        Log::info('- max_execution_time: ' . ini_get('max_execution_time'));
        
        // Verificar si hay errores de upload
        if (!$request->hasFile('archivo')) {
            Log::error('No se recibió ningún archivo en el campo "archivo"');
            return back()->withErrors(['archivo' => 'No se recibió ningún archivo.']);
        }
        
        $archivo = $request->file('archivo');
        
        Log::info('Detalles del archivo recibido:');
        Log::info('- Nombre original: ' . $archivo->getClientOriginalName());
        Log::info('- Extensión: ' . $archivo->getClientOriginalExtension());
        Log::info('- Tamaño: ' . $archivo->getSize() . ' bytes');
        Log::info('- MIME type: ' . $archivo->getMimeType());
        Log::info('- Es válido: ' . ($archivo->isValid() ? 'true' : 'false'));
        
        if (!$archivo->isValid()) {
            Log::error('Error en el archivo: ' . $archivo->getError());
            Log::error('Código de error: ' . $archivo->getErrorMessage());
            return back()->withErrors(['archivo' => 'Error en el archivo: ' . $archivo->getErrorMessage()]);
        }
        
        try {
            // Validación manual más específica
            $extension = strtolower($archivo->getClientOriginalExtension());
            $allowedExtensions = ['csv', 'txt', 'xlsx'];
            
            if (!in_array($extension, $allowedExtensions)) {
                Log::error('Extensión no permitida: ' . $extension);
                return back()->withErrors(['archivo' => 'Extensión de archivo no permitida. Use: ' . implode(', ', $allowedExtensions)]);
            }
            
            Log::info('Validación de extensión pasada: ' . $extension);
            
            // Validación de MIME type para XLSX
            if ($extension === 'xlsx') {
                $allowedMimes = [
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'application/octet-stream'
                ];
                
                if (!in_array($archivo->getMimeType(), $allowedMimes)) {
                    Log::warning('MIME type no reconocido para XLSX: ' . $archivo->getMimeType() . ', pero continuando...');
                }
            }
            
            Log::info('Validación pasada correctamente');

        $path = $archivo->getRealPath();
        Log::info('Ruta real del archivo: ' . $path);            $cabeceras = [];
            $datos = [];

            // Detectar si es archivo XLSX o CSV
            $extension = strtolower($archivo->getClientOriginalExtension());
            Log::info('Procesando archivo: ' . $archivo->getClientOriginalName() . ' - Extensión: ' . $extension);
            
            if ($extension === 'xlsx') {// Procesar archivo XLSX
            try {
                Log::info('Iniciando procesamiento de archivo XLSX');
                  // Verificar que el archivo existe y es legible
                if (!file_exists($path) || !is_readable($path)) {
                    throw new Exception("El archivo no existe o no es legible: " . $path);
                }
                
                // Verificar que PhpSpreadsheet puede detectar el tipo de archivo
                $inputFileType = IOFactory::identify($path);
                Log::info("Tipo de archivo detectado: " . $inputFileType);
                
                if ($inputFileType !== 'Xlsx') {
                    throw new Exception("El archivo no es un XLSX válido. Tipo detectado: " . $inputFileType);
                }
                
                $spreadsheet = IOFactory::load($path);
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();                Log::info("Archivo XLSX cargado - Filas: " . $highestRow . ", Columnas: " . $highestColumn);                
                // Para XLSX, las cabeceras están en la fila 4
                $cabeceras = [];
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $valor = $worksheet->getCell($col . '4')->getCalculatedValue();
                    if (!empty($valor)) {
                        $cabeceras[] = $valor;
                    }
                }

                Log::info('Cabeceras encontradas RAW: ' . json_encode($cabeceras));
                Log::info('Número de cabeceras: ' . count($cabeceras));
                Log::info('Columnas hasta: ' . $highestColumn);

                // Limpiar cabeceras para XLSX igual que para CSV
                $cabeceras_limpias = [];
                foreach ($cabeceras as $header) {
                    $header = trim($header);
                    // Normalizar caracteres con tilde o especiales
                    $header = strtr($header, [
                        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
                        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ñ' => 'n'
                    ]);
                    $header = strtolower($header);
                    $header = preg_replace('/\s+/', '_', $header);       // Reemplaza espacios por _
                    $header = preg_replace('/[^a-z0-9_]/', '', $header); // Elimina otros caracteres
                    $cabeceras_limpias[] = $header;
                }
                $cabeceras = $cabeceras_limpias;

                Log::info('Cabeceras procesadas: ' . json_encode($cabeceras));

                // Verificar que tenemos cabeceras válidas
                if (empty($cabeceras)) {
                    throw new Exception("No se encontraron cabeceras válidas en la fila 4 del archivo XLSX");
                }                // Leer datos desde la fila 5 en adelante
                for ($row = 5; $row <= $highestRow; $row++) {
                    $fila = [];
                    $filaVacia = true;
                    
                    for ($col = 'A'; $col <= $highestColumn; $col++) {
                        $valor = $worksheet->getCell($col . $row)->getCalculatedValue();
                        $valor = trim($valor ?? '');
                        if (!empty($valor)) {
                            $filaVacia = false;
                        }
                        $fila[] = $valor;
                    }

                    // Solo agregar si la fila no está vacía y tiene el mismo número de columnas que cabeceras
                    if (!$filaVacia && count($fila) === count($cabeceras)) {
                        $datos[] = array_combine($cabeceras, $fila);
                    }
                }

                Log::info('Total de filas de datos procesadas: ' . count($datos));} catch (Exception $e) {
                Log::error('Error al procesar archivo XLSX: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                return back()->withErrors(array('archivo' => 'Error al procesar el archivo XLSX: ' . $e->getMessage()));
            }        } else {
            // Procesar archivo CSV (lógica original)
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

            $linea4 = isset($lineas[3]) ? $lineas[3] : '';

            // Detectar delimitador con más ocurrencias en la línea 4
            $maxCount = 0;
            foreach ($delimitadores as $delim) {
                $count = substr_count($linea4, $delim);
                if ($count > $maxCount) {
                    $maxCount = $count;
                    $delimitadorDetectado = $delim;
                }
            }            if (!$delimitadorDetectado) {
                return back()->withErrors(array('archivo' => 'No se pudo detectar el delimitador del archivo.'));
            }

            // 2. Leer el archivo CSV con el delimitador detectado
            if (($handle = fopen($path, 'r')) !== false) {
                $fila = 0;
                while (($linea = fgetcsv($handle, 1000, $delimitadorDetectado)) !== false) {
                    $fila++;

                    // Saltar filas vacías
                    if (empty(array_filter($linea))) {
                        continue;
                    }                    if ($fila == 4) {
                        $cabeceras = $linea;
                        // Limpiar cabeceras para evitar problemas con caracteres especiales
                        $cabeceras_limpias = array();
                        foreach ($cabeceras as $header) {
                            $header = trim($header);
                            // Normalizar caracteres con tilde o especiales
                            $header = strtr($header, array(
                                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
                                'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ñ' => 'n'
                            ));
                            $header = strtolower($header);
                            $header = preg_replace('/\s+/', '_', $header);       // Reemplaza espacios por _
                            $header = preg_replace('/[^a-z0-9_]/', '', $header); // Elimina otros caracteres
                            $cabeceras_limpias[] = $header;
                        }
                        $cabeceras = $cabeceras_limpias;

                        continue;
                    }                    // Leer datos solo si ya tenemos cabeceras y las columnas coinciden
                    if ($fila > 4 && count($cabeceras) === count($linea)) {
                        // Limpiar valores para evitar caracteres problemáticos
                        $linea_limpia = array();
                        foreach ($linea as $value) {
                            $value = trim($value);
                            // Convierte de ISO-8859-1 o Windows-1252 a UTF-8
                            $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                            // Elimina caracteres de control
                            $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
                            $linea_limpia[] = $value;
                        }

                        $datos[] = array_combine($cabeceras, $linea_limpia);
                    }
                }
                fclose($handle);
            }
        }        // 3. Procesar e insertar los datos en la base de datos
        Log::info('Iniciando procesamiento de ' . count($datos) . ' filas de datos');
        
        $procesadas = 0;
        $errores = 0;
          foreach ($datos as $index => $fila) {
            try {                $proveedorId = isset($fila['proveedor']) ? $fila['proveedor'] : '';
                $nombreProveedor = isset($fila['nombre_del_proveedor']) ? $fila['nombre_del_proveedor'] : '';
                $materialCodigo = isset($fila['material']) ? $fila['material'] : '';
                $jerarquia = isset($fila['jerarqua_product']) ? $fila['jerarqua_product'] : '';
                $descripcionMaterial = isset($fila['descripcin_de_material']) ? $fila['descripcin_de_material'] : '';
                
                // Buscar el mes de manera más segura
                $mes = '';
                if (isset($cabeceras[11]) && isset($fila[$cabeceras[11]])) {
                    $mes = $fila[$cabeceras[11]];
                } else {
                    // Buscar por nombres alternativos de mes
                    foreach ($fila as $key => $value) {
                        if (strpos(strtolower($key), 'mes') !== false) {
                            $mes = $value;
                            break;
                        }
                    }
                }
                  $totalKgRaw = isset($fila['total_kg']) ? $fila['total_kg'] : '';
                $ctd_emdev = isset($fila['ctd_emdev']) ? $fila['ctd_emdev'] : '';
                $umb = isset($fila['umb']) ? $fila['umb'] : '';
                $ce = isset($fila['ce']) ? $fila['ce'] : '';
                $valor_emdev = isset($fila['valor_emdev']) ? $fila['valor_emdev'] : '';
                $factor_conversin = isset($fila['factor_conversin']) ? $fila['factor_conversin'] : '';

                // Convertir total_kg a float (soportar coma decimal y punto miles)
                $totalKg = floatval(str_replace(',', '.', str_replace('.', '', $totalKgRaw)));
                
                if (empty($proveedorId) || empty($materialCodigo)) {
                    // Saltar filas sin info crítica
                    Log::warning("Fila {$index} saltada: falta proveedor o material");
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

                $año = date('Y');

                //limpiar valores decimales
                $valor_emdev_decimales = str_replace('.', '', $valor_emdev); 
                //  Reemplazar la coma por punto (separador decimal)
                $valor_emdev_convertido = str_replace(',', '.', $valor_emdev_decimales); 
                $valor_emdev_final = (float) $valor_emdev_convertido; 

                //valor de factor conversion
                $factor_conversin_decimales = str_replace('.', '', $factor_conversin);
                //  Reemplazar la coma por punto (separador decimal)
                $factor_conversin_convertido = str_replace(',', '.', $factor_conversin_decimales);
                $factor_conversin = (float) $factor_conversin_convertido;

                //valor de ctd_emdev
                $ctd_emdev_decimales = str_replace('.', '', $ctd_emdev);
                //  Reemplazar la coma por punto (separador decimal)
                $ctd_emdev_convertido = str_replace(',', '.', $ctd_emdev_decimales);
                $ctd_emdev = (float) $ctd_emdev_convertido;

                MaterialKilo::Create(
                    [
                        'codigo_material' => $materialCodigo,
                        'proveedor_id' => $proveedor->id_proveedor,
                        'mes' => $mes,
                        'año' => $año,
                        'total_kg' => $totalKg,
                        'ctd_emdev' => $ctd_emdev,
                        'umb' => $umb,
                        'ce' => $ce,
                        'valor_emdev' => $valor_emdev_final,
                        'factor_conversion' => $factor_conversin,
                    ]
                );
                
                $procesadas++;
                
            } catch (Exception $e) {
                $errores++;
                Log::error("Error procesando fila {$index}: " . $e->getMessage());
                Log::error("Datos de la fila: " . json_encode($fila));
            }
        }
          Log::info("Procesamiento completado. Filas procesadas: {$procesadas}, Errores: {$errores}");
        
        Log::info('=== FIN IMPORTAR ARCHIVO EXITOSO ===');
        return back()->with('success', "Archivo importado correctamente. Filas procesadas: {$procesadas}");
        
        } catch (Exception $e) {
            Log::error('Error general en importarArchivo: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== FIN IMPORTAR ARCHIVO CON ERROR ===');
            return back()->withErrors(['archivo' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    public function testUpload(Request $request)
    {
        Log::info('=== TEST UPLOAD ===');
        Log::info('Método: ' . $request->method());
        Log::info('Content-Type: ' . $request->header('Content-Type'));
        Log::info('Files: ' . json_encode($request->allFiles()));
        Log::info('All input: ' . json_encode($request->all()));
        
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            Log::info('Archivo detectado:');
            Log::info('- Original name: ' . $file->getClientOriginalName());
            Log::info('- Extension: ' . $file->getClientOriginalExtension());
            Log::info('- Size: ' . $file->getSize() . ' bytes');
            Log::info('- MIME type: ' . $file->getMimeType());
            Log::info('- Path: ' . $file->getRealPath());
            Log::info('- Is valid: ' . ($file->isValid() ? 'true' : 'false'));
            
            if (!$file->isValid()) {
                Log::error('File error: ' . $file->getError());
            }
        } else {
            Log::warning('No se detectó archivo');
        }
        
        return response()->json([
            'status' => 'ok',
            'message' => 'Check logs for details'
        ]);
    }
}
