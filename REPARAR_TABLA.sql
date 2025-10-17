-- =====================================================
-- REPARAR TABLA gp_ls_devoluciones_proveedores
-- =====================================================
-- Ejecuta estos comandos en phpMyAdmin uno por uno
-- =====================================================

-- Paso 1: Eliminar la tabla corrupta
DROP TABLE IF EXISTS `gp_ls_devoluciones_proveedores`;

-- Paso 2: Crear la tabla nueva
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Paso 3: Verificar que la tabla se creó correctamente
CHECK TABLE `gp_ls_devoluciones_proveedores`;

-- =====================================================
-- LISTO! Ahora puedes subir el Excel sin problemas
-- =====================================================
