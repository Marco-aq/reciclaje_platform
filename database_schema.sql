-- EcoCusco Database Schema
-- Base de datos para la plataforma de gestión de residuos sólidos urbanos

-- Configuración inicial
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `reciclaje_platform` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `reciclaje_platform`;

-- Tabla de usuarios
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) NULL,
  `direccion` text NULL,
  `rol` enum('usuario','administrador','supervisor') NOT NULL DEFAULT 'usuario',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(255) NULL,
  `ultimo_acceso` datetime NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_activo` (`activo`),
  KEY `idx_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tipos de residuos
DROP TABLE IF EXISTS `tipos_residuos`;
CREATE TABLE `tipos_residuos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NULL,
  `puntos_por_kg` decimal(5,2) NOT NULL DEFAULT 10.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar tipos de residuos básicos
INSERT INTO `tipos_residuos` (`nombre`, `descripcion`, `puntos_por_kg`) VALUES
('Plástico', 'Botellas, envases y otros plásticos reciclables', 10.00),
('Papel', 'Papel, cartón y productos de papel', 8.00),
('Vidrio', 'Botellas y envases de vidrio', 12.00),
('Metal', 'Latas de aluminio y otros metales', 15.00),
('Orgánico', 'Restos de comida y material orgánico compostable', 5.00);

-- Tabla de reportes de reciclaje
DROP TABLE IF EXISTS `reportes`;
CREATE TABLE `reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tipo_residuo_id` int(11) NOT NULL,
  `descripcion` text NULL,
  `cantidad` decimal(8,2) NOT NULL,
  `unidad` varchar(10) NOT NULL DEFAULT 'kg',
  `ubicacion` varchar(255) NULL,
  `latitud` decimal(10,8) NULL,
  `longitud` decimal(11,8) NULL,
  `foto_path` varchar(255) NULL,
  `estado` enum('pendiente','procesado','rechazado') NOT NULL DEFAULT 'pendiente',
  `puntos_obtenidos` int(11) NOT NULL DEFAULT 0,
  `notas_admin` text NULL,
  `procesado_por` int(11) NULL,
  `fecha_procesado` datetime NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_tipo_residuo_id` (`tipo_residuo_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tipo_residuo_id`) REFERENCES `tipos_residuos` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`procesado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de puntos de usuarios
DROP TABLE IF EXISTS `puntos_usuarios`;
CREATE TABLE `puntos_usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `puntos_totales` int(11) NOT NULL DEFAULT 0,
  `puntos_canjeados` int(11) NOT NULL DEFAULT 0,
  `puntos_disponibles` int(11) NOT NULL DEFAULT 0,
  `nivel` varchar(50) NOT NULL DEFAULT 'Bronce',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario` (`usuario_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de historial de puntos
DROP TABLE IF EXISTS `historial_puntos`;
CREATE TABLE `historial_puntos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `reporte_id` int(11) NULL,
  `tipo` enum('ganancia','canje','ajuste') NOT NULL,
  `puntos` int(11) NOT NULL,
  `descripcion` varchar(255) NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_reporte_id` (`reporte_id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reporte_id`) REFERENCES `reportes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración del sistema
DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL UNIQUE,
  `valor` text NULL,
  `descripcion` text NULL,
  `tipo` enum('string','integer','boolean','json') NOT NULL DEFAULT 'string',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuraciones básicas
INSERT INTO `configuracion` (`clave`, `valor`, `descripcion`, `tipo`) VALUES
('app_name', 'EcoCusco', 'Nombre de la aplicación', 'string'),
('puntos_minimos_canje', '100', 'Puntos mínimos requeridos para canje', 'integer'),
('max_reportes_diarios', '10', 'Máximo número de reportes por día por usuario', 'integer'),
('enable_geolocation', 'true', 'Habilitar geolocalización en reportes', 'boolean'),
('email_notificaciones', 'true', 'Enviar notificaciones por email', 'boolean');

-- Tabla de logs del sistema
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nivel` enum('info','warning','error','debug') NOT NULL DEFAULT 'info',
  `mensaje` text NOT NULL,
  `contexto` json NULL,
  `usuario_id` int(11) NULL,
  `ip_address` varchar(45) NULL,
  `user_agent` text NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nivel` (`nivel`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear usuario administrador por defecto
INSERT INTO `usuarios` (`nombre`, `apellido`, `email`, `password`, `rol`, `created_at`) VALUES
('Administrador', 'Sistema', 'admin@ecocusco.pe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', NOW());

-- Inicializar puntos para el usuario administrador
INSERT INTO `puntos_usuarios` (`usuario_id`, `puntos_totales`, `puntos_disponibles`, `nivel`) VALUES
(1, 0, 0, 'Bronce');

-- Crear triggers para automatizar el cálculo de puntos

DELIMITER $$

-- Trigger para actualizar puntos cuando se procesa un reporte
CREATE TRIGGER `tr_actualizar_puntos_reporte` 
AFTER UPDATE ON `reportes`
FOR EACH ROW
BEGIN
    -- Solo cuando el estado cambia de 'pendiente' a 'procesado'
    IF OLD.estado = 'pendiente' AND NEW.estado = 'procesado' AND NEW.puntos_obtenidos > 0 THEN
        -- Actualizar puntos del usuario
        INSERT INTO `puntos_usuarios` (`usuario_id`, `puntos_totales`, `puntos_disponibles`)
        VALUES (NEW.usuario_id, NEW.puntos_obtenidos, NEW.puntos_obtenidos)
        ON DUPLICATE KEY UPDATE
            `puntos_totales` = `puntos_totales` + NEW.puntos_obtenidos,
            `puntos_disponibles` = `puntos_disponibles` + NEW.puntos_obtenidos;
        
        -- Registrar en historial
        INSERT INTO `historial_puntos` (`usuario_id`, `reporte_id`, `tipo`, `puntos`, `descripcion`)
        VALUES (NEW.usuario_id, NEW.id, 'ganancia', NEW.puntos_obtenidos, 
                CONCAT('Puntos por reporte de ', (SELECT nombre FROM tipos_residuos WHERE id = NEW.tipo_residuo_id)));
        
        -- Actualizar nivel del usuario
        CALL sp_actualizar_nivel_usuario(NEW.usuario_id);
    END IF;
END$$

-- Procedimiento para actualizar nivel del usuario
CREATE PROCEDURE `sp_actualizar_nivel_usuario`(IN p_usuario_id INT)
BEGIN
    DECLARE v_puntos_totales INT DEFAULT 0;
    DECLARE v_nuevo_nivel VARCHAR(50) DEFAULT 'Bronce';
    
    -- Obtener puntos totales del usuario
    SELECT puntos_totales INTO v_puntos_totales 
    FROM puntos_usuarios 
    WHERE usuario_id = p_usuario_id;
    
    -- Determinar nivel según puntos
    IF v_puntos_totales >= 5000 THEN
        SET v_nuevo_nivel = 'Oro';
    ELSEIF v_puntos_totales >= 2000 THEN
        SET v_nuevo_nivel = 'Plata';
    ELSE
        SET v_nuevo_nivel = 'Bronce';
    END IF;
    
    -- Actualizar nivel
    UPDATE puntos_usuarios 
    SET nivel = v_nuevo_nivel 
    WHERE usuario_id = p_usuario_id;
END$$

DELIMITER ;

-- Crear índices adicionales para optimización
CREATE INDEX `idx_reportes_usuario_fecha` ON `reportes` (`usuario_id`, `created_at`);
CREATE INDEX `idx_reportes_estado_fecha` ON `reportes` (`estado`, `created_at`);
CREATE INDEX `idx_historial_usuario_fecha` ON `historial_puntos` (`usuario_id`, `created_at`);

-- Restaurar configuración
SET FOREIGN_KEY_CHECKS = 1;

-- Información sobre la base de datos
SELECT 'Base de datos EcoCusco creada exitosamente' as resultado;
SELECT COUNT(*) as total_tablas FROM information_schema.tables WHERE table_schema = 'reciclaje_platform';
