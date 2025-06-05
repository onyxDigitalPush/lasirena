<?php
echo "<h2>Verificación de extensiones para PhpSpreadsheet</h2>";

$extensiones_necesarias = array(
    'ctype', 'dom', 'fileinfo', 'gd', 'iconv', 'libxml', 
    'mbstring', 'simplexml', 'xml', 'xmlreader', 'xmlwriter', 
    'zip', 'zlib'
);

foreach ($extensiones_necesarias as $ext) {
    $status = extension_loaded($ext) ? "✅ Cargada" : "❌ NO cargada";
    echo "- " . $ext . ": " . $status . "<br>";
}

echo "<h3>Versión de PHP: " . PHP_VERSION . "</h3>";

echo "<h3>Test básico de PhpSpreadsheet:</h3>";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo "✅ PhpSpreadsheet cargado correctamente<br>";
    
    // Test de creación simple
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    echo "✅ Spreadsheet creado correctamente<br>";
    
    // Test de IOFactory
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    echo "✅ IOFactory disponible<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
