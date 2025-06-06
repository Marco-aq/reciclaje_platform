-- Base de Datos para Plataforma de Reciclaje MVC
-- Creado para el proyecto reciclaje_platform_mvc_working

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS reciclaje_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reciclaje_db;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de reportes de reciclaje
CREATE TABLE reportes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_material ENUM('plastico', 'papel', 'vidrio', 'metal', 'electronico', 'organico', 'textil', 'otros') NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    ubicacion VARCHAR(255) NOT NULL,
    descripcion TEXT,
    foto VARCHAR(255),
    fecha_reporte TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_tipo_material (tipo_material),
    INDEX idx_fecha_reporte (fecha_reporte),
    INDEX idx_ubicacion (ubicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para tokens de recuperación de contraseña
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de sesiones (opcional, para gestión avanzada de sesiones)
CREATE TABLE sessions (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de logros/achievements (opcional, para gamificación)
CREATE TABLE logros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_logro VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_obtenido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_tipo_logro (tipo_logro),
    UNIQUE KEY unique_user_achievement (usuario_id, tipo_logro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración del sistema
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuración inicial
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('app_version', '1.0.0', 'Versión actual de la aplicación'),
('maintenance_mode', 'false', 'Modo de mantenimiento activado'),
('registrations_enabled', 'true', 'Permitir nuevos registros'),
('max_upload_size', '5242880', 'Tamaño máximo de archivos en bytes (5MB)'),
('points_per_kg', '10', 'Puntos otorgados por kilogramo reciclado');

-- Crear usuario de ejemplo (contraseña: demo123)
-- Nota: Esta contraseña está hasheada con el salt por defecto
INSERT INTO usuarios (nombre, email, password) VALUES
('Usuario Demo', 'demo@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insertar algunos reportes de ejemplo
INSERT INTO reportes (usuario_id, tipo_material, cantidad, ubicacion, descripcion, fecha_reporte) VALUES
(1, 'plastico', 2.5, 'Centro de la Ciudad', 'Botellas de agua y refrescos', NOW() - INTERVAL 1 DAY),
(1, 'papel', 1.8, 'Oficina Central', 'Documentos y periódicos', NOW() - INTERVAL 2 DAY),
(1, 'vidrio', 3.2, 'Casa', 'Frascos y botellas de vidrio', NOW() - INTERVAL 3 DAY),
(1, 'metal', 0.8, 'Taller Mecánico', 'Latas de aluminio', NOW() - INTERVAL 4 DAY),
(1, 'electronico', 1.5, 'Centro de Reciclaje', 'Cables y componentes', NOW() - INTERVAL 5 DAY);

-- Vista para estadísticas rápidas
CREATE VIEW vista_estadisticas_generales AS
SELECT 
    COUNT(DISTINCT u.id) as total_usuarios,
    COUNT(r.id) as total_reportes,
    SUM(r.cantidad) as total_materiales_kg,
    COUNT(DISTINCT r.ubicacion) as ubicaciones_unicas,
    AVG(r.cantidad) as promedio_cantidad_reporte
FROM usuarios u
LEFT JOIN reportes r ON u.id = r.usuario_id;

-- Vista para ranking de usuarios
CREATE VIEW vista_ranking_usuarios AS
SELECT 
    u.id,
    u.nombre,
    u.email,
    COUNT(r.id) as total_reportes,
    COALESCE(SUM(r.cantidad), 0) as total_kg_reciclados,
    COALESCE(SUM(r.cantidad * 10), 0) as puntos_totales,
    MAX(r.fecha_reporte) as ultimo_reporte
FROM usuarios u
LEFT JOIN reportes r ON u.id = r.usuario_id
GROUP BY u.id, u.nombre, u.email
ORDER BY puntos_totales DESC, total_reportes DESC;

-- Vista para estadísticas por tipo de material
CREATE VIEW vista_materiales_estadisticas AS
SELECT 
    tipo_material,
    COUNT(*) as cantidad_reportes,
    SUM(cantidad) as total_kg,
    AVG(cantidad) as promedio_kg,
    COUNT(DISTINCT usuario_id) as usuarios_unicos
FROM reportes
GROUP BY tipo_material
ORDER BY total_kg DESC;

-- Función para calcular CO2 evitado (aproximado)
DELIMITER //
CREATE FUNCTION calcular_co2_evitado(tipo VARCHAR(20), cantidad DECIMAL(10,2))
RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE factor DECIMAL(4,2) DEFAULT 1.0;
    
    CASE tipo
        WHEN 'plastico' THEN SET factor = 2.0;
        WHEN 'papel' THEN SET factor = 3.3;
        WHEN 'vidrio' THEN SET factor = 0.5;
        WHEN 'metal' THEN SET factor = 6.0;
        WHEN 'electronico' THEN SET factor = 4.0;
        WHEN 'organico' THEN SET factor = 0.3;
        WHEN 'textil' THEN SET factor = 2.5;
        ELSE SET factor = 1.0;
    END CASE;
    
    RETURN cantidad * factor;
END //
DELIMITER ;

-- Procedimiento para limpiar datos antiguos
DELIMITER //
CREATE PROCEDURE limpiar_datos_antiguos()
BEGIN
    -- Limpiar tokens de recuperación expirados
    DELETE FROM password_resets WHERE expires_at < NOW();
    
    -- Limpiar sesiones antiguas (más de 30 días)
    DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));
    
    -- Log de la limpieza
    INSERT INTO configuracion (clave, valor, descripcion) 
    VALUES (CONCAT('limpieza_', DATE_FORMAT(NOW(), '%Y%m%d')), NOW(), 'Última limpieza de datos')
    ON DUPLICATE KEY UPDATE valor = NOW();
END //
DELIMITER ;

-- Trigger para actualizar updated_at automáticamente
DELIMITER //
CREATE TRIGGER tr_usuarios_updated_at
    BEFORE UPDATE ON usuarios
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //

CREATE TRIGGER tr_reportes_updated_at
    BEFORE UPDATE ON reportes
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //
DELIMITER ;

-- Índices adicionales para optimización
CREATE INDEX idx_reportes_fecha_usuario ON reportes(fecha_reporte, usuario_id);
CREATE INDEX idx_reportes_tipo_fecha ON reportes(tipo_material, fecha_reporte);
CREATE INDEX idx_usuarios_nombre ON usuarios(nombre);

-- Configurar el motor de almacenamiento y charset
ALTER TABLE usuarios ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE reportes ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE password_resets ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE configuracion ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentarios de documentación
ALTER TABLE usuarios COMMENT = 'Tabla de usuarios del sistema de reciclaje';
ALTER TABLE reportes COMMENT = 'Tabla de reportes de actividades de reciclaje';
ALTER TABLE password_resets COMMENT = 'Tabla para tokens de recuperación de contraseña';
ALTER TABLE configuracion COMMENT = 'Tabla de configuración del sistema';

-- Verificar la estructura creada
SHOW TABLES;

-- Mostrar estadísticas iniciales
SELECT * FROM vista_estadisticas_generales;
SELECT * FROM vista_ranking_usuarios;
SELECT * FROM vista_materiales_estadisticas;

-- Mensaje de finalización
SELECT 'Base de datos creada exitosamente' as status;
