<?php
echo "<h2>Test específico de importación XLSX</h2>";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $archivo = __DIR__ . '/../archivo_prueba_con_datos.xlsx';
    
    if (!file_exists($archivo)) {
        echo "❌ Archivo não existe: " . $archivo . "<br>";
        exit;
    }
    
    echo "✅ Archivo encontrado: " . $archivo . "<br>";
    echo "Tamaño del archivo: " . filesize($archivo) . " bytes<br>";
    
    // Test de lectura del archivo XLSX
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    echo "✅ Reader XLSX creado<br>";
    
    $spreadsheet = $reader->load($archivo);
    echo "✅ Archivo XLSX cargado<br>";
    
    $worksheet = $spreadsheet->getActiveSheet();
    echo "✅ Hoja activa obtenida<br>";
    
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    echo "Dimensiones: " . $highestRow . " filas, hasta columna " . $highestColumn . "<br>";
    
    // Leer las primeras 3 filas como ejemplo
    echo "<h3>Contenido del archivo:</h3>";
    for ($row = 1; $row <= min(3, $highestRow); $row++) {
        echo "Fila " . $row . ": ";
        $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
        echo implode(' | ', $rowData[0]) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}
?>
