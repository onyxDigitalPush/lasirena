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
use Illuminate\Support\Facades\DB;
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
            $proveedor->familia = $request->familia;
            $proveedor->subfamilia = $request->subfamilia;
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
        $proveedor->familia = $request->input('familia_edit');
        $proveedor->subfamilia = $request->input('subfamilia_edit');
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
        
        // Verificar si hay errores de upload
        if (!$request->hasFile('archivo')) {
            return redirect('/proveedores')->withErrors(['archivo' => 'No se recibió ningún archivo.']);
        }
        
        $archivo = $request->file('archivo');
        
        if (!$archivo->isValid()) {
            return redirect('/proveedores')->withErrors(['archivo' => 'Error en el archivo: ' . $archivo->getErrorMessage()]);
        }
        
        try {
            // Validación manual más específica
            $extension = strtolower($archivo->getClientOriginalExtension());
            $allowedExtensions = ['csv', 'txt', 'xlsx'];
            
            if (!in_array($extension, $allowedExtensions)) {
                return redirect('/proveedores')->withErrors(['archivo' => 'Extensión de archivo no permitida. Use: ' . implode(', ', $allowedExtensions)]);
            }
            
            // Validación de MIME type para XLSX
            if ($extension === 'xlsx') {
                $allowedMimes = [
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'application/octet-stream'
                ];
                
                if (!in_array($archivo->getMimeType(), $allowedMimes)) {
                    // Continuar de todos modos, algunos servidores reportan MIME types incorrectos
                }
            }

    $path = $archivo->getRealPath();
    // Generar ID de import para seguimiento de progreso
    $importId = uniqid('import_', true);
    $progressPath = storage_path('app/import_progress_' . $importId . '.json');
    // Inicializar progreso
    file_put_contents($progressPath, json_encode(['status' => 'started', 'processed' => 0, 'total' => 0, 'percent' => 0, 'id' => $importId]));
        
        $importType = $request->input('import_type', 'general');
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
                $extension = 'xlsx'; // Forzar procesamiento como Excel
            }
            
            if ($importType === 'proveedores') {
                // Importar solo proveedores desde la tercera hoja (LISTADO GENERAL) - columnas F (id) y G (nombre)
                try {
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
                        return view('proveedores.import_complete', ['message' => "Importación proveedores completada. Creados: {$created}, Omitidos: {$skipped}"]);
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
                            return redirect('/proveedores')->withErrors(['archivo' => 'No se pudo detectar el delimitador del archivo CSV/TXT.']);
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
                            return view('proveedores.import_complete', ['message' => "Importación proveedores completada. Creados: {$created}, Omitidos: {$skipped}"]);
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Error en importación de proveedores: ' . $e->getMessage());
                    return redirect('/proveedores')->withErrors(['archivo' => 'Error al importar proveedores: ' . $e->getMessage()]);
                }
            }

            // Importación sin factor de conversión (formato especial con columnas desorganizadas)
            if ($importType === 'sin_fconversion') {
                try {
                    if ($extension === 'xlsx') {
                        if (!file_exists($path) || !is_readable($path)) {
                            throw new Exception("El archivo no existe o no es legible: " . $path);
                        }

                        $reader = IOFactory::createReader('Xlsx');
                        $reader->setReadDataOnly(true);
                        
                        $spreadsheet = $reader->load($path);
                        $sheet = $spreadsheet->getActiveSheet();
                        $highestRow = $sheet->getHighestRow();
                        
                        $created = 0;
                        $skipped = 0;
                        $errors = [];
                        $filasVaciasConsecutivas = 0;
                        $maxFilasVacias = 3; // Detener si encuentra 3 filas vacías seguidas
                        
                        // Columnas esperadas (índices de letras):
                        // A=ML, B=Material(código), C=Jerarquía, D=Descripción, E=Proveedor(id), 
                        // F=Nombre proveedor, G=Ce, H=Mes(formato 9.2025), I=Ctd.EM-DEV, J=UMB, K=Valor EM-DEV
                        
                        for ($row = 2; $row <= $highestRow; $row++) {
                            try {
                                // Leer SOLO las columnas que necesitamos (B-K)
                                $codigo_material = trim((string)$sheet->getCell('B' . $row)->getCalculatedValue());
                                $jerarquia = trim((string)$sheet->getCell('C' . $row)->getCalculatedValue());
                                $descripcion = trim((string)$sheet->getCell('D' . $row)->getCalculatedValue());
                                $id_proveedor = trim((string)$sheet->getCell('E' . $row)->getCalculatedValue());
                                $nombre_proveedor = trim((string)$sheet->getCell('F' . $row)->getCalculatedValue());
                                $ce = trim((string)$sheet->getCell('G' . $row)->getCalculatedValue());
                                $mes_raw = trim((string)$sheet->getCell('H' . $row)->getCalculatedValue());
                                $ctd_emdev = $sheet->getCell('I' . $row)->getCalculatedValue();
                                $umb = trim((string)$sheet->getCell('J' . $row)->getCalculatedValue());
                                $valor_emdev = $sheet->getCell('K' . $row)->getCalculatedValue();
                                
                                // Verificar si la fila está completamente vacía (todas las columnas B-K vacías)
                                $filaVacia = empty($codigo_material) && empty($jerarquia) && empty($descripcion) && 
                                             empty($id_proveedor) && empty($nombre_proveedor) && empty($ce) && 
                                             empty($mes_raw) && empty($ctd_emdev) && empty($umb) && empty($valor_emdev);
                                
                                if ($filaVacia) {
                                    $filasVaciasConsecutivas++;
                                    // Si encontramos 3 filas vacías consecutivas, detener el procesamiento
                                    if ($filasVaciasConsecutivas >= $maxFilasVacias) {
                                        break;
                                    }
                                    continue;
                                } else {
                                    // Resetear contador si encontramos una fila con datos
                                    $filasVaciasConsecutivas = 0;
                                }
                                
                                // Saltar filas sin datos mínimos requeridos
                                if (empty($codigo_material) && empty($id_proveedor)) {
                                    continue;
                                }
                                
                                // Parsear mes en formato "09.2025" o "9.2025" o "09,2025" -> mes="09.2025", año=2025
                                // Acepta tanto punto (.) como coma (,) como separador
                                $mes = null;
                                $año = null;
                                $mes_formateado = null; // Para guardar en formato "MM.YYYY"
                                
                                if (preg_match('/^(\d{1,2})[.,](\d{4})$/', $mes_raw, $matches)) {
                                    $mes_numero = (int)$matches[1];
                                    $año = (int)$matches[2];
                                    // Formatear mes con cero a la izquierda: 9 -> 09
                                    $mes_formateado = str_pad($mes_numero, 2, '0', STR_PAD_LEFT) . '.' . $año;
                                    $mes = $mes_numero; // Para búsquedas/comparaciones numéricas
                                } else {
                                    Log::warning("Fila {$row}: formato de mes inválido '{$mes_raw}'");
                                    $errors[] = "Fila {$row}: mes inválido";
                                    $skipped++;
                                    continue;
                                }
                                
                                // Validar datos mínimos
                                if (empty($codigo_material) || empty($id_proveedor) || !$mes || !$año) {
                                    $skipped++;
                                    continue;
                                }
                                
                                // Crear o actualizar proveedor
                                Proveedor::firstOrCreate(
                                    ['id_proveedor' => $id_proveedor],
                                    ['nombre_proveedor' => $nombre_proveedor ?: 'Proveedor ' . $id_proveedor]
                                );
                                
                                // Buscar material en BD para obtener factor_conversion
                                $material_db = DB::table('materiales')
                                    ->where('codigo', $codigo_material)
                                    ->first();
                                
                                // Si no existe el material, crearlo
                                if (!$material_db) {
                                    DB::table('materiales')->insertOrIgnore([
                                        'codigo' => $codigo_material,
                                        'descripcion' => $descripcion,
                                        'jerarquia' => $jerarquia,
                                        'proveedor_id' => $id_proveedor,
                                        'factor_conversion' => null, // NULL hasta que se configure
                                        'created_at' => now(),
                                        'updated_at' => now()
                                    ]);
                                    
                                    // Volver a consultar para obtener el registro recién creado
                                    $material_db = DB::table('materiales')
                                        ->where('codigo', $codigo_material)
                                        ->first();
                                }
                                
                                // Calcular factor_conversion y total_kg
                                $factor_conversion = 0;
                                $total_kg = 0;
                                
                                // Si el material existe y tiene factor_conversion definido (incluso si es 0)
                                if ($material_db && isset($material_db->factor_conversion) && $material_db->factor_conversion !== null) {
                                    $factor_conversion = (float)$material_db->factor_conversion;
                                    // Solo calcular total_kg si el factor es mayor que 0
                                    if ($factor_conversion > 0) {
                                        $total_kg = (float)$ctd_emdev * $factor_conversion;
                                    }
                                }
                                // Si factor_conversion es NULL, se usa 0 (valor por defecto)
                                
                                // Insertar TODOS los registros sin verificar duplicados
                                // Pueden existir múltiples registros con el mismo material, proveedor, mes y año
                                // pero con diferentes valores en otras columnas
                                
                                try {
                                    DB::table('material_kilos')->insert([
                                        'codigo_material' => $codigo_material,
                                        'proveedor_id' => $id_proveedor,
                                        'ctd_emdev' => (float)$ctd_emdev,
                                        'umb' => $umb,
                                        'ce' => $ce,
                                        'valor_emdev' => (float)$valor_emdev,
                                        'factor_conversion' => $factor_conversion,
                                        'total_kg' => $total_kg,
                                        'mes' => $mes_formateado,  // Guardar en formato "MM.YYYY"
                                        'año' => $año,
                                        'created_at' => now(),
                                        'updated_at' => now()
                                    ]);
                                    
                                    $created++;
                                    
                                } catch (\Exception $insertEx) {
                                    $errors[] = "Fila {$row}: " . substr($insertEx->getMessage(), 0, 100);
                                    $skipped++;
                                }
                                
                            } catch (Exception $e) {
                                $errors[] = "Fila {$row}: " . substr($e->getMessage(), 0, 80);
                                $skipped++;
                            }
                        }
                        
                        $spreadsheet->disconnectWorksheets();
                        unset($spreadsheet);
                        gc_collect_cycles();
                        
                        $total_procesados = $created + $skipped;
                        $mensaje = "Importación completada. ✓ Insertados: {$created} de {$total_procesados} filas";
                        if ($skipped > 0) {
                            $mensaje .= " | ✗ Omitidos: {$skipped}";
                        }
                        if (count($errors) > 0 && count($errors) <= 5) {
                            $mensaje .= " | Errores: " . implode('; ', array_slice($errors, 0, 5));
                        } elseif (count($errors) > 5) {
                            $mensaje .= " | Ver log para detalles de " . count($errors) . " errores";
                        }
                        
                        return view('proveedores.import_complete', ['message' => $mensaje]);
                        
                    } else {
                        return redirect('/proveedores')->withErrors(['archivo' => 'Para importación sin factor de conversión, debe usar archivo XLSX']);
                    }
                } catch (Exception $e) {
                    Log::error('Error en importación sin factor de conversión: ' . $e->getMessage());
                    return redirect('/proveedores')->withErrors(['archivo' => 'Error al importar sin factor de conversión: ' . $e->getMessage()]);
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
                
                // PASO 2: OPTIMIZACIÓN - Leer y procesar datos directamente por chunks sin almacenar todo en memoria
                $chunkSize = 1000; // Aumentado para mejor rendimiento
                @file_put_contents($progressPath, json_encode(['status' => 'reading_xlsx', 'processed' => 0, 'total' => max(0, $highestRow - 4), 'percent' => 0, 'id' => $importId]));
                
                // Procesamiento directo sin almacenar todo el array $datos en memoria
                $procesadas = 0;
                $errores = 0;
                $año = date('Y');
                $batchSize = 500; // Reducido para evitar lock timeouts
                
                // Cache para proveedores y materiales (SIN verificar duplicados en MaterialKilo)
                $proveedoresCache = Proveedor::pluck('nombre_proveedor', 'id_proveedor')->toArray();
                $materialesCache = Material::pluck('id', 'codigo')->toArray();
                
                // Arrays para lotes
                $proveedoresLote = [];
                $materialesLote = [];
                $materialesKilosLote = [];
                
                $startTime = microtime(true);
                $lastLogTime = $startTime;
                
                for ($start = 5; $start <= $highestRow; $start += $chunkSize) {
                    $end = min($start + $chunkSize - 1, $highestRow);
                    $chunkFilter = new ChunkReadFilter($start, $end);
                    $reader->setReadFilter($chunkFilter);
                    $reader->setLoadSheetsOnly($sheetToRead);
                    $spreadsheetChunk = $reader->load($path);
                    $ws = $spreadsheetChunk->getActiveSheet();

                    // PROCESAMIENTO DIRECTO FILA POR FILA - SIN ALMACENAR EN MEMORIA
                    for ($row = $start; $row <= $end; $row++) {
                        $fila = [];
                        $filaVacia = true;
                        for ($col = 'A'; $col <= $highestColumn; $col++) {
                            $valor = $ws->getCell($col . $row)->getCalculatedValue();
                            $valor = trim($valor ?? '');
                            if (!empty($valor)) { $filaVacia = false; }
                            $fila[] = $valor;
                        }
                        
                        if (!$filaVacia && count($cabeceras) > 0) {
                            // Ajustar tamaño del array
                            if (count($fila) < count($cabeceras)) {
                                $fila = array_pad($fila, count($cabeceras), '');
                            } elseif (count($fila) > count($cabeceras)) {
                                $fila = array_slice($fila, 0, count($cabeceras));
                            }
                            
                            $filaData = array_combine($cabeceras, $fila);
                            
                            // PROCESAMIENTO INMEDIATO (sin almacenar en array $datos)
                            $this->procesarFilaDirecta($filaData, $proveedoresCache, $materialesCache, 
                                                     $materialesKilosExistenteCache, $proveedoresLote, 
                                                     $materialesLote, $materialesKilosLote, $año, 
                                                     $procesadas, $batchSize, $progressPath, $importId, 
                                                     $startTime, $lastLogTime, $highestRow);
                        }
                    }

                    $spreadsheetChunk->disconnectWorksheets();
                    unset($spreadsheetChunk);
                    gc_collect_cycles();
                    
                    // Actualizar progreso de lectura
                    $processedSoFar = max(0, $end - 4);
                    $totalRows = max(0, $highestRow - 4);
                    $percent = $totalRows > 0 ? intval(($processedSoFar / $totalRows) * 100) : 100;
                    @file_put_contents($progressPath, json_encode(['status' => 'reading_xlsx', 'processed' => $processedSoFar, 'total' => $totalRows, 'percent' => $percent, 'id' => $importId]));
                }
                
                // Insertar lote final si queda algún registro
                $this->insertarLoteFinal($proveedoresLote, $materialesLote, $materialesKilosLote, $startTime);
                
                Log::info('XLSX procesado directamente. Total filas procesadas: ' . $procesadas);
                
                // Marcar completado y salir con éxito
                @file_put_contents($progressPath, json_encode(['status' => 'completed', 'processed' => $procesadas, 'total' => $procesadas, 'percent' => 100, 'id' => $importId]));
                return view('proveedores.import_complete', ['message' => "Archivo XLSX importado con procesamiento optimizado. Filas procesadas: {$procesadas}"]);
                
            } catch (Exception $e) {
                Log::error('Error al procesar archivo XLSX: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                @file_put_contents($progressPath, json_encode(['status' => 'error', 'message' => $e->getMessage(), 'id' => $importId]));
                return redirect('/proveedores')->withErrors(array('archivo' => 'Error al procesar el archivo XLSX: ' . $e->getMessage()));
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
                    return redirect('/proveedores')->withErrors(array('archivo' => 'No se pudo detectar el delimitador del archivo CSV.'));
                }
            } catch (Exception $e) {
                Log::error('Error en procesamiento inicial de CSV: ' . $e->getMessage());
                return redirect('/proveedores')->withErrors(array('archivo' => 'Error al procesar archivo CSV: ' . $e->getMessage()));
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
                return redirect('/proveedores')->withErrors(array('archivo' => 'Error al leer archivo CSV: ' . $e->getMessage()));
            }
        }

        // 3. OPTIMIZACIÓN: Procesar e insertar los datos en lotes para máximo rendimiento
        Log::info('Iniciando procesamiento optimizado de ' . count($datos) . ' filas de datos');
        
        $procesadas = 0;
        $errores = 0;
        $año = date('Y');
        $batchSize = 500; // Reducido para evitar lock wait timeout
        
        // Cache para proveedores y materiales existentes para evitar consultas repetitivas
        $proveedoresCache = [];
        $materialesCache = [];
        $materialesKilosExistenteCache = [];
        
        // Arrays para lotes de inserción
        $proveedoresLote = [];
        $materialesLote = [];
        $materialesKilosLote = [];
        
        // Precarga de todos los proveedores existentes
        $proveedoresExistentes = Proveedor::pluck('nombre_proveedor', 'id_proveedor')->toArray();
        $proveedoresCache = $proveedoresExistentes;
        
        // Precarga de todos los materiales existentes  
        $materialesExistentes = Material::pluck('id', 'codigo')->toArray();
        $materialesCache = $materialesExistentes;
        
        Log::info("Cache inicializado: " . count($proveedoresCache) . " proveedores, " . 
                  count($materialesCache) . " materiales. PERMITIENDO DUPLICADOS EN MaterialKilo");
        
        $startTime = microtime(true);
        $lastLogTime = $startTime;
        
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
                
                $mes = isset($filaArray[11]) ? trim($filaArray[11]) : ''; // Posición 11 - MES
                
                // Fallbacks usando nombres de cabeceras
                if (empty($proveedorId)) $proveedorId = isset($fila['proveedor']) ? trim($fila['proveedor']) : '';
                if (empty($nombreProveedor)) $nombreProveedor = isset($fila['nombre_del_proveedor']) ? trim($fila['nombre_del_proveedor']) : '';
                if (empty($materialCodigo)) $materialCodigo = isset($fila['material']) ? trim($fila['material']) : '';
                if (empty($jerarquia)) $jerarquia = isset($fila['jerarqua_product']) ? trim($fila['jerarqua_product']) : '';
                if (empty($descripcionMaterial)) $descripcionMaterial = isset($fila['descripcin_de_material']) ? trim($fila['descripcin_de_material']) : '';
                if (empty($mes) && isset($cabeceras[11]) && isset($fila[$cabeceras[11]])) $mes = trim($fila[$cabeceras[11]]);
                
                // Obtener otros campos por posición
                $ce = isset($filaArray[5]) ? trim($filaArray[5]) : '';
                $ctd_emdev = isset($filaArray[7]) ? trim($filaArray[7]) : '';
                $umb = isset($filaArray[8]) ? trim($filaArray[8]) : '';
                $valor_emdev = isset($filaArray[9]) ? trim($filaArray[9]) : '';
                $factor_conversin = isset($filaArray[12]) ? trim($filaArray[12]) : '';
                $totalKgRaw = isset($filaArray[13]) ? trim($filaArray[13]) : '';
                
                // Fallbacks adicionales
                if (empty($totalKgRaw)) $totalKgRaw = isset($fila['total_kg']) ? trim($fila['total_kg']) : '';
                if (empty($ctd_emdev)) $ctd_emdev = isset($fila['ctd_emdev']) ? trim($fila['ctd_emdev']) : '';
                if (empty($umb)) $umb = isset($fila['umb']) ? trim($fila['umb']) : '';
                if (empty($ce)) $ce = isset($fila['ce']) ? trim($fila['ce']) : '';
                if (empty($valor_emdev)) $valor_emdev = isset($fila['valor_emdev']) ? trim($fila['valor_emdev']) : '';
                if (empty($factor_conversin)) $factor_conversin = isset($fila['factor_conversin']) ? trim($fila['factor_conversin']) : '';

                // Conversiones de formato optimizadas
                // Función para convertir números con formato europeo o decimal simple
                $convertirNumero = function($valor) {
                    $valor = trim($valor);
                    if (empty($valor)) return 0;
                    
                    // Contar puntos y comas para determinar el formato
                    $numPuntos = substr_count($valor, '.');
                    $numComas = substr_count($valor, ',');
                    
                    // Si tiene ambos, es formato europeo: 1.234,56 -> eliminar puntos, coma a punto
                    if ($numPuntos > 0 && $numComas > 0) {
                        return floatval(str_replace(',', '.', str_replace('.', '', $valor)));
                    }
                    // Si solo tiene comas, la coma es el decimal: 0,3 o 1,234 -> coma a punto
                    elseif ($numComas > 0 && $numPuntos == 0) {
                        return floatval(str_replace(',', '.', $valor));
                    }
                    // Si solo tiene puntos múltiples, son separadores de miles: 1.234 -> eliminar puntos
                    elseif ($numPuntos > 1) {
                        return floatval(str_replace('.', '', $valor));
                    }
                    // Si tiene un solo punto, es decimal: 0.3 -> dejar como está
                    else {
                        return floatval($valor);
                    }
                };
                
                $totalKg = $convertirNumero($totalKgRaw);
                $valor_emdev_final = $convertirNumero($valor_emdev);
                $factor_conversin_final = $convertirNumero($factor_conversin);
                $ctd_emdev_final = $convertirNumero($ctd_emdev);
                
                // Log de conversión para las primeras 5 filas (debugging)
                if ($index < 5) {
                    Log::info("Fila {$index} - Conversiones:");
                    Log::info("  factor_conversion: '{$factor_conversin}' -> {$factor_conversin_final}");
                    Log::info("  valor_emdev: '{$valor_emdev}' -> {$valor_emdev_final}");
                    Log::info("  total_kg: '{$totalKgRaw}' -> {$totalKg}");
                    Log::info("  ctd_emdev: '{$ctd_emdev}' -> {$ctd_emdev_final}");
                }

                // Validación crítica
                if (empty($proveedorId) || empty($materialCodigo) || empty($mes)) {
                    continue;
                }

                // PERMITIR DUPLICADOS: Agregar TODOS los registros sin verificar existencia
                
                // Agregar al lote de proveedores si no existe
                if (!isset($proveedoresCache[$proveedorId])) {
                    $proveedoresLote[$proveedorId] = [
                        'id_proveedor' => $proveedorId,
                        'nombre_proveedor' => $nombreProveedor,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $proveedoresCache[$proveedorId] = $nombreProveedor;
                }

                // Agregar al lote de materiales si no existe
                if (!isset($materialesCache[$materialCodigo])) {
                    $materialesLote[$materialCodigo] = [
                        'codigo' => $materialCodigo,
                        'jerarquia' => $jerarquia,
                        'descripcion' => $descripcionMaterial,
                        'proveedor_id' => $proveedorId,
                        'factor_conversion' => $factor_conversin_final,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $materialesCache[$materialCodigo] = true;
                }

                // SIEMPRE agregar al lote de MaterialKilo (SIN verificar duplicados)
                $materialesKilosLote[] = [
                    'codigo_material' => $materialCodigo,
                    'proveedor_id' => $proveedorId,
                    'mes' => $mes,
                    'año' => $año,
                    'total_kg' => $totalKg,
                    'ctd_emdev' => $ctd_emdev_final,
                    'umb' => $umb,
                    'ce' => $ce,
                    'valor_emdev' => $valor_emdev_final,
                    'factor_conversion' => $factor_conversin_final,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $procesadas++;

                // INSERCIÓN EN LOTES CADA 500 REGISTROS CON LOGGING DETALLADO
                if (count($materialesKilosLote) >= $batchSize) {
                    // Configurar timeout para evitar lock wait timeout
                    DB::statement('SET SESSION innodb_lock_wait_timeout = 300');
                    
                    $maxRetries = 3;
                    $attempt = 0;
                    
                    while ($attempt < $maxRetries) {
                        try {
                            DB::beginTransaction();
                        // Insertar proveedores en lote
                        if (!empty($proveedoresLote)) {
                            DB::table('proveedores')->insertOrIgnore(array_values($proveedoresLote));
                            $proveedoresLote = [];
                        }

                        // Insertar materiales en lote
                        if (!empty($materialesLote)) {
                            DB::table('materiales')->insertOrIgnore(array_values($materialesLote));
                        }

                        // Insertar material kilos en lote (PERMITIR DUPLICADOS)
                        if (!empty($materialesKilosLote)) {
                            DB::table('material_kilos')->insert($materialesKilosLote);
                            $materialesKilosLote = [];
                        }

                        DB::commit();
                        
                        // LOGGING DETALLADO CADA 3000 INSERCIONES COMO SOLICITÓ EL USUARIO
                        $currentTime = microtime(true);
                        $elapsedTime = $currentTime - $startTime;
                        $batchTime = $currentTime - $lastLogTime;
                        $avgTimePerRecord = $elapsedTime / $procesadas;
                        $remainingRecords = count($datos) - $procesadas;
                        $estimatedTimeRemaining = $remainingRecords * $avgTimePerRecord;
                        $percentage = round(($procesadas / count($datos)) * 100, 2);
                        
                        Log::info("=== PROGRESO DE IMPORTACIÓN ===");
                        Log::info("Registros procesados: {$procesadas} de " . count($datos) . " ({$percentage}%)");
                        Log::info("Tiempo transcurrido: " . round($elapsedTime, 2) . " segundos");
                        Log::info("Tiempo del último lote: " . round($batchTime, 2) . " segundos");
                        Log::info("Promedio por registro: " . round($avgTimePerRecord * 1000, 2) . " ms");
                        Log::info("Tiempo estimado restante: " . round($estimatedTimeRemaining / 60, 1) . " minutos");
                        Log::info("Memoria utilizada: " . round(memory_get_usage(true) / 1024 / 1024, 1) . " MB");
                        Log::info("===============================");
                        
                        $lastLogTime = $currentTime;
                        
                        // Actualizar progreso en archivo
                        $percentInsert = intval($percentage);
                        @file_put_contents($progressPath, json_encode([
                            'status' => 'inserting', 
                            'processed' => $procesadas, 
                            'total' => count($datos), 
                            'percent' => $percentInsert,
                            'estimated_remaining_minutes' => round($estimatedTimeRemaining / 60, 1),
                            'records_per_second' => round(500 / max($batchTime, 0.001), 1),
                            'id' => $importId
                        ]));
                        
                            DB::commit();
                            break; // Salir del bucle si fue exitoso
                            
                        } catch (Exception $e) {
                            DB::rollBack();
                            $attempt++;
                            
                            // Si es un error de lock timeout o deadlock, reintentar
                            if (($e->getCode() == 1205 || $e->getCode() == 1213) && $attempt < $maxRetries) {
                                Log::warning("Lock timeout/deadlock detectado. Intento {$attempt}/{$maxRetries}. Reintentando en 2 segundos...");
                                sleep(2); // Esperar 2 segundos antes de reintentar
                                continue;
                            }
                            
                            Log::error("Error en inserción de lote (intento {$attempt}/{$maxRetries}): " . $e->getMessage());
                            throw $e;
                        }
                    }
                }
                
            } catch (Exception $e) {
                $errores++;
                Log::error("Error procesando fila {$index}: " . $e->getMessage());
            }
        }

        // Insertar lote final si queda algún registro (con manejo de deadlocks)
        if (!empty($materialesKilosLote) || !empty($proveedoresLote) || !empty($materialesLote)) {
            DB::statement('SET SESSION innodb_lock_wait_timeout = 300');
            
            $maxRetries = 3;
            $attempt = 0;
            
            while ($attempt < $maxRetries) {
                try {
                    DB::beginTransaction();
                    
                    if (!empty($proveedoresLote)) {
                        DB::table('proveedores')->insertOrIgnore(array_values($proveedoresLote));
                    }
                    if (!empty($materialesLote)) {
                        DB::table('materiales')->insertOrIgnore(array_values($materialesLote));
                    }
                    if (!empty($materialesKilosLote)) {
                        DB::table('material_kilos')->insert($materialesKilosLote);
                    }
                    
                    DB::commit();
                    
                    // Log final
                    $finalTime = microtime(true);
                    $totalTime = $finalTime - $startTime;
                    Log::info("=== LOTE FINAL INSERTADO ===");
                    Log::info("Registros en lote final: " . count($materialesKilosLote));
                    Log::info("Tiempo total: " . round($totalTime, 2) . " segundos");
                    Log::info("Promedio final: " . round($totalTime / max($procesadas, 1), 4) . " seg/registro");
                    Log::info("============================");
                    
                    break; // Salir si fue exitoso
                    
                } catch (Exception $e) {
                    DB::rollBack();
                    $attempt++;
                    
                    // Reintentar en caso de deadlock
                    if (($e->getCode() == 1205 || $e->getCode() == 1213) && $attempt < $maxRetries) {
                        Log::warning("Lock timeout/deadlock en lote final CSV. Intento {$attempt}/{$maxRetries}. Reintentando...");
                        sleep(2);
                        continue;
                    }
                    
                    Log::error("Error en inserción de lote final CSV (intento {$attempt}/{$maxRetries}): " . $e->getMessage());
                    throw $e;
                }
            }
        }
          Log::info("Procesamiento completado. Filas procesadas: {$procesadas}, Errores: {$errores}");
            
    // marcar completado y limpiar archivo de progreso
    @file_put_contents($progressPath, json_encode(['status' => 'completed', 'processed' => $procesadas, 'total' => $totalRows ?? count($datos), 'percent' => 100, 'id' => $importId]));
    Log::info('=== FIN IMPORTAR ARCHIVO EXITOSO ===');
    return view('proveedores.import_complete', ['message' => "Archivo importado correctamente. Filas procesadas: {$procesadas}"]);
        
        } catch (Exception $e) {
            Log::error('Error general en importarArchivo: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== FIN IMPORTAR ARCHIVO CON ERROR ===');
            return redirect('/proveedores')->withErrors(['archivo' => 'Error al procesar el archivo: ' . $e->getMessage()]);
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

    /**
     * Procesa una fila de datos directamente sin almacenarla en memoria
     * OPTIMIZACIÓN CRÍTICA para archivos grandes
     */
    private function procesarFilaDirecta($filaData, &$proveedoresCache, &$materialesCache, 
                                      &$materialesKilosExistenteCache, &$proveedoresLote, 
                                      &$materialesLote, &$materialesKilosLote, $año, 
                                      &$procesadas, $batchSize, $progressPath, $importId, 
                                      $startTime, &$lastLogTime, $totalRows)
    {
        try {
            $filaArray = array_values($filaData);
            
            // Extraer datos de la fila
            $materialCodigo = isset($filaArray[1]) ? trim($filaArray[1]) : '';
            $proveedorId = isset($filaArray[3]) ? trim($filaArray[3]) : '';
            $nombreProveedor = isset($filaArray[4]) ? trim($filaArray[4]) : '';
            $mes = isset($filaArray[11]) ? trim($filaArray[11]) : '';
            
            // Fallbacks usando nombres de cabeceras si están disponibles
            if (empty($proveedorId)) $proveedorId = isset($filaData['proveedor']) ? trim($filaData['proveedor']) : '';
            if (empty($nombreProveedor)) $nombreProveedor = isset($filaData['nombre_del_proveedor']) ? trim($filaData['nombre_del_proveedor']) : '';
            if (empty($materialCodigo)) $materialCodigo = isset($filaData['material']) ? trim($filaData['material']) : '';
            if (empty($mes)) $mes = isset($filaData['mes']) ? trim($filaData['mes']) : '';
            
            // Validación crítica
            if (empty($proveedorId) || empty($materialCodigo) || empty($mes)) {
                return;
            }
            
            // Obtener resto de campos
            $jerarquia = isset($filaArray[0]) ? trim($filaArray[0]) : '';
            $descripcionMaterial = isset($filaArray[2]) ? trim($filaArray[2]) : '';
            $ce = isset($filaArray[5]) ? trim($filaArray[5]) : '';
            $ctd_emdev = isset($filaArray[7]) ? trim($filaArray[7]) : '';
            $umb = isset($filaArray[8]) ? trim($filaArray[8]) : '';
            $valor_emdev = isset($filaArray[9]) ? trim($filaArray[9]) : '';
            $factor_conversin = isset($filaArray[12]) ? trim($filaArray[12]) : '';
            $totalKgRaw = isset($filaArray[13]) ? trim($filaArray[13]) : '';
            
            // Conversiones de formato optimizadas
            // Función para convertir números con formato europeo o decimal simple
            $convertirNumero = function($valor) {
                $valor = trim($valor);
                if (empty($valor)) return 0;
                
                // Contar puntos y comas para determinar el formato
                $numPuntos = substr_count($valor, '.');
                $numComas = substr_count($valor, ',');
                
                // Si tiene ambos, es formato europeo: 1.234,56 -> eliminar puntos, coma a punto
                if ($numPuntos > 0 && $numComas > 0) {
                    return floatval(str_replace(',', '.', str_replace('.', '', $valor)));
                }
                // Si solo tiene comas, la coma es el decimal: 0,3 o 1,234 -> coma a punto
                elseif ($numComas > 0 && $numPuntos == 0) {
                    return floatval(str_replace(',', '.', $valor));
                }
                // Si solo tiene puntos múltiples, son separadores de miles: 1.234 -> eliminar puntos
                elseif ($numPuntos > 1) {
                    return floatval(str_replace('.', '', $valor));
                }
                // Si tiene un solo punto, es decimal: 0.3 -> dejar como está
                else {
                    return floatval($valor);
                }
            };
            
            $totalKg = $convertirNumero($totalKgRaw);
            $valor_emdev_final = $convertirNumero($valor_emdev);
            $factor_conversin_final = $convertirNumero($factor_conversin);
            $ctd_emdev_final = $convertirNumero($ctd_emdev);

            // PERMITIR DUPLICADOS: Procesar TODOS los registros sin verificar existencia
            
            // Agregar al lote de proveedores si no existe
            if (!isset($proveedoresCache[$proveedorId])) {
                $proveedoresLote[$proveedorId] = [
                    'id_proveedor' => $proveedorId,
                    'nombre_proveedor' => $nombreProveedor,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $proveedoresCache[$proveedorId] = $nombreProveedor;
            }

            // Agregar al lote de materiales si no existe
            if (!isset($materialesCache[$materialCodigo])) {
                $materialesLote[$materialCodigo] = [
                    'codigo' => $materialCodigo,
                    'jerarquia' => $jerarquia,
                    'descripcion' => $descripcionMaterial,
                    'proveedor_id' => $proveedorId,
                    'factor_conversion' => $factor_conversin_final,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $materialesCache[$materialCodigo] = true;
            }

            // SIEMPRE agregar al lote de MaterialKilo (PERMITIR DUPLICADOS)
            $materialesKilosLote[] = [
                'codigo_material' => $materialCodigo,
                'proveedor_id' => $proveedorId,
                'mes' => $mes,
                'año' => $año,
                'total_kg' => $totalKg,
                'ctd_emdev' => $ctd_emdev_final,
                'umb' => $umb,
                'ce' => $ce,
                'valor_emdev' => $valor_emdev_final,
                'factor_conversion' => $factor_conversin_final,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            $procesadas++;

            // INSERCIÓN EN LOTES CADA 3000 REGISTROS
            if (count($materialesKilosLote) >= $batchSize) {
                $this->insertarLote($proveedoresLote, $materialesLote, $materialesKilosLote, 
                                  $procesadas, $totalRows, $startTime, $lastLogTime, 
                                  $progressPath, $importId);
            }
            
        } catch (Exception $e) {
            Log::error("Error procesando fila directa: " . $e->getMessage());
        }
    }

    /**
     * Inserta un lote de registros con logging optimizado
     */
    private function insertarLote(&$proveedoresLote, &$materialesLote, &$materialesKilosLote, 
                                $procesadas, $totalRows, $startTime, &$lastLogTime, 
                                $progressPath, $importId)
    {
        // Configurar timeout más largo para evitar lock wait timeout
        DB::statement('SET SESSION innodb_lock_wait_timeout = 300'); // 5 minutos
        
        $maxRetries = 3;
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();
            // Insertar proveedores en lote
            if (!empty($proveedoresLote)) {
                DB::table('proveedores')->insertOrIgnore(array_values($proveedoresLote));
                $proveedoresLote = [];
            }

            // Insertar materiales en lote
            if (!empty($materialesLote)) {
                DB::table('materiales')->insertOrIgnore(array_values($materialesLote));
                $materialesLote = [];
            }

            // Insertar material kilos en lote (PERMITIR DUPLICADOS)
            if (!empty($materialesKilosLote)) {
                DB::table('material_kilos')->insert($materialesKilosLote);
                $materialesKilosLote = [];
            }

            DB::commit();
            
            // LOGGING DETALLADO CADA 3000 INSERCIONES
            $currentTime = microtime(true);
            $elapsedTime = $currentTime - $startTime;
            $batchTime = $currentTime - $lastLogTime;
            $avgTimePerRecord = $elapsedTime / max($procesadas, 1);
            $remainingRecords = $totalRows - $procesadas;
            $estimatedTimeRemaining = $remainingRecords * $avgTimePerRecord;
            $percentage = round(($procesadas / max($totalRows, 1)) * 100, 2);
            
            Log::info("=== PROGRESO DE IMPORTACIÓN ===");
            Log::info("Registros procesados: {$procesadas} de {$totalRows} ({$percentage}%)");
            Log::info("Tiempo transcurrido: " . round($elapsedTime, 2) . " segundos");
            Log::info("Tiempo del último lote: " . round($batchTime, 2) . " segundos");
            Log::info("Promedio por registro: " . round($avgTimePerRecord * 1000, 2) . " ms");
            Log::info("Tiempo estimado restante: " . round($estimatedTimeRemaining / 60, 1) . " minutos");
            Log::info("Memoria utilizada: " . round(memory_get_usage(true) / 1024 / 1024, 1) . " MB");
            Log::info("===============================");
            
            $lastLogTime = $currentTime;
            
            // Actualizar progreso en archivo
            @file_put_contents($progressPath, json_encode([
                'status' => 'inserting', 
                'processed' => $procesadas, 
                'total' => $totalRows, 
                'percent' => intval($percentage),
                'estimated_remaining_minutes' => round($estimatedTimeRemaining / 60, 1),
                'records_per_second' => round(500 / max($batchTime, 0.001), 1),
                'id' => $importId
            ]));
            
                DB::commit();
                break; // Salir del bucle si fue exitoso
                
            } catch (Exception $e) {
                DB::rollBack();
                $attempt++;
                
                // Si es un error de lock timeout o deadlock, reintentar
                if (($e->getCode() == 1205 || $e->getCode() == 1213) && $attempt < $maxRetries) {
                    Log::warning("Lock timeout/deadlock detectado. Intento {$attempt}/{$maxRetries}. Reintentando en 2 segundos...");
                    sleep(2); // Esperar 2 segundos antes de reintentar
                    continue;
                }
                
                Log::error("Error en inserción de lote (intento {$attempt}/{$maxRetries}): " . $e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * Inserta el lote final de registros
     */
    private function insertarLoteFinal($proveedoresLote, $materialesLote, $materialesKilosLote, $startTime)
    {
        if (!empty($materialesKilosLote) || !empty($proveedoresLote) || !empty($materialesLote)) {
            // Configurar timeout más largo
            DB::statement('SET SESSION innodb_lock_wait_timeout = 300');
            
            $maxRetries = 3;
            $attempt = 0;
            
            while ($attempt < $maxRetries) {
                try {
                    DB::beginTransaction();
                if (!empty($proveedoresLote)) {
                    DB::table('proveedores')->insertOrIgnore(array_values($proveedoresLote));
                }
                if (!empty($materialesLote)) {
                    DB::table('materiales')->insertOrIgnore(array_values($materialesLote));
                }
                if (!empty($materialesKilosLote)) {
                    DB::table('material_kilos')->insert($materialesKilosLote);
                }
                DB::commit();
                
                // Log final
                $finalTime = microtime(true);
                $totalTime = $finalTime - $startTime;
                Log::info("=== LOTE FINAL INSERTADO ===");
                Log::info("Registros en lote final: " . count($materialesKilosLote));
                Log::info("Tiempo total de importación: " . round($totalTime, 2) . " segundos");
                Log::info("============================");
                
                    DB::commit();
                    break; // Salir si fue exitoso
                    
                } catch (Exception $e) {
                    DB::rollBack();
                    $attempt++;
                    
                    // Reintentar en caso de deadlock
                    if (($e->getCode() == 1205 || $e->getCode() == 1213) && $attempt < $maxRetries) {
                        Log::warning("Lock timeout/deadlock en lote final. Intento {$attempt}/{$maxRetries}. Reintentando...");
                        sleep(2);
                        continue;
                    }
                    
                    Log::error("Error en inserción de lote final (intento {$attempt}/{$maxRetries}): " . $e->getMessage());
                    throw $e;
                }
            }
        }
    }

    /**
     * Descargar formato ejemplo para importación de proveedores y artículos
     */
    public function descargarFormatoProveedoresEntradas()
    {
        // Buscar primero en public/docs por si se colocó ahí para acceso directo
        $publicPath = public_path('docs/FormatoProveedoresEntradas.xlsx');
        $resourcePath = resource_path('views/proveedores/FormatoProveedoresEntradas.xlsx');

        if (file_exists($publicPath)) {
            $rutaArchivo = $publicPath;
        } elseif (file_exists($resourcePath)) {
            $rutaArchivo = $resourcePath;
        } else {
            abort(404, 'Archivo de formato no encontrado');
        }

        // Evitar salidas previas que puedan corromper el binario
        if (ob_get_level()) {
            @ob_end_clean();
        }

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="FormatoProveedoresEntradas.xlsx"'
        ];

        return response()->download($rutaArchivo, 'FormatoProveedoresEntradas.xlsx', $headers);
    }

    /**
     * Descargar formato ejemplo para importación sin factor de conversión
     */
    public function descargarFormatoSinFconversion()
    {
        // Buscar primero en public/docs por si se colocó ahí para acceso directo
        $publicPath = public_path('docs/FormatoEntradasSinFconversion.xlsx');
        $resourcePath = resource_path('views/proveedores/FormatoEntradasSinFconversion.xlsx');

        if (file_exists($publicPath)) {
            $rutaArchivo = $publicPath;
        } elseif (file_exists($resourcePath)) {
            $rutaArchivo = $resourcePath;
        } else {
            abort(404, 'Archivo de formato no encontrado');
        }

        // Evitar salidas previas que puedan corromper el binario
        if (ob_get_level()) {
            @ob_end_clean();
        }

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="FormatoEntradasSinFconversion.xlsx"'
        ];

        return response()->download($rutaArchivo, 'FormatoEntradasSinFconversion.xlsx', $headers);
    }
}

