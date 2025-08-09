-- =====================================================
-- SCRIPT DE BASE DE DATOS - SISTEMA DE CONTROL
-- Base de datos: globocit_soft_control
-- Usuario: globocit_globocit
-- =====================================================

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS globocit_soft_control
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE globocit_soft_control;

-- =====================================================
-- TABLA DE USUARIOS
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    contraseña_hash VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    activo BOOLEAN DEFAULT TRUE,
    rol ENUM('admin', 'usuario', 'moderador') DEFAULT 'usuario',
    token_recuperacion VARCHAR(255) NULL,
    token_expiracion TIMESTAMP NULL,
    INDEX idx_nombre_usuario (nombre_usuario),
    INDEX idx_email (email),
    INDEX idx_activo (activo)
);

-- =====================================================
-- TABLA DE SESIONES
-- =====================================================
CREATE TABLE sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_fecha_expiracion (fecha_expiracion)
);

-- =====================================================
-- TABLA DE LOGS DE ACTIVIDAD
-- =====================================================
CREATE TABLE logs_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (fecha)
);

-- =====================================================
-- TABLA DE CONFIGURACIONES
-- =====================================================
CREATE TABLE configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descripcion TEXT,
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clave (clave)
);

-- =====================================================
-- DATOS DE EJEMPLO
-- =====================================================

-- Insertar usuario administrador de ejemplo
-- NOTA: La contraseña debe ser hasheada en la aplicación
INSERT INTO usuarios (nombre_usuario, email, contraseña_hash, nombre_completo, rol) VALUES
('admin', 'admin@globocity.com.ec', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'admin'),
('usuario1', 'usuario1@globocity.com.ec', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Usuario Ejemplo 1', 'usuario'),
('moderador1', 'moderador1@globocity.com.ec', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Moderador Ejemplo', 'moderador');

-- Insertar configuraciones por defecto
INSERT INTO configuraciones (clave, valor, descripcion, tipo) VALUES
('app_name', 'Sistema de Control', 'Nombre de la aplicación', 'string'),
('app_version', '1.0.0', 'Versión de la aplicación', 'string'),
('max_login_attempts', '5', 'Máximo número de intentos de login', 'number'),
('session_timeout', '3600', 'Tiempo de expiración de sesión en segundos', 'number'),
('maintenance_mode', 'false', 'Modo mantenimiento', 'boolean'),
('email_settings', '{"smtp_host":"smtp.gmail.com","smtp_port":587}', 'Configuración de email', 'json');

-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS
-- =====================================================

-- Procedimiento para registrar login
DELIMITER //
CREATE PROCEDURE RegistrarLogin(
    IN p_usuario_id INT,
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT
)
BEGIN
    -- Actualizar último login
    UPDATE usuarios 
    SET ultimo_login = CURRENT_TIMESTAMP 
    WHERE id = p_usuario_id;
    
    -- Registrar en logs
    INSERT INTO logs_actividad (usuario_id, accion, descripcion, ip_address, user_agent)
    VALUES (p_usuario_id, 'LOGIN', 'Inicio de sesión exitoso', p_ip_address, p_user_agent);
    
    -- Limpiar sesiones expiradas
    DELETE FROM sesiones 
    WHERE fecha_expiracion < CURRENT_TIMESTAMP OR activa = FALSE;
END //
DELIMITER ;

-- Procedimiento para verificar credenciales
DELIMITER //
CREATE PROCEDURE VerificarCredenciales(
    IN p_nombre_usuario VARCHAR(50),
    IN p_email VARCHAR(100),
    OUT p_usuario_id INT,
    OUT p_contraseña_hash VARCHAR(255),
    OUT p_activo BOOLEAN,
    OUT p_rol VARCHAR(20)
)
BEGIN
    SELECT 
        id, contraseña_hash, activo, rol
    INTO 
        p_usuario_id, p_contraseña_hash, p_activo, p_rol
    FROM usuarios 
    WHERE (nombre_usuario = p_nombre_usuario OR email = p_email)
    AND activo = TRUE
    LIMIT 1;
END //
DELIMITER ;

-- Procedimiento para crear sesión
DELIMITER //
CREATE PROCEDURE CrearSesion(
    IN p_usuario_id INT,
    IN p_token VARCHAR(255),
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT,
    IN p_duracion_horas INT
)
BEGIN
    INSERT INTO sesiones (usuario_id, token, ip_address, user_agent, fecha_expiracion)
    VALUES (p_usuario_id, p_token, p_ip_address, p_user_agent, 
            DATE_ADD(CURRENT_TIMESTAMP, INTERVAL p_duracion_horas HOUR));
END //
DELIMITER ;

-- =====================================================
-- VISTAS ÚTILES
-- =====================================================

-- Vista de usuarios activos
CREATE VIEW v_usuarios_activos AS
SELECT 
    id, nombre_usuario, email, nombre_completo, 
    fecha_registro, ultimo_login, rol
FROM usuarios 
WHERE activo = TRUE;

-- Vista de actividad reciente
CREATE VIEW v_actividad_reciente AS
SELECT 
    la.id,
    u.nombre_usuario,
    la.accion,
    la.descripcion,
    la.ip_address,
    la.fecha
FROM logs_actividad la
LEFT JOIN usuarios u ON la.usuario_id = u.id
ORDER BY la.fecha DESC
LIMIT 100;

-- Vista de sesiones activas
CREATE VIEW v_sesiones_activas AS
SELECT 
    s.id,
    u.nombre_usuario,
    s.ip_address,
    s.fecha_creacion,
    s.fecha_expiracion
FROM sesiones s
JOIN usuarios u ON s.usuario_id = u.id
WHERE s.activa = TRUE AND s.fecha_expiracion > CURRENT_TIMESTAMP;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Trigger para limpiar sesiones expiradas automáticamente
DELIMITER //
CREATE TRIGGER limpiar_sesiones_expiradas
BEFORE INSERT ON sesiones
FOR EACH ROW
BEGIN
    DELETE FROM sesiones 
    WHERE fecha_expiracion < CURRENT_TIMESTAMP OR activa = FALSE;
END //
DELIMITER ;

-- Trigger para registrar cambios en usuarios
DELIMITER //
CREATE TRIGGER registrar_cambio_usuario
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF OLD.activo != NEW.activo THEN
        INSERT INTO logs_actividad (usuario_id, accion, descripcion)
        VALUES (NEW.id, 'CAMBIO_ESTADO', 
                CONCAT('Usuario ', IF(NEW.activo = TRUE, 'activado', 'desactivado')));
    END IF;
    
    IF OLD.rol != NEW.rol THEN
        INSERT INTO logs_actividad (usuario_id, accion, descripcion)
        VALUES (NEW.id, 'CAMBIO_ROL', 
                CONCAT('Rol cambiado de ', OLD.rol, ' a ', NEW.rol));
    END IF;
END //
DELIMITER ;

-- =====================================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =====================================================

-- Índices para búsquedas frecuentes
CREATE INDEX idx_usuarios_rol_activo ON usuarios(rol, activo);
CREATE INDEX idx_sesiones_activa_expiracion ON sesiones(activa, fecha_expiracion);
CREATE INDEX idx_logs_fecha_accion ON logs_actividad(fecha, accion);

-- =====================================================
-- NOTAS IMPORTANTES DE SEGURIDAD
-- =====================================================

/*
IMPORTANTE: Almacenamiento seguro de contraseñas

1. NUNCA almacenes contraseñas en texto plano
2. Usa funciones de hash seguras como bcrypt, Argon2, o PBKDF2
3. En PHP, usa password_hash() y password_verify()
4. En Node.js, usa bcrypt o argon2
5. En Python, usa bcrypt o passlib

Ejemplo en PHP:
$hash = password_hash($password, PASSWORD_BCRYPT);
$isValid = password_verify($password, $hash);

Ejemplo en Node.js:
const bcrypt = require('bcrypt');
const hash = await bcrypt.hash(password, 12);
const isValid = await bcrypt.compare(password, hash);

Ejemplo en Python:
import bcrypt
hash = bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt())
is_valid = bcrypt.checkpw(password.encode('utf-8'), hash)
*/

-- =====================================================
-- CONSULTAS DE PRUEBA
-- =====================================================

-- Verificar que las tablas se crearon correctamente
SELECT 'Tablas creadas:' as info;
SHOW TABLES;

-- Verificar usuarios de ejemplo
SELECT 'Usuarios de ejemplo:' as info;
SELECT id, nombre_usuario, email, rol, activo FROM usuarios;

-- Verificar configuraciones
SELECT 'Configuraciones:' as info;
SELECT clave, valor, tipo FROM configuraciones;

-- Verificar vistas
SELECT 'Vistas creadas:' as info;
SHOW FULL TABLES WHERE Table_type = 'VIEW'; 