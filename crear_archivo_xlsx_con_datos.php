<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

echo "Creando archivo de prueba XLSX...\n";

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Datos de ejemplo con cabeceras en la fila 5
$datos = [
    [1, '', '', '', 'Datos de prueba', '', '', '', '', '', '', ''],
    [2, '', '', '', '', '', '', '', '', '', '', ''],
    [3, '', '', '', '', '', '', '', '', '', '', ''],
    [4, '', '', '', '', '', '', '', '', '', '', ''],
    [5, 'código', 'nombre', 'descripción', 'precio', 'categoria', 'stock', 'proveedor', 'estado', 'fecha', 'observaciones', 'activo'],
    [6, 'PROD001', 'Producto 1', 'Descripción del producto 1', 100.50, 'Categoria A', 10, 'Proveedor 1', 'Activo', '2025-06-05', 'Sin observaciones', 'Si'],
    [7, 'PROD002', 'Producto 2', 'Descripción del producto 2', 200.75, 'Categoria B', 25, 'Proveedor 2', 'Activo', '2025-06-05', 'Producto nuevo', 'Si'],
    [8, 'PROD003', 'Producto 3', 'Descripción del producto 3', 50.25, 'Categoria A', 5, 'Proveedor 1', 'Inactivo', '2025-06-05', 'Revisar stock', 'No']
];

// Escribir los datos
foreach ($datos as $rowIndex => $rowData) {
    $row = $rowIndex + 1;
    foreach ($rowData as $colIndex => $value) {
        $col = chr(65 + $colIndex); // A, B, C, etc.
        $sheet->setCellValue($col . $row, $value);
    }
}

// Guardar el archivo
$writer = new Xlsx($spreadsheet);
$writer->save('archivo_prueba_con_datos.xlsx');

echo "Archivo creado: archivo_prueba_con_datos.xlsx\n";
echo "Cabeceras en fila 5, datos desde fila 6\n";
?>
