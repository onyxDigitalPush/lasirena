<?php

// Test directo del método importarArchivo del ProveedorController

require_once __DIR__ . '/vendor/autoload.php';

// Simular el entorno de Laravel
$_ENV['APP_ENV'] = 'local';

// Agregar las rutas necesarias para Laravel
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

// Crear un mock del Log para capturar los mensajes
class MockLog {
    public static function info($message) {
        echo "[INFO] " . $message . "\n";
    }
    
    public static function error($message) {
        echo "[ERROR] " . $message . "\n";
    }
}

// Reemplazar temporalmente la clase Log
class_alias('MockLog', 'Log');

echo "=== TEST DIRECTO DEL CONTROLADOR ===\n\n";

try {
    // Incluir el controlador
    require_once __DIR__ . '/app/Http/Controllers/MainApp/ProveedorController.php';
    
    // Crear instancia del controlador
    $controller = new \App\Http\Controllers\MainApp\ProveedorController();
    
    // Crear mock del archivo
    $archivoPath = __DIR__ . '/archivo_prueba_con_datos.xlsx';
    
    // Verificar que el archivo existe
    if (!file_exists($archivoPath)) {
        throw new Exception("Archivo no encontrado: " . $archivoPath);
    }
    
    echo "Archivo encontrado: " . $archivoPath . "\n";
    echo "Tamaño: " . filesize($archivoPath) . " bytes\n\n";
    
    // Crear mock del UploadedFile
    $uploadedFile = new UploadedFile(
        $archivoPath,
        'archivo_prueba_con_datos.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true // test mode
    );
    
    // Crear mock del Request
    $request = new Request();
    $request->files->set('archivo', $uploadedFile);
    
    echo "Llamando al método importarArchivo...\n\n";
    
    // Llamar al método
    $response = $controller->importarArchivo($request);
    
    echo "\n=== RESPUESTA ===\n";
    echo "Tipo de respuesta: " . get_class($response) . "\n";
    
    if (method_exists($response, 'getContent')) {
        echo "Contenido de la respuesta:\n";
        echo $response->getContent() . "\n";
    }
    
    if (method_exists($response, 'getStatusCode')) {
        echo "Código de estado: " . $response->getStatusCode() . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

?>
