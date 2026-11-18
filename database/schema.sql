SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
DROP DATABASE IF EXISTS `vetmadri_db`;

-- ============================================================================
-- ESQUEMA DE BASE DE DATOS EMPRESARIAL - VETERINARIA MADRI
-- Motor: InnoDB | Charset: utf8mb4_unicode_ci | Estándar: 3FN + OWASP
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `vetmadri_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `vetmadri_db`;

-- ----------------------------------------------------------------------------
-- 1. TABLA: usuarios
-- Almacena al administrador (veterinario) y deja preparada la arquitectura
-- para cuentas de clientes en la versión 2.0.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre_completo` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `rol` ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_usuarios_email` (`email`),
    INDEX `idx_usuarios_rol_activo` (`rol`, `activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. TABLA: mascotas
-- Datos obligatorios mínimos: codigo_unico, nombre_mascota, especie, nombre_dueno.
-- Incluye foto_webp opcional y modificable, y usuario_id opcional para v2.0.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mascotas` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `codigo_unico` VARCHAR(12) NOT NULL COMMENT 'Formato aleatorio ej. VM-7K4P9',
    `usuario_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Reservado para asociación de cuenta en v2.0',
    `nombre_mascota` VARCHAR(80) NOT NULL,
    `especie` ENUM('Perro', 'Gato', 'Otro') NOT NULL DEFAULT 'Perro',
    `raza` VARCHAR(80) NULL DEFAULT NULL,
    `sexo` ENUM('Macho', 'Hembra', 'Sin especificar') NULL DEFAULT NULL,
    `edad_aprox` VARCHAR(40) NULL DEFAULT NULL,
    `peso_actual` DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Peso en kg',
    `esterilizado` ENUM('Sí', 'No', 'Se desconoce') NULL DEFAULT NULL,
    `alergias_notas` TEXT NULL DEFAULT NULL,
    `foto_webp` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Archivo .webp en /assets/img/mascotas/',
    `nombre_dueno` VARCHAR(120) NOT NULL,
    `telefono_contacto` VARCHAR(20) NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_mascotas_codigo` (`codigo_unico`),
    INDEX `idx_mascotas_busqueda` (`nombre_mascota`, `nombre_dueno`),
    INDEX `idx_mascotas_telefono` (`telefono_contacto`),
    CONSTRAINT `fk_mascotas_usuario`
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. TABLA: historial_clinico
-- Línea del tiempo médica de cada mascota (vacunas, desparasitaciones, consultas).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `historial_clinico` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mascota_id` INT UNSIGNED NOT NULL,
    `veterinario_id` INT UNSIGNED NULL DEFAULT NULL,
    `fecha_atencion` DATE NOT NULL,
    `tipo_atencion` ENUM('Vacunación', 'Desparasitación', 'Consulta General', 'Cirugía / Otro') NOT NULL,
    `titulo` VARCHAR(150) NOT NULL,
    `descripcion` TEXT NULL DEFAULT NULL,
    `peso_consulta` DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Peso registrado en esa visita (kg)',
    `proximo_refuerzo` DATE NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_historial_mascota_fecha` (`mascota_id`, `fecha_atencion` DESC),
    CONSTRAINT `fk_historial_mascota`
        FOREIGN KEY (`mascota_id`) REFERENCES `mascotas` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_historial_veterinario`
        FOREIGN KEY (`veterinario_id`) REFERENCES `usuarios` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. TABLA: productos
-- Catálogo vitrina administrable con precios en MXN (IVA incluido) y estado.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `productos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sku` VARCHAR(20) NOT NULL,
    `nombre` VARCHAR(150) NOT NULL,
    `categoria` VARCHAR(80) NOT NULL,
    `descripcion` TEXT NULL DEFAULT NULL,
    `precio_mxn` DECIMAL(10,2) NOT NULL,
    `disponibilidad` ENUM('Disponible', 'Pocas unidades', 'Agotado') NOT NULL DEFAULT 'Disponible',
    `imagen_webp` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Archivo .webp en /assets/img/productos/',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_productos_sku` (`sku`),
    INDEX `idx_productos_visibles` (`activo`, `categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. TABLA: auditoria_accesos
-- Control de Rate Limiting contra fuerza bruta en login y enumeración de folios.
-- Guarda el hash SHA-256 de la IP para cumplir con privacidad por diseño.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auditoria_accesos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_hash` CHAR(64) NOT NULL,
    `accion` VARCHAR(40) NOT NULL COMMENT 'login_admin o busqueda_folio',
    `intentos` INT UNSIGNED NOT NULL DEFAULT 1,
    `bloqueado_hasta` DATETIME NULL DEFAULT NULL,
    `ultimo_intento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_auditoria_ip_accion` (`ip_hash`, `accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;