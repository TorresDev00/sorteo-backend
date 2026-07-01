-- =============================================
-- SISTEMA EXPRESS DE GESTIÓN DE SORTEOS
-- Base de datos: sorteo
-- Versión: 3.0 (con preselección y cantidad_reservada)
-- =============================================

-- Configuración de caracteres
ALTER DATABASE `sorteo` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 0;

-- Limpiar tablas existentes (orden por dependencias)
DROP TABLE IF EXISTS `registros`;
DROP TABLE IF EXISTS `liberacion_premios`;
DROP TABLE IF EXISTS `liberaciones_semanales`;
DROP TABLE IF EXISTS `premios`;
DROP TABLE IF EXISTS `distribuidores`;
DROP TABLE IF EXISTS `sorteos`;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- 1. TABLA: sorteos (Campañas)
-- =============================================
CREATE TABLE `sorteos` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. TABLA: premios (Inventario global de premios)
-- =============================================
CREATE TABLE `premios` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sorteo_id` BIGINT UNSIGNED NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `tipo` ENUM('electrodomesticos', 'merch', 'experiencia_de_marca') NOT NULL,
  `cantidad_total` INT NOT NULL COMMENT 'Stock total de este premio en la campaña',
  `cantidad_disponible` INT NOT NULL COMMENT 'Stock restante sin entregar',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_premios_sorteo_id` 
    FOREIGN KEY (`sorteo_id`) REFERENCES `sorteos` (`id`) 
    ON DELETE CASCADE,
  INDEX `idx_premio_tipo` (`tipo`),
  INDEX `idx_premio_sorteo` (`sorteo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. TABLA: liberaciones_semanales (Cabecera de liberación por semana)
-- =============================================
CREATE TABLE `liberaciones_semanales` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sorteo_id` BIGINT UNSIGNED NOT NULL,
  `semana` INT NOT NULL COMMENT 'Número de semana del sorteo',
  `fecha_liberacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notas` TEXT NULL COMMENT 'Notas del administrador sobre esta liberación',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_lib_sorteo` 
    FOREIGN KEY (`sorteo_id`) REFERENCES `sorteos` (`id`) 
    ON DELETE CASCADE,
  UNIQUE INDEX `idx_sorteo_semana` (`sorteo_id`, `semana`),
  INDEX `idx_lib_semana` (`semana`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. TABLA: liberacion_premios 
-- ⚠️ CAMBIO v3.0: Agregado cantidad_reservada
-- =============================================
CREATE TABLE `liberacion_premios` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `liberacion_semanal_id` BIGINT UNSIGNED NOT NULL,
  `premio_id` BIGINT UNSIGNED NOT NULL,
  `cantidad` INT NOT NULL COMMENT 'Cantidad de este premio liberada para esta semana',
  `cantidad_entregada` INT NOT NULL DEFAULT 0 COMMENT 'Premios confirmados (admin aprobó factura)',
  `cantidad_reservada` INT NOT NULL DEFAULT 0 COMMENT 'Premios asignados por algoritmo, pendientes de validación',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_lp_liberacion` 
    FOREIGN KEY (`liberacion_semanal_id`) REFERENCES `liberaciones_semanales` (`id`) 
    ON DELETE CASCADE,
  CONSTRAINT `fk_lp_premio` 
    FOREIGN KEY (`premio_id`) REFERENCES `premios` (`id`) 
    ON DELETE CASCADE,
  UNIQUE INDEX `idx_lib_premio` (`liberacion_semanal_id`, `premio_id`),
  INDEX `idx_lp_premio` (`premio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. TABLA: registros (Participantes del Sorteo)
-- ⚠️ CAMBIO v3.0: ENUM con 4 estados + sin unique(cedula, semana)
-- =============================================
CREATE TABLE `registros` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sorteo_id` BIGINT UNSIGNED NOT NULL,
  `cedula` VARCHAR(20) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50) NOT NULL,
  `direccion` TEXT NULL,
  `lugar_compra` VARCHAR(255) NOT NULL,
  `factura_imagen` VARCHAR(255) NOT NULL,
  `semana` INT NOT NULL COMMENT 'Semana en la que participa',
  `estado` ENUM('pendiente', 'preseleccionado', 'verificado', 'rechazado') 
           NOT NULL DEFAULT 'pendiente' 
           COMMENT 'pendiente=perdió, preseleccionado=ganó(pendiente validar), verificado=ganó confirmado, rechazado=factura inválida',
  `ganador` TINYINT(1) NOT NULL DEFAULT 0,
  `premio_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `liberacion_premio_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Referencia al lote semanal del premio reservado/entregado',
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_registros_sorteo_id` 
    FOREIGN KEY (`sorteo_id`) REFERENCES `sorteos` (`id`) 
    ON DELETE CASCADE,
  CONSTRAINT `fk_registros_premio_id` 
    FOREIGN KEY (`premio_id`) REFERENCES `premios` (`id`) 
    ON DELETE SET NULL,
  CONSTRAINT `fk_registros_liberacion_premio_id` 
    FOREIGN KEY (`liberacion_premio_id`) REFERENCES `liberacion_premios` (`id`) 
    ON DELETE SET NULL,
  -- ÍNDICES (sin UNIQUE en cedula+semana — permite reintentos)
  INDEX `idx_participante_cedula` (`cedula`),
  INDEX `idx_registro_estado` (`estado`),
  INDEX `idx_registro_semana` (`semana`),
  INDEX `idx_registro_ganador` (`ganador`),
  INDEX `idx_registro_sorteo_semana` (`sorteo_id`, `semana`),
  INDEX `idx_registro_estado_ganador` (`estado`, `ganador`)  -- Clave para query sinEvaluar
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 6. TABLA: distribuidores (Módulo de captación de aliados comerciales)
-- =============================================
CREATE TABLE `distribuidores` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre_comercial` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50) NOT NULL,
  `estado_ubicacion` VARCHAR(100) NOT NULL,
  `mensaje` TEXT NULL,
  `estatus_lead` ENUM('nuevo', 'contactado', 'rechazado') NOT NULL DEFAULT 'nuevo' 
                COMMENT 'nuevo, contactado, rechazado',
  `notas_administrador` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE INDEX `idx_distribuidor_email` (`email`),
  INDEX `idx_distribuidor_estado` (`estado_ubicacion`),
  INDEX `idx_distribuidor_estatus` (`estatus_lead`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DATOS DE EJEMPLO (Opcional)
-- =============================================

-- Insertar sorteo de ejemplo
INSERT INTO `sorteos` (`nombre`, `fecha_inicio`, `fecha_fin`, `activo`, `created_at`, `updated_at`) VALUES
('Sorteo Aniversario 2026', '2026-06-01', '2026-08-31', 1, NOW(), NOW());

-- Insertar premios de ejemplo
-- electrodomesticos (180 en total)
INSERT INTO `premios` (`sorteo_id`, `nombre`, `tipo`, `cantidad_total`, `cantidad_disponible`, `created_at`, `updated_at`) VALUES
(1, 'Televisor LED 32"', 'electrodomesticos', 25, 25, NOW(), NOW()),
(1, 'Lavadora portátil', 'electrodomesticos', 20, 20, NOW(), NOW()),
(1, 'Licuadora de alta potencia', 'electrodomesticos', 35, 35, NOW(), NOW()),
(1, 'Microondas digital', 'electrodomesticos', 30, 30, NOW(), NOW()),
(1, 'Cafetera eléctrica', 'electrodomesticos', 40, 40, NOW(), NOW()),
(1, 'Plancha a vapor', 'electrodomesticos', 30, 30, NOW(), NOW());

-- merch (520 en total)
INSERT INTO `premios` (`sorteo_id`, `nombre`, `tipo`, `cantidad_total`, `cantidad_disponible`, `created_at`, `updated_at`) VALUES
(1, 'Camiseta edición limitada', 'merch', 120, 120, NOW(), NOW()),
(1, 'Gorra bordada', 'merch', 100, 100, NOW(), NOW()),
(1, 'Termo personalizado', 'merch', 100, 100, NOW(), NOW()),
(1, 'Mochila ecológica', 'merch', 80, 80, NOW(), NOW()),
(1, 'Kit de stickers y llaveros', 'merch', 120, 120, NOW(), NOW());

-- experiencia_de_marca (100 en total)
INSERT INTO `premios` (`sorteo_id`, `nombre`, `tipo`, `cantidad_total`, `cantidad_disponible`, `created_at`, `updated_at`) VALUES
(1, 'Cena exclusiva con el CEO', 'experiencia_de_marca', 10, 10, NOW(), NOW()),
(1, 'Tour VIP por las instalaciones', 'experiencia_de_marca', 20, 20, NOW(), NOW()),
(1, 'Día de spa corporativo', 'experiencia_de_marca', 15, 15, NOW(), NOW()),
(1, 'Taller de cocina con chef invitado', 'experiencia_de_marca', 25, 25, NOW(), NOW()),
(1, 'Sesión de fotos profesional', 'experiencia_de_marca', 30, 30, NOW(), NOW());

-- Semana 1: liberación de ejemplo
INSERT INTO `liberaciones_semanales` (`sorteo_id`, `semana`, `notas`, `created_at`, `updated_at`) VALUES
(1, 1, 'Primera semana - enfoque en merch y electrodomésticos pequeños', NOW(), NOW());

INSERT INTO `liberacion_premios` (`liberacion_semanal_id`, `premio_id`, `cantidad`, `created_at`, `updated_at`) VALUES
(1, 1, 5, NOW(), NOW()),
(1, 2, 5, NOW(), NOW()),
(1, 3, 20, NOW(), NOW()),
(1, 4, 15, NOW(), NOW()),
(1, 5, 30, NOW(), NOW()),
(1, 6, 25, NOW(), NOW()),
(1, 7, 50, NOW(), NOW()),
(1, 8, 50, NOW(), NOW()),
(1, 9, 50, NOW(), NOW()),
(1, 10, 40, NOW(), NOW()),
(1, 11, 60, NOW(), NOW()),
(1, 12, 3, NOW(), NOW()),
(1, 13, 10, NOW(), NOW()),
(1, 14, 5, NOW(), NOW()),
(1, 15, 7, NOW(), NOW()),
(1, 16, 5, NOW(), NOW());

-- =============================================
-- CONSULTAS ÚTILES
-- =============================================

-- Disponibilidad REAL por premio (descontando reservados):
-- SELECT p.nombre,
--        lp.cantidad,
--        lp.cantidad_entregada,
--        lp.cantidad_reservada,
--        (lp.cantidad - lp.cantidad_entregada - lp.cantidad_reservada) AS disponible_real
-- FROM liberacion_premios lp
-- JOIN premios p ON p.id = lp.premio_id
-- WHERE lp.liberacion_semanal_id = 1;

-- Conteo de sinEvaluar (para el algoritmo):
-- SELECT COUNT(*) AS sin_evaluar
-- FROM registros
-- WHERE sorteo_id = 1 AND semana = 1 
-- AND estado IN ('pendiente', 'preseleccionado');