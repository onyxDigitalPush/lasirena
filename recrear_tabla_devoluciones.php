<?php
/**
 * Script para recrear la tabla gp_ls_devoluciones_proveedores
 * ADVERTENCIA: Este script eliminará y recreará la tabla
 * Ejecutar: php recrear_tabla_devoluciones.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RECREACIÓN DE TABLA gp_ls_devoluciones_proveedores ===\n\n";
echo "⚠️  ADVERTENCIA: Este proceso eliminará la tabla actual y la recreará\n";
echo "    Si tienes datos importantes, haz un backup primero\n\n";

try {
    // Verificar si existe algún backup
    echo "1. Verificando estado actual...\n";
    $tableExists = DB::select("SHOW TABLES LIKE 'gp_ls_devoluciones_proveedores'");
    
    if (!empty($tableExists)) {
        echo "   ✓ Tabla existe en el registro\n";
        
        // Intentar contar registros
        try {
            $count = DB::table('gp_ls_devoluciones_proveedores')->count();
            echo "   ✓ Registros actuales: {$count}\n";
        } catch (\Exception $e) {
            echo "   ✗ No se pueden leer los registros (tabla corrupta)\n";
        }
    }
    
    echo "\n2. Eliminando tabla corrupta...\n";
    DB::statement('DROP TABLE IF EXISTS gp_ls_devoluciones_proveedores');
    echo "   ✓ Tabla eliminada\n";
    
    echo "\n3. Creando nueva tabla...\n";
    DB::statement("
        CREATE TABLE `gp_ls_devoluciones_proveedores` (
          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `codigo_producto` varchar(255) DEFAULT NULL,
          `codigo_proveedor` varchar(255) DEFAULT NULL,
          `nombre_proveedor` varchar(255) DEFAULT NULL,
          `descripcion_producto` text DEFAULT NULL,
          `descripcion_queja` text DEFAULT NULL,
          `fecha_reclamacion` datetime DEFAULT NULL,
          `fecha_inicio` date DEFAULT NULL,
          `fecha_fin` date DEFAULT NULL,
          `clasificacion_incidencia` varchar(50) DEFAULT NULL,
          `clasificacion_devolucion` varchar(50) DEFAULT NULL,
          `tipo_reclamacion` varchar(100) DEFAULT NULL,
          `tipo_reclamacion_grave` varchar(255) DEFAULT NULL,
          `descripcion_motivo` text DEFAULT NULL,
          `especificacion_motivo_reclamacion_leve` text DEFAULT NULL,
          `especificacion_motivo_reclamacion_grave` varchar(255) DEFAULT NULL,
          `recuperamos_objeto_extraño` varchar(10) DEFAULT NULL,
          `nombre_tienda` varchar(255) DEFAULT NULL,
          `no_queja` varchar(100) DEFAULT NULL,
          `origen` varchar(255) DEFAULT NULL,
          `descripcion_queja_detalle` text DEFAULT NULL,
          `lote_sirena` varchar(255) DEFAULT NULL,
          `lote_proveedor` varchar(255) DEFAULT NULL,
          `informe_a_proveedor` varchar(10) DEFAULT NULL,
          `informe` text DEFAULT NULL,
          `fecha_envio_proveedor` date DEFAULT NULL,
          `fecha_respuesta_proveedor` date DEFAULT NULL,
          `fecha_reclamacion_respuesta` date DEFAULT NULL,
          `abierto` varchar(10) DEFAULT 'Si',
          `informe_respuesta` text DEFAULT NULL,
          `comentarios` text DEFAULT NULL,
          `año` int(11) DEFAULT NULL,
          `mes` int(11) DEFAULT NULL,
          `np` varchar(100) DEFAULT NULL,
          `top100fy2` varchar(10) DEFAULT NULL,
          `archivos` text DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_proveedor` (`codigo_proveedor`),
          KEY `idx_año_mes` (`año`, `mes`),
          KEY `idx_clasificacion` (`clasificacion_incidencia`),
          KEY `idx_fecha_inicio` (`fecha_inicio`),
          KEY `idx_fecha_fin` (`fecha_fin`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✓ Tabla creada exitosamente\n";
    
    echo "\n4. Verificando nueva tabla...\n";
    $check = DB::select('CHECK TABLE gp_ls_devoluciones_proveedores');
    foreach ($check as $result) {
        echo "   - {$result->Msg_type}: {$result->Msg_text}\n";
    }
    
    echo "\n=== PROCESO COMPLETADO ===\n";
    echo "✓ La tabla ha sido recreada correctamente\n";
    echo "✓ Ahora puedes subir el Excel sin problemas\n\n";
    
} catch (\Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "\nPor favor, ejecuta esto manualmente en phpMyAdmin:\n\n";
    echo "DROP TABLE IF EXISTS gp_ls_devoluciones_proveedores;\n";
    echo "\nLuego copia y pega el CREATE TABLE que está en este script.\n";
}
