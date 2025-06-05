<?php
echo "=== TEST DE UPLOAD XLSX ===\n";

// Configurar datos del formulario
$archivo = __DIR__ . '/archivo_prueba_con_datos.xlsx';
$url = 'http://localhost/lasirena/public/test-upload';

if (!file_exists($archivo)) {
    echo "Error: Archivo no encontrado: $archivo\n";
    exit(1);
}

echo "Archivo a subir: $archivo\n";
echo "Tamaño: " . filesize($archivo) . " bytes\n";

// Primero obtener el token CSRF
echo "\n1. Obteniendo token CSRF...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, sys_get_temp_dir() . '/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, sys_get_temp_dir() . '/cookies.txt');
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Error en cURL: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit(1);
}

// Extraer token CSRF
preg_match('/name="_token" value="([^"]+)"/', $response, $matches);
if (!isset($matches[1])) {
    echo "Error: No se pudo obtener el token CSRF\n";
    echo "Respuesta del servidor:\n" . substr($response, 0, 500) . "...\n";
    curl_close($ch);
    exit(1);
}

$token = $matches[1];
echo "Token CSRF obtenido: $token\n";

// Ahora hacer el upload
echo "\n2. Subiendo archivo...\n";
$postData = [
    '_token' => $token,
    'archivo' => new CURLFile($archivo, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'archivo_prueba_con_datos.xlsx')
];

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Código de respuesta HTTP: $httpCode\n";

if (curl_errno($ch)) {
    echo "Error en cURL: " . curl_error($ch) . "\n";
} else {
    echo "Upload completado!\n";
    echo "Respuesta del servidor:\n";
    echo substr($response, 0, 1000) . "\n";
    
    if (strlen($response) > 1000) {
        echo "... (respuesta truncada)\n";
    }
}

curl_close($ch);

echo "\n3. Revisando logs...\n";
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -20); // Últimas 20 líneas
    
    foreach ($recentLines as $line) {
        if (!empty(trim($line))) {
            echo $line . "\n";
        }
    }
} else {
    echo "No se encontró archivo de log en: $logFile\n";
}

echo "\n=== FIN TEST ===\n";
?>
