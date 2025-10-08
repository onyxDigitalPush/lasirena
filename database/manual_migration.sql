-- Agregar campos a tabla analiticas (Análisis de Agua) - Solo si no existen
ALTER TABLE `gp_ls_analiticas` 
ADD COLUMN IF NOT EXISTS `numero_factura` VARCHAR(255) NULL AFTER `tipo_analitica`,
ADD COLUMN IF NOT EXISTS `E_coli_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `E_coli_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `coliformes_totales_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `coliformes_totales_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `enterococos_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `enterococos_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `amonio_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `amonio_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `nitritos_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `nitritos_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `color_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `color_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `sabor_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `sabor_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `olor_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `olor_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `conductividad_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `conductividad_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `ph_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `ph_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `turbidez_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `turbidez_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `cloro_libre_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `cloro_libre_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `cloro_combinado_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `cloro_combinado_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `cloro_total_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `cloro_total_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `cobre_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `cobre_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `cromo_total_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `cromo_total_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `niquel_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `niquel_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `hierro_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `hierro_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `cloruro_vinilo_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `cloruro_vinilo_resultado` ENUM('correcto', 'falso') NULL,
ADD COLUMN IF NOT EXISTS `bisfenol_valor` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `bisfenol_resultado` ENUM('correcto', 'falso') NULL;

-- Agregar campos a tabla tendencias_micro
ALTER TABLE `gp_ls_tendencias_micro`
ADD COLUMN IF NOT EXISTS `lote_proveedor` VARCHAR(255) NULL AFTER `te_proveedor`,
ADD COLUMN IF NOT EXISTS `lote_sap` VARCHAR(255) NULL AFTER `lote_proveedor`,
ADD COLUMN IF NOT EXISTS `fcp` VARCHAR(255) NULL AFTER `lote_sap`,
ADD COLUMN IF NOT EXISTS `salmonella_presencia` VARCHAR(255) NULL AFTER `salmonella_resultado`,
ADD COLUMN IF NOT EXISTS `salmonella_recuento` VARCHAR(255) NULL AFTER `salmonella_presencia`;

-- Agregar campos a tabla tendencias_superficie
ALTER TABLE `gp_ls_tendencias_superficie`
ADD COLUMN IF NOT EXISTS `referencia` VARCHAR(255) NULL AFTER `proveedor_id`,
ADD COLUMN IF NOT EXISTS `numero_muestra` VARCHAR(255) NULL AFTER `referencia`;
