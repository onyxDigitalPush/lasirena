<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

echo "=== Test PhpSpreadsheet ===\n";

try {
    // Crear un archivo XLSX de prueba
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Agregar datos de prueba
    $sheet->setCellValue('A5', 'proveedor');
    $sheet->setCellValue('B5', 'nombre_del_proveedor');
    $sheet->setCellValue('C5', 'material');
    $sheet->setCellValue('D5', 'jerarquia_product');
    $sheet->setCellValue('E5', 'descripcion_de_material');
    
    $sheet->setCellValue('A6', '001');
    $sheet->setCellValue('B6', 'Proveedor Test');
    $sheet->setCellValue('C6', 'MAT001');
    $sheet->setCellValue('D6', 'Categoria');
    $sheet->setCellValue('E6', 'Material de prueba');
    
    $writer = new Xlsx($spreadsheet);
    $testFile = 'test_file.xlsx';
    $writer->save($testFile);
    
    echo "✓ Archivo XLSX de prueba creado: {$testFile}\n";
    
    // Leer el archivo
    $loadedSpreadsheet = IOFactory::load($testFile);
    $worksheet = $loadedSpreadsheet->getActiveSheet();
    
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    echo "✓ Archivo leído exitosamente\n";
    echo "Filas: {$highestRow}, Columnas: {$highestColumn}\n";
    
    // Leer cabeceras (fila 5)
    echo "\nCabeceras (Fila 5):\n";
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $valor = $worksheet->getCell($col . '5')->getCalculatedValue();
        echo "{$col}5: {$valor}\n";
    }
    
    // Leer datos (fila 6)
    echo "\nDatos (Fila 6):\n";
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $valor = $worksheet->getCell($col . '6')->getCalculatedValue();
        echo "{$col}6: {$valor}\n";
    }
    
    // Limpiar archivo de prueba
    unlink($testFile);
    echo "\n✓ Test completado exitosamente\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
