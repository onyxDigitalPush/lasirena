<?php
// Test simple para verificar la funcionalidad de búsqueda de productos por código

require_once 'bootstrap/app.php';

use Illuminate\Http\Request;
use App\Http\Controllers\MainApp\MaterialKiloController;

// Simular una request
$request = new Request();
$request->merge(['codigo' => 'TEST001']);

$controller = new MaterialKiloController();

try {
    $result = $controller->buscarProductoPorCodigo($request);
    echo "Resultado de la búsqueda:\n";
    echo $result->getContent();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
