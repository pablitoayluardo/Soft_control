<?php
// =====================================================
// SCRIPT DE CONFIGURACIÓN DE BASE DE DATOS
// Sistema de Control - globocit_soft_control
// =====================================================

// Configuración de la base de datos
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'globocit_soft_control',
    'username' => 'globocit_globocit',
    'password' => 'Correo2026+@',
    'charset' => 'utf8mb4'
];

try {
    // Conectar a la base de datos
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "✅ Conexión exitosa a la base de datos\n";
    
    // Crear tablas
    createTables($pdo);
    
    // Insertar datos de ejemplo
    insertSampleData($pdo);
    
    // Crear procedimientos almacenados
    createStoredProcedures($pdo);
    
    // Crear vistas
    createViews($pdo);
    
    // Crear triggers
    createTriggers($pdo);
    
    echo "\n🎉 Configuración de base de datos completada exitosamente!\n";
    echo "📊 Tablas creadas:\n";
    echo "   - usuarios\n";
    echo "   - sesiones\n";
    echo "   - logs_actividad\n";
    echo "   - configuraciones\n";
    echo "\n🔐 Credenciales de prueba:\n";
    echo "   Usuario: admin\n";
    echo "   Contraseña: 123456\n";
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}

function createTables($pdo) {
    echo "\n📋 Creando tablas...\n";
    
    // Tabla usuarios
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Tabla 'usuarios' creada\n";
    
    // Tabla sesiones
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sesiones (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Tabla 'sesiones' creada\n";
    
    // Tabla logs_actividad
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS logs_actividad (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Tabla 'logs_actividad' creada\n";
    
    // Tabla configuraciones
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS configuraciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            clave VARCHAR(100) NOT NULL UNIQUE,
            valor TEXT,
            descripcion TEXT,
            tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_clave (clave)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Tabla 'configuraciones' creada\n";
}

function insertSampleData($pdo) {
    echo "\n📝 Insertando datos de ejemplo...\n";
    
    // Verificar si ya existen usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        // Insertar usuarios de ejemplo
        $usuarios = [
            [
                'nombre_usuario' => 'admin',
                'email' => 'admin@globocity.com.ec',
                'contraseña_hash' => password_hash('123456', PASSWORD_BCRYPT),
                'nombre_completo' => 'Administrador del Sistema',
                'rol' => 'admin'
            ],
            [
                'nombre_usuario' => 'usuario1',
                'email' => 'usuario1@globocity.com.ec',
                'contraseña_hash' => password_hash('123456', PASSWORD_BCRYPT),
                'nombre_completo' => 'Usuario Ejemplo 1',
                'rol' => 'usuario'
            ],
            [
                'nombre_usuario' => 'moderador1',
                'email' => 'moderador1@globocity.com.ec',
                'contraseña_hash' => password_hash('123456', PASSWORD_BCRYPT),
                'nombre_completo' => 'Moderador Ejemplo',
                'rol' => 'moderador'
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre_usuario, email, contraseña_hash, nombre_completo, rol) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($usuarios as $usuario) {
            $stmt->execute([
                $usuario['nombre_usuario'],
                $usuario['email'],
                $usuario['contraseña_hash'],
                $usuario['nombre_completo'],
                $usuario['rol']
            ]);
        }
        echo "   ✅ Usuarios de ejemplo insertados\n";
    } else {
        echo "   ℹ️  Los usuarios ya existen, saltando inserción\n";
    }
    
    // Insertar configuraciones por defecto
    $configuraciones = [
        ['app_name', 'Sistema de Control', 'Nombre de la aplicación', 'string'],
        ['app_version', '1.0.0', 'Versión de la aplicación', 'string'],
        ['max_login_attempts', '5', 'Máximo número de intentos de login', 'number'],
        ['session_timeout', '3600', 'Tiempo de expiración de sesión en segundos', 'number'],
        ['maintenance_mode', 'false', 'Modo mantenimiento', 'boolean'],
        ['email_settings', '{"smtp_host":"smtp.gmail.com","smtp_port":587}', 'Configuración de email', 'json']
    ];
    
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO configuraciones (clave, valor, descripcion, tipo) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($configuraciones as $config) {
        $stmt->execute($config);
    }
    echo "   ✅ Configuraciones por defecto insertadas\n";
}

function createStoredProcedures($pdo) {
    echo "\n🔧 Creando procedimientos almacenados...\n";
    
    // Procedimiento para registrar login
    $pdo->exec("
        DROP PROCEDURE IF EXISTS RegistrarLogin
    ");
    
    $pdo->exec("
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
        END
    ");
    echo "   ✅ Procedimiento 'RegistrarLogin' creado\n";
    
    // Procedimiento para verificar credenciales
    $pdo->exec("
        DROP PROCEDURE IF EXISTS VerificarCredenciales
    ");
    
    $pdo->exec("
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
        END
    ");
    echo "   ✅ Procedimiento 'VerificarCredenciales' creado\n";
}

function createViews($pdo) {
    echo "\n👁️  Creando vistas...\n";
    
    // Vista de usuarios activos
    $pdo->exec("
        CREATE OR REPLACE VIEW v_usuarios_activos AS
        SELECT 
            id, nombre_usuario, email, nombre_completo, 
            fecha_registro, ultimo_login, rol
        FROM usuarios 
        WHERE activo = TRUE
    ");
    echo "   ✅ Vista 'v_usuarios_activos' creada\n";
    
    // Vista de actividad reciente
    $pdo->exec("
        CREATE OR REPLACE VIEW v_actividad_reciente AS
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
        LIMIT 100
    ");
    echo "   ✅ Vista 'v_actividad_reciente' creada\n";
}

function createTriggers($pdo) {
    echo "\n⚡ Creando triggers...\n";
    
    // Trigger para limpiar sesiones expiradas
    $pdo->exec("
        DROP TRIGGER IF EXISTS limpiar_sesiones_expiradas
    ");
    
    $pdo->exec("
        CREATE TRIGGER limpiar_sesiones_expiradas
        BEFORE INSERT ON sesiones
        FOR EACH ROW
        BEGIN
            DELETE FROM sesiones 
            WHERE fecha_expiracion < CURRENT_TIMESTAMP OR activa = FALSE;
        END
    ");
    echo "   ✅ Trigger 'limpiar_sesiones_expiradas' creado\n";
    
    // Trigger para registrar cambios en usuarios
    $pdo->exec("
        DROP TRIGGER IF EXISTS registrar_cambio_usuario
    ");
    
    $pdo->exec("
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
        END
    ");
    echo "   ✅ Trigger 'registrar_cambio_usuario' creado\n";
}

echo "\n🚀 Iniciando configuración de base de datos...\n";
echo "📊 Base de datos: globocit_soft_control\n";
echo "👤 Usuario: globocit_globocit\n";
echo "🌐 Host: localhost\n\n";
?> 