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
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use Exception;

class ChunkReadFilter implements IReadFilter
{
    private $startRow;
    private $endRow;

    public function __construct($startRow, $endRow)
    {
        $this->startRow = $startRow;
        $this->endRow = $endRow;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        if ($row >= $this->startRow && $row <= $this->endRow) {
            return true;
        }
        return false;
    }
}

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
        try {
            $proveedor = new Proveedor();
            $proveedor->id_proveedor = $request->id_proveedor;
            $proveedor->nombre_proveedor = $request->nombre_proveedor;
            $proveedor->save();
            return redirect()->back()->with('success', 'Proveedor creado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) { // Duplicado clave primaria
                return redirect()->back()->with('error', 'El ID de proveedor ya existe.');
            }
            return redirect()->back()->with('error', 'Error al crear proveedor: ' . $e->getMessage());
        }
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
        $proveedorId = $request->input('id');
        $proveedor = Proveedor::find($proveedorId);
        if (!$proveedor) {
            return redirect()->back()->with('error', 'Proveedor no encontrado.');
        }

        // Verificar si el proveedor tiene materiales asociados y borrarlos tambien
        $materiales = Material::where('proveedor_id', $proveedor->id_proveedor)->get();
        if ($materiales->isNotEmpty()) {
            foreach ($materiales as $material) {
                // Eliminar registros de MaterialKilo asociados
                MaterialKilo::where('codigo_material', $material->codigo)->delete();
                // Eliminar el material
                $material->delete();
            }
        }
    
        $proveedor->delete();
        return redirect()->back()->with('success', 'Proveedor eliminado correctamente.');

    }

    public function importarArchivo(Request $request)
    {
    // Aumentar límites para procesar archivos grandes
    ini_set('memory_limit', '1024M');
    // Quitar límite de tiempo para evitar errores con archivos grandes
    ini_set('max_execution_time', 0); // 0 = ilimitado
        
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
    // Generar ID de import para seguimiento de progreso
    $importId = uniqid('import_', true);
    $progressPath = storage_path('app/import_progress_' . $importId . '.json');
    // Inicializar progreso
    file_put_contents($progressPath, json_encode(['status' => 'started', 'processed' => 0, 'total' => 0, 'percent' => 0, 'id' => $importId]));
        Log::info('Ruta real del archivo: ' . $path);
        $importType = $request->input('import_type', 'general');
        Log::info('Tipo de importación solicitado: ' . $importType);
        $cabeceras = [];
            $datos = [];

            // Detectar si es archivo XLSX o CSV
            $extension = strtolower($archivo->getClientOriginalExtension());
            $mimeType = $archivo->getMimeType();
            
            // Verificar si es un Excel con extensión incorrecta
            $excelMimeTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel'
            ];
            
            if (in_array($mimeType, $excelMimeTypes) && $extension === 'csv') {
                Log::warning('Archivo con extensión .csv pero MIME type de Excel detectado. Procesando como Excel.');
                $extension = 'xlsx'; // Forzar procesamiento como Excel
            }
            
            Log::info('Procesando archivo: ' . $archivo->getClientOriginalName() . ' - Extensión: ' . $extension . ' - MIME: ' . $mimeType);
            
            if ($importType === 'proveedores') {
                // Importar solo proveedores desde la tercera hoja (LISTADO GENERAL) - columnas F (id) y G (nombre)
                try {
                    Log::info('Iniciando importación de proveedores desde XLSX/CSV');
                    if ($extension === 'xlsx') {
                        if (!file_exists($path) || !is_readable($path)) {
                            throw new Exception("El archivo no existe o no es legible: " . $path);
                        }
                        // Leer la hoja LISTADO GENERAL o la tercera hoja en chunks para evitar cargar todo en memoria
                        $reader = IOFactory::createReader('Xlsx');
                        $sheetIndex = null;
                        // identificar hoja por nombre primero usando reader ligero
                        $xlsxReader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                        $sheetNames = $xlsxReader->listWorksheetNames($path);
                        foreach ($sheetNames as $idx => $name) {
                            if (trim(strtoupper($name)) === 'LISTADO GENERAL') {
                                $sheetIndex = $idx;
                                break;
                            }
                        }
                        if ($sheetIndex === null) {
                            $sheetIndex = 2; // tercera hoja por defecto
                        }
                        $sheetInfos = $xlsxReader->listWorksheetInfo($path);
                        $highestRow = isset($sheetInfos[$sheetIndex]['totalRows']) ? $sheetInfos[$sheetIndex]['totalRows'] : ($sheetInfos[0]['totalRows'] ?? 0);

                        $created = 0; $skipped = 0;
                        $chunkSize = 500; // filas por chunk
                        // actualizar total para progreso
                        @file_put_contents($progressPath, json_encode(['status' => 'reading_providers', 'processed' => 0, 'total' => max(0, $highestRow - 9), 'percent' => 0, 'id' => $importId]));
                        for ($start = 10; $start <= $highestRow; $start += $chunkSize) {
                            $end = min($start + $chunkSize - 1, $highestRow);
                            $chunkFilter = new ChunkReadFilter($start, $end);
                            $reader->setReadFilter($chunkFilter);
                            $reader->setReadDataOnly(true);
                            $reader->setLoadSheetsOnly($sheetNames[$sheetIndex]);
                            $spreadsheetChunk = $reader->load($path);
                            $ws = $spreadsheetChunk->getActiveSheet();
                            for ($row = $start; $row <= $end; $row++) {
                                $provRaw = $ws->getCell('F' . $row)->getCalculatedValue();
                                $nameRaw = $ws->getCell('G' . $row)->getCalculatedValue();
                                $provId = trim((string) ($provRaw ?? ''));
                                $provName = trim((string) ($nameRaw ?? ''));
                                if ($provId === '' && $provName === '') continue;
                                if (!is_numeric($provId)) { Log::warning("Fila {$row} ignorada: id_proveedor no numérico ('{$provId}')"); $skipped++; continue; }
                                $id = intval($provId);
                                $exists = Proveedor::find($id);
                                if ($exists) { $skipped++; continue; }
                                Proveedor::firstOrCreate(['id_proveedor' => $id], ['nombre_proveedor' => $provName]);
                                $created++;
                            }
                            $spreadsheetChunk->disconnectWorksheets();
                            unset($spreadsheetChunk);
                            gc_collect_cycles();
                            // actualizar progreso por chunk
                            $processedSoFar = min($end, $highestRow) - 9; // since we start at row 10
                            $totalRows = max(0, $highestRow - 9);
                            $percent = $totalRows > 0 ? intval(($processedSoFar / $totalRows) * 100) : 100;
                            @file_put_contents($progressPath, json_encode(['status' => 'reading_providers', 'processed' => $processedSoFar, 'total' => $totalRows, 'percent' => $percent, 'id' => $importId]));
                            Log::info("Import progress ({$importId}): {$processedSoFar}/{$totalRows} ({$percent}%)");
                        }
                        Log::info("Importación proveedores completada. Creados: {$created}, Omitidos: {$skipped}");
                        return back()->with('success', "Importación proveedores completada. Creados: {$created}, Omitidos: {$skipped}");
                    } else {
                        // CSV/TXT fallback: leer columnas F (índice 5) y G (índice 6)
                        $delimitadores = [',', ';', "\t"];
                        $delimitadorDetectado = null;
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
                        $maxCount = 0;
                        foreach ($delimitadores as $delim) {
                            $count = substr_count($linea4, $delim);
                            if ($count > $maxCount) { $maxCount = $count; $delimitadorDetectado = $delim; }
                        }
                        if (!$delimitadorDetectado) {
                            return back()->withErrors(['archivo' => 'No se pudo detectar el delimitador del archivo CSV/TXT.']);
                        }
                        if (($handle = fopen($path, 'r')) !== false) {
                            $created = 0; $skipped = 0;
                            $currentRow = 0;
                            while (($linea = fgetcsv($handle, 1000, $delimitadorDetectado)) !== false) {
                                $currentRow++;
                                if ($currentRow < 10) continue; // empezar en la fila 10
                                // Ignorar filas completamente vacías
                                if (empty(array_filter($linea))) continue;
                                $provId = isset($linea[5]) ? trim($linea[5]) : '';
                                $provName = isset($linea[6]) ? trim($linea[6]) : '';
                                if ($provId === '' && $provName === '') continue;
                                if (!is_numeric($provId)) { $skipped++; continue; }
                                $id = intval($provId);
                                $exists = Proveedor::find($id);
                                if ($exists) { $skipped++; continue; }
                                Proveedor::firstOrCreate(['id_proveedor' => $id], ['nombre_proveedor' => $provName]);
                                $created++;
                            }
                            fclose($handle);
                            Log::info("Importación proveedores CSV completada. Creados: {$created}, Omitidos: {$skipped}");
                            return back()->with('success', "Importación proveedores completada. Creados: {$created}, Omitidos: {$skipped}");
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Error en importación de proveedores: ' . $e->getMessage());
                    return back()->withErrors(['archivo' => 'Error al importar proveedores: ' . $e->getMessage()]);
                }
            }
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
                
                // Usar chunked reading para el archivo XLSX general
                $reader = IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(true);
                $xlsxReaderInfo = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $sheetNames = $xlsxReaderInfo->listWorksheetNames($path);
                $sheetToRead = $sheetNames[0] ?? null;
                $sheetInfos = $xlsxReaderInfo->listWorksheetInfo($path);
                $highestRow = isset($sheetInfos[0]['totalRows']) ? $sheetInfos[0]['totalRows'] : 0;
                $highestColumn = isset($sheetInfos[0]['lastColumnLetter']) ? $sheetInfos[0]['lastColumnLetter'] : 'Z';
                Log::info("Archivo XLSX detectado - Filas: " . $highestRow . ", Columnas: " . $highestColumn);

                $cabeceras = [];
                $datos = [];
                
                // PASO 1: Leer cabeceras de la fila 4 por separado
                $headerFilter = new ChunkReadFilter(4, 4);
                $reader->setReadFilter($headerFilter);
                $reader->setLoadSheetsOnly($sheetToRead);
                $headerSpreadsheet = $reader->load($path);
                $headerWs = $headerSpreadsheet->getActiveSheet();
                
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $valor = $headerWs->getCell($col . '4')->getCalculatedValue();
                    if (!empty($valor)) { $cabeceras[] = $valor; }
                }
                
                // Limpiar cabeceras
                $cabeceras_limpias = [];
                foreach ($cabeceras as $header) {
                    $header = trim($header);
                    $header = strtr($header, [
                        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
                        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ñ' => 'n'
                    ]);
                    $header = strtolower($header);
                    $header = preg_replace('/\s+/', '_', $header);
                    $header = preg_replace('/[^a-z0-9_]/', '', $header);
                    $cabeceras_limpias[] = $header;
                }
                $cabeceras = $cabeceras_limpias;
                
                // CORRECCIÓN CRÍTICA: Hacer cabeceras únicas para evitar pérdida de datos en array_combine
                $cabeceras_unicas = [];
                $contador = [];
                foreach ($cabeceras as $cabecera) {
                    if (!isset($contador[$cabecera])) {
                        $contador[$cabecera] = 0;
                        $cabeceras_unicas[] = $cabecera;
                    } else {
                        $contador[$cabecera]++;
                        $cabeceras_unicas[] = $cabecera . '_' . $contador[$cabecera];
                    }
                }
                $cabeceras = $cabeceras_unicas;
                
                $headerSpreadsheet->disconnectWorksheets();
                unset($headerSpreadsheet);
                
                Log::info("Cabeceras leídas de fila 4: " . json_encode($cabeceras));
                Log::info("Cabeceras ahora son únicas - Longitud: " . count($cabeceras));
                
                // PASO 2: Leer datos desde la fila 5
                $chunkSize = 500;
                @file_put_contents($progressPath, json_encode(['status' => 'reading_xlsx', 'processed' => 0, 'total' => max(0, $highestRow - 4), 'percent' => 0, 'id' => $importId]));
                
                for ($start = 5; $start <= $highestRow; $start += $chunkSize) {
                    $end = min($start + $chunkSize - 1, $highestRow);
                    $chunkFilter = new ChunkReadFilter($start, $end);
                    $reader->setReadFilter($chunkFilter);
                    $reader->setLoadSheetsOnly($sheetToRead);
                    $spreadsheetChunk = $reader->load($path);
                    $ws = $spreadsheetChunk->getActiveSheet();

                    // Leer filas del chunk (ya estamos desde fila 5 en adelante)
                    for ($row = $start; $row <= $end; $row++) {
                        $fila = [];
                        $filaVacia = true;
                        for ($col = 'A'; $col <= $highestColumn; $col++) {
                            $valor = $ws->getCell($col . $row)->getCalculatedValue();
                            $valor = trim($valor ?? '');
                            if (!empty($valor)) { $filaVacia = false; }
                            $fila[] = $valor;
                        }
                        
                        if (!$filaVacia) {
                            // Ajustar el tamaño del array si es necesario
                            if (count($fila) < count($cabeceras)) {
                                $fila = array_pad($fila, count($cabeceras), '');
                            } elseif (count($fila) > count($cabeceras)) {
                                $fila = array_slice($fila, 0, count($cabeceras));
                            }
                            
                            if (count($cabeceras) > 0) {
                                $datos[] = array_combine($cabeceras, $fila);
                                // Datos agregados silenciosamente para mejor rendimiento
                            }
                        }
                    }

                    $spreadsheetChunk->disconnectWorksheets();
                    unset($spreadsheetChunk);
                    gc_collect_cycles();
                    // actualizar progreso general
                    $processedSoFar = max(0, $end - 4); // rows processed starting at 5
                    $totalRows = max(0, $highestRow - 4);
                    $percent = $totalRows > 0 ? intval(($processedSoFar / $totalRows) * 100) : 100;
                    @file_put_contents($progressPath, json_encode(['status' => 'reading_xlsx', 'processed' => $processedSoFar, 'total' => $totalRows, 'percent' => $percent, 'id' => $importId]));
                    Log::info("Import progress ({$importId}): {$processedSoFar}/{$totalRows} ({$percent}%)");
                }
                Log::info('Total de filas de datos procesadas: ' . count($datos));} catch (Exception $e) {
                Log::error('Error al procesar archivo XLSX: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                @file_put_contents($progressPath, json_encode(['status' => 'error', 'message' => $e->getMessage(), 'id' => $importId]));
                return back()->withErrors(array('archivo' => 'Error al procesar el archivo XLSX: ' . $e->getMessage()));
            }        } else {
            // Procesar archivo CSV (lógica original)
            try {
                Log::info('Iniciando procesamiento de archivo CSV');
                $delimitadores = [',', ';', "\t"];
                $delimitadorDetectado = null;
                
                // 1. Leer la línea 4 para detectar el delimitador
                $lineas = [];
                $handle = fopen($path, 'r');
                if (!$handle) {
                    throw new Exception("No se pudo abrir el archivo CSV: " . $path);
                }
                
                Log::info('Leyendo primeras 5 líneas para detectar delimitador');
                for ($i = 0; $i < 5; $i++) {
                    $linea = fgets($handle);
                    if ($linea === false) break;
                    $lineas[] = $linea;
                    Log::info("Línea " . ($i + 1) . ": " . substr($linea, 0, 100) . "...");
                }
                fclose($handle);

                $linea4 = isset($lineas[3]) ? $lineas[3] : '';
                Log::info("Línea 4 para detección de delimitador: " . substr($linea4, 0, 200));

                // Detectar delimitador con más ocurrencias en la línea 4
                $maxCount = 0;
                foreach ($delimitadores as $delim) {
                    $count = substr_count($linea4, $delim);
                    Log::info("Delimitador '" . addslashes($delim) . "': {$count} ocurrencias");
                    if ($count > $maxCount) {
                        $maxCount = $count;
                        $delimitadorDetectado = $delim;
                    }
                }
                
                Log::info("Delimitador detectado: '" . addslashes($delimitadorDetectado) . "' con {$maxCount} ocurrencias");

                if (!$delimitadorDetectado) {
                    Log::error('No se pudo detectar el delimitador del archivo CSV');
                    return back()->withErrors(array('archivo' => 'No se pudo detectar el delimitador del archivo CSV.'));
                }
            } catch (Exception $e) {
                Log::error('Error en procesamiento inicial de CSV: ' . $e->getMessage());
                return back()->withErrors(array('archivo' => 'Error al procesar archivo CSV: ' . $e->getMessage()));
            }

            // 2. Leer el archivo CSV con el delimitador detectado
            try {
                Log::info('Abriendo archivo para lectura completa con delimitador: ' . addslashes($delimitadorDetectado));
                $handle = fopen($path, 'r');
                if (!$handle) {
                    throw new Exception("No se pudo abrir el archivo para lectura completa");
                }
                
                $fila = 0;
                $totalFilas = 0;
                while (($linea = fgetcsv($handle, 1000, $delimitadorDetectado)) !== false) {
                    $fila++;
                    $totalFilas++;

                    // Saltar filas vacías
                    if (empty(array_filter($linea))) {
                        Log::info("Fila {$fila} vacía, saltando");
                        continue;
                    }
                    
                    if ($fila <= 5) {
                        Log::info("Fila {$fila} - primeras 5 columnas: " . json_encode(array_slice($linea, 0, 5)));
                    }

                    if ($fila == 4) {
                        $cabeceras = $linea;
                        Log::info("Cabeceras encontradas en fila 4: " . json_encode($cabeceras));
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
                        
                        // Debug: mostrar las cabeceras detectadas
                        Log::info("Cabeceras detectadas en fila 4:");
                        foreach ($cabeceras as $index => $header) {
                            Log::info("  Posición {$index}: '{$header}'");
                        }

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
                Log::info("Archivo CSV procesado. Total filas leídas: {$totalFilas}, Filas de datos: " . count($datos));
                
            } catch (Exception $e) {
                Log::error('Error leyendo archivo CSV: ' . $e->getMessage());
                return back()->withErrors(array('archivo' => 'Error al leer archivo CSV: ' . $e->getMessage()));
            }
        }

        // 3. Procesar e insertar los datos en la base de datos
        Log::info('Iniciando procesamiento de ' . count($datos) . ' filas de datos');
        
        $procesadas = 0;
        $errores = 0;
          foreach ($datos as $index => $fila) {
            try {
                // Obtener datos usando posiciones directas del array para mayor confiabilidad
                $filaArray = array_values($fila);
                
                // Mapear columnas por posición según la estructura proporcionada
                $jerarquia = isset($filaArray[0]) ? trim($filaArray[0]) : ''; // Columna A
                $materialCodigo = isset($filaArray[1]) ? trim($filaArray[1]) : ''; // Columna B
                $descripcionMaterial = isset($filaArray[2]) ? trim($filaArray[2]) : ''; // Columna C
                $proveedorId = isset($filaArray[3]) ? trim($filaArray[3]) : ''; // Columna D
                $nombreProveedor = isset($filaArray[4]) ? trim($filaArray[4]) : ''; // Columna E
                
                // CORREGIDO: Obtener el mes de la posición 11 (columna L - segundo "mes")
                $mes = isset($filaArray[11]) ? trim($filaArray[11]) : ''; // Posición 11 - MES CORRECTO
                
                // Fallback: usar nombres de cabeceras si las posiciones fallan
                if (empty($proveedorId)) {
                    $proveedorId = isset($fila['proveedor']) ? trim($fila['proveedor']) : '';
                }
                if (empty($nombreProveedor)) {
                    $nombreProveedor = isset($fila['nombre_del_proveedor']) ? trim($fila['nombre_del_proveedor']) : '';
                }
                if (empty($materialCodigo)) {
                    $materialCodigo = isset($fila['material']) ? trim($fila['material']) : '';
                }
                if (empty($jerarquia)) {
                    $jerarquia = isset($fila['jerarqua_product']) ? trim($fila['jerarqua_product']) : '';
                }
                if (empty($descripcionMaterial)) {
                    $descripcionMaterial = isset($fila['descripcin_de_material']) ? trim($fila['descripcin_de_material']) : '';
                }
                
                // Debug eliminado para mejor rendimiento en archivos grandes
                
                // Fallback: buscar por nombre de cabecera si la posición directa no funciona
                if (empty($mes) && isset($cabeceras[11]) && isset($fila[$cabeceras[11]])) {
                    $mes = trim($fila[$cabeceras[11]]);
                }
                
                // Log básico de procesamiento
                // Procesando fila silenciosamente
                
                // Obtener otros campos por posición
                $ce = isset($filaArray[5]) ? trim($filaArray[5]) : ''; // Columna F
                $ctd_emdev = isset($filaArray[7]) ? trim($filaArray[7]) : ''; // Columna H
                $umb = isset($filaArray[8]) ? trim($filaArray[8]) : ''; // Columna I
                $valor_emdev = isset($filaArray[9]) ? trim($filaArray[9]) : ''; // Columna J
                $factor_conversin = isset($filaArray[12]) ? trim($filaArray[12]) : ''; // Columna M
                $totalKgRaw = isset($filaArray[13]) ? trim($filaArray[13]) : ''; // Posición 13 - Columna N (Total kg)
                
                // Fallbacks usando nombres de cabeceras
                if (empty($totalKgRaw)) {
                    $totalKgRaw = isset($fila['total_kg']) ? trim($fila['total_kg']) : '';
                }
                if (empty($ctd_emdev)) {
                    $ctd_emdev = isset($fila['ctd_emdev']) ? trim($fila['ctd_emdev']) : '';
                }
                if (empty($umb)) {
                    $umb = isset($fila['umb']) ? trim($fila['umb']) : '';
                }
                if (empty($ce)) {
                    $ce = isset($fila['ce']) ? trim($fila['ce']) : '';
                }
                if (empty($valor_emdev)) {
                    $valor_emdev = isset($fila['valor_emdev']) ? trim($fila['valor_emdev']) : '';
                }
                if (empty($factor_conversin)) {
                    $factor_conversin = isset($fila['factor_conversin']) ? trim($fila['factor_conversin']) : '';
                }

                // Convertir total_kg a float (soportar coma decimal y punto miles)
                $totalKg = floatval(str_replace(',', '.', str_replace('.', '', $totalKgRaw)));
                
                // Validación crítica con logging detallado
                if (empty($proveedorId) || empty($materialCodigo)) {
                  //  Log::warning("FILA {$index} SALTADA - Proveedor: '{$proveedorId}' | Material: '{$materialCodigo}' - FALTAN DATOS CRÍTICOS");
                    continue;
                }
                
                if (empty($mes)) {
                 //   Log::warning("FILA {$index} SALTADA - Mes vacío: '{$mes}'");
                    continue;
                }
                
                // Fila válida - procesando silenciosamente

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
               // Log::info("Material procesado: Código={$material->codigo}");

                $año = date('Y');
              //  Log::info("Preparando para MaterialKilo: material={$materialCodigo}, proveedor={$proveedor->id_proveedor}, mes={$mes}, año={$año}");

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

                // Verificar si ya existe un registro con el mismo codigo_material, mes y año
                $existingMaterialKilo = MaterialKilo::where('codigo_material', $materialCodigo)
                    ->where('mes', $mes)
                    ->where('año', $año)
                    ->first();

                // Solo crear si no existe un registro con el mismo material, mes y año
                if (!$existingMaterialKilo) {
                    MaterialKilo::Create([
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
                    ]);
                //    Log::info("MaterialKilo creado: material={$materialCodigo}, mes={$mes}, año={$año}");
                } else {
                  //  Log::info("MaterialKilo ya existe: material={$materialCodigo}, mes={$mes}, año={$año} - Omitido");
                }
                
                $procesadas++;
                // actualizar progreso de inserción en disco
                $percentInsert = isset($procesadas) && isset($totalRows) && $totalRows>0 ? intval(($procesadas / $totalRows) * 100) : 0;
                @file_put_contents($progressPath, json_encode(['status' => 'inserting', 'processed' => $procesadas, 'total' => $totalRows ?? count($datos), 'percent' => $percentInsert, 'id' => $importId]));
                
            } catch (Exception $e) {
                $errores++;
              //  Log::error("Error procesando fila {$index}: " . $e->getMessage());
              //  Log::error("Datos de la fila: " . json_encode($fila));
            }
        }
          Log::info("Procesamiento completado. Filas procesadas: {$procesadas}, Errores: {$errores}");
        
    // marcar completado y limpiar archivo de progreso
    @file_put_contents($progressPath, json_encode(['status' => 'completed', 'processed' => $procesadas, 'total' => $totalRows ?? count($datos), 'percent' => 100, 'id' => $importId]));
    Log::info('=== FIN IMPORTAR ARCHIVO EXITOSO ===');
    return back()->with('success', "Archivo importado correctamente. Filas procesadas: {$procesadas}")->with('import_id', $importId);
        
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

    // Endpoint para consultar progreso de import
    public function importProgress(Request $request)
    {
        $importId = $request->query('import_id');
        if (!$importId) {
            return response()->json(['error' => 'import_id required'], 400);
        }
        $progressPath = storage_path('app/import_progress_' . $importId . '.json');
        if (!file_exists($progressPath)) {
            return response()->json(['status' => 'not_found'], 404);
        }
        $content = @file_get_contents($progressPath);
        if ($content === false) {
            return response()->json(['status' => 'error_reading'], 500);
        }
        $data = json_decode($content, true);
        return response()->json($data ?: ['status' => 'unknown']);
    }
}
