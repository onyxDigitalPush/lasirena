<?php
// Archivo de prueba para descargar archivos directamente
// URL: http://localhost/lasirena/public/test_descarga_directa.php?archivo=NOMBRE_ARCHIVO

if (!isset($_GET['archivo'])) {
    die('Parámetro archivo requerido');
}

$nombreArchivo = $_GET['archivo'];
$rutaArchivo = __DIR__ . '/../storage/app/public/incidencias/' . $nombreArchivo;

if (!file_exists($rutaArchivo)) {
    die('Archivo no encontrado: ' . $rutaArchivo);
}

// Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_end_clean();
}

// Obtener información del archivo
$mimeType = mime_content_type($rutaArchivo);
$fileSize = filesize($rutaArchivo);
$nombreOriginal = $nombreArchivo;

// Configurar headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Content-Disposition: attachment; filename="' . $nombreOriginal . '"');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Enviar el archivo
readfile($rutaArchivo);
exit;
?>