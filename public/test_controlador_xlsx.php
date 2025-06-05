<?php
echo "<h2>Test completo de importación XLSX - Simulando ProveedorController</h2>";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $archivo = __DIR__ . '/../archivo_prueba_con_datos.xlsx';
    
    if (!file_exists($archivo)) {
        echo "❌ Archivo no existe: " . $archivo . "<br>";
        exit;
    }
    
    echo "✅ Archivo encontrado<br>";
    
    // Simulación exacta del código del controlador
    $path = $archivo;
    $cabeceras = [];
    $datos = [];
    
    echo "<h3>1. Verificando archivo...</h3>";
    
    // Verificar que el archivo existe y es legible
    if (!file_exists($path) || !is_readable($path)) {
        throw new Exception("El archivo no existe o no es legible: " . $path);
    }
    echo "✅ Archivo existe y es legible<br>";
      // Verificar que PhpSpreadsheet puede detectar el tipo de archivo
    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($path);
    echo "✅ Tipo de archivo detectado: " . $inputFileType . "<br>";
    
    if ($inputFileType !== 'Xlsx') {
        throw new Exception("El archivo no es un XLSX válido. Tipo detectado: " . $inputFileType);
    }
      echo "<h3>2. Cargando archivo...</h3>";
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    echo "✅ Archivo XLSX cargado - Filas: " . $highestRow . ", Columnas: " . $highestColumn . "<br>";
    
    echo "<h3>3. Procesando cabeceras (fila 5)...</h3>";
    
    // Para XLSX, las cabeceras están en la fila 5 (índice 5)
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $valor = $worksheet->getCell($col . '5')->getCalculatedValue();
        if (!empty($valor)) {
            $cabeceras[] = $valor;
        }
    }
    
    echo "✅ Cabeceras encontradas: " . implode(', ', $cabeceras) . "<br>";
    
    // Limpiar cabeceras
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
    
    echo "✅ Cabeceras procesadas: " . implode(', ', $cabeceras) . "<br>";
    
    // Verificar que tenemos cabeceras válidas
    if (empty($cabeceras)) {
        throw new Exception("No se encontraron cabeceras válidas en la fila 5 del archivo XLSX");
    }
    
    echo "<h3>4. Procesando datos (desde fila 6)...</h3>";
    
    // Leer datos desde la fila 6 en adelante
    for ($row = 6; $row <= $highestRow; $row++) {
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
            $registro = array_combine($cabeceras, $fila);
            $datos[] = $registro;
            echo "✅ Fila " . $row . " procesada: " . json_encode($registro) . "<br>";
        } else {
            echo "⚠️ Fila " . $row . " saltada - Vacía: " . ($filaVacia ? 'Sí' : 'No') . ", Columnas: " . count($fila) . " vs " . count($cabeceras) . "<br>";
        }
    }
    
    echo "<h3>Resumen:</h3>";
    echo "✅ Total de filas de datos procesadas: " . count($datos) . "<br>";
    echo "✅ Cabeceras: " . count($cabeceras) . "<br>";
    
    if (!empty($datos)) {
        echo "<h3>Primer registro de ejemplo:</h3>";
        echo "<pre>" . print_r($datos[0], true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}
?>
