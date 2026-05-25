-- ============================================================
--  Miss Whitney · SQL completo para BD limpia
--  Ejecutar en phpMyAdmin > misswhitney > pestaña SQL
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ── Contador de referencias ───────────────────────────────
CREATE TABLE IF NOT EXISTS `contador_referencias` (
  `serie`          VARCHAR(20)      NOT NULL,
  `ultimo_numero`  INT UNSIGNED     NOT NULL DEFAULT 0,
  PRIMARY KEY (`serie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `contador_referencias` (`serie`, `ultimo_numero`) VALUES
  ('MW-2024', 0), ('MW-2025', 0), ('MW-2026', 0), ('MW-2027', 0);

-- ── Solicitudes de factura (clientes) ─────────────────────
CREATE TABLE IF NOT EXISTS `solicitudes_factura` (
  `id`              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `referencia`      VARCHAR(20)      NOT NULL,
  `tipo_receptor`   ENUM('particular','empresa') NOT NULL DEFAULT 'particular',
  `nombre_cliente`  VARCHAR(150)     NOT NULL,
  `nombre_empresa`  VARCHAR(150)     NULL,
  `nif_cif`         VARCHAR(15)      NOT NULL,
  `email`           VARCHAR(200)     NOT NULL,
  `direccion`       VARCHAR(255)     NOT NULL,
  `codigo_postal`   VARCHAR(10)      NOT NULL,
  `ciudad`          VARCHAR(100)     NOT NULL,
  `fecha_consumo`   DATE             NOT NULL,
  `importe_ticket`  DECIMAL(10,2)    NOT NULL,
  `ticket_filename` VARCHAR(255)     NULL,
  `ticket_path`     VARCHAR(500)     NULL,
  `ticket_mime`     VARCHAR(80)      NULL,
  `observaciones`   VARCHAR(500)     NULL,
  `acepta_lopd`     TINYINT(1)       NOT NULL DEFAULT 1,
  `ip_solicitante`  VARCHAR(45)      NULL,
  `user_agent`      VARCHAR(500)     NULL,
  `created_at`      DATETIME         NULL,
  `updated_at`      DATETIME         NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `solicitudes_factura_referencia_unique` (`referencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Facturas ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `facturas` (
  `id`                  BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `id_solicitud`        BIGINT UNSIGNED  NOT NULL,
  `numero_factura`      VARCHAR(20)      NOT NULL,
  `receptor_nombre`     VARCHAR(150)     NOT NULL,
  `receptor_empresa`    VARCHAR(150)     NULL,
  `receptor_nif`        VARCHAR(15)      NOT NULL,
  `receptor_email`      VARCHAR(200)     NOT NULL,
  `receptor_direccion`  VARCHAR(255)     NOT NULL DEFAULT '',
  `receptor_cp`         VARCHAR(10)      NOT NULL DEFAULT '00000',
  `receptor_ciudad`     VARCHAR(100)     NOT NULL DEFAULT '',
  `base_imponible`      DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `porcentaje_iva`      DECIMAL(5,2)     NOT NULL DEFAULT 10.00,
  `cuota_iva`           DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `total_factura`       DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `concepto`            VARCHAR(500)     NOT NULL DEFAULT 'Consumicion en Miss Whitney',
  `fecha_consumo`       DATE             NOT NULL,
  `estado`              ENUM('pendiente','procesando','emitida','cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_emision`       DATETIME         NULL,
  `pdf_path`            VARCHAR(500)     NULL,
  `notas_internas`      VARCHAR(500)     NULL,
  `admin_usuario`       VARCHAR(80)      NULL,
  `created_at`          DATETIME         NULL,
  `updated_at`          DATETIME         NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facturas_numero_factura_unique` (`numero_factura`),
  CONSTRAINT `facturas_id_solicitud_foreign`
    FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes_factura` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla de migraciones de Laravel ───────────────────────
CREATE TABLE IF NOT EXISTS `migrations` (
  `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255)  NOT NULL,
  `batch`     INT           NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Marcar migraciones como ejecutadas para que artisan no las repita
INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
  ('0001_01_01_000000_create_users_table', 1),
  ('0001_01_01_000001_create_cache_table', 1),
  ('0001_01_01_000002_create_jobs_table', 1),
  ('2024_01_01_000010_create_contador_referencias_table', 1),
  ('2024_01_01_000011_create_solicitudes_factura_table', 1),
  ('2024_01_01_000012_create_facturas_table', 1);

-- ── Tablas de sesiones y caché de Laravel ─────────────────
CREATE TABLE IF NOT EXISTS `sessions` (
  `id`             VARCHAR(255)     NOT NULL,
  `user_id`        BIGINT UNSIGNED  NULL,
  `ip_address`     VARCHAR(45)      NULL,
  `user_agent`     TEXT             NULL,
  `payload`        LONGTEXT         NOT NULL,
  `last_activity`  INT              NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
  `key`        VARCHAR(255)  NOT NULL,
  `value`      MEDIUMTEXT    NOT NULL,
  `expiration` INT           NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key`        VARCHAR(255)  NOT NULL,
  `owner`      VARCHAR(255)  NOT NULL,
  `expiration` INT           NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
