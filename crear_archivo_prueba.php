<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Agregar cabeceras en la fila 5 (como espera el código)
$sheet->setCellValue('A5', 'proveedor');
$sheet->setCellValue('B5', 'nombre_del_proveedor');
$sheet->setCellValue('C5', 'material');
$sheet->setCellValue('D5', 'jerarqua_product');
$sheet->setCellValue('E5', 'descripcin_de_material');
$sheet->setCellValue('F5', 'mes');
$sheet->setCellValue('G5', 'total_kg');
$sheet->setCellValue('H5', 'ctd_emdev');
$sheet->setCellValue('I5', 'umb');
$sheet->setCellValue('J5', 'ce');
$sheet->setCellValue('K5', 'valor_emdev');
$sheet->setCellValue('L5', 'factor_conversin');

// Agregar datos de prueba en la fila 6
$sheet->setCellValue('A6', '001');
$sheet->setCellValue('B6', 'Proveedor Test');
$sheet->setCellValue('C6', 'MAT001');
$sheet->setCellValue('D6', 'Categoria A');
$sheet->setCellValue('E6', 'Material de prueba');
$sheet->setCellValue('F6', 'Enero');
$sheet->setCellValue('G6', '100,50');
$sheet->setCellValue('H6', '50,25');
$sheet->setCellValue('I6', 'KG');
$sheet->setCellValue('J6', 'CE001');
$sheet->setCellValue('K6', '1.500,75');
$sheet->setCellValue('L6', '2,5');

// Agregar otra fila de datos
$sheet->setCellValue('A7', '002');
$sheet->setCellValue('B7', 'Proveedor Test 2');
$sheet->setCellValue('C7', 'MAT002');
$sheet->setCellValue('D7', 'Categoria B');
$sheet->setCellValue('E7', 'Material de prueba 2');
$sheet->setCellValue('F7', 'Febrero');
$sheet->setCellValue('G7', '200,75');
$sheet->setCellValue('H7', '75,50');
$sheet->setCellValue('I7', 'KG');
$sheet->setCellValue('J7', 'CE002');
$sheet->setCellValue('K7', '2.000,25');
$sheet->setCellValue('L7', '3,0');

$writer = new Xlsx($spreadsheet);
$writer->save('archivo_prueba.xlsx');

echo "Archivo de prueba 'archivo_prueba.xlsx' creado exitosamente.\n";
echo "Puedes usar este archivo para probar la importación.\n";
