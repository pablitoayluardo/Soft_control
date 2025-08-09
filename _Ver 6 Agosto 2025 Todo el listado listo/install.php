<?php
// =====================================================
// SCRIPT DE INSTALACIÓN - SISTEMA DE CONTROL
// Acceder desde el navegador: https://www.globocity.com.ec/soft_control/install.php
// =====================================================

// Configurar headers
header('Content-Type: text/html; charset=utf-8');

// Configuración de la base de datos
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'globocit_soft_control',
    'username' => 'globocit_globocit',
    'password' => 'Correo2026+@',
    'charset' => 'utf8mb4'
];

$output = [];
$success = true;

function addOutput($message, $type = 'info') {
    global $output;
    $output[] = ['message' => $message, 'type' => $type];
}

function addSuccess($message) {
    addOutput($message, 'success');
}

function addError($message) {
    global $success;
    $success = false;
    addOutput($message, 'error');
}

function addWarning($message) {
    addOutput($message, 'warning');
}

// Iniciar instalación
addOutput('🚀 Iniciando instalación del Sistema de Control...', 'info');
addOutput('📊 Base de datos: ' . $dbConfig['dbname'], 'info');
addOutput('👤 Usuario: ' . $dbConfig['username'], 'info');
addOutput('🌐 Host: ' . $dbConfig['host'], 'info');

try {
    // Conectar a la base de datos
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    addSuccess('✅ Conexión exitosa a la base de datos');
    
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
    
    addSuccess('🎉 Instalación completada exitosamente!');
    
} catch (PDOException $e) {
    addError('❌ Error de conexión: ' . $e->getMessage());
}

function createTables($pdo) {
    addOutput('📋 Creando tablas...', 'info');
    
    // Tabla usuarios
    try {
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
        addSuccess('   ✅ Tabla \'usuarios\' creada');
    } catch (Exception $e) {
        addError('   ❌ Error creando tabla \'usuarios\': ' . $e->getMessage());
    }
    
    // Tabla sesiones
    try {
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
        addSuccess('   ✅ Tabla \'sesiones\' creada');
    } catch (Exception $e) {
        addError('   ❌ Error creando tabla \'sesiones\': ' . $e->getMessage());
    }
    
    // Tabla logs_actividad
    try {
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
        addSuccess('   ✅ Tabla \'logs_actividad\' creada');
    } catch (Exception $e) {
        addError('   ❌ Error creando tabla \'logs_actividad\': ' . $e->getMessage());
    }
    
    // Tabla configuraciones
    try {
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
        addSuccess('   ✅ Tabla \'configuraciones\' creada');
    } catch (Exception $e) {
        addError('   ❌ Error creando tabla \'configuraciones\': ' . $e->getMessage());
    }
}

function insertSampleData($pdo) {
    addOutput('📝 Insertando datos de ejemplo...', 'info');
    
    try {
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
            addSuccess('   ✅ Usuarios de ejemplo insertados');
        } else {
            addWarning('   ℹ️  Los usuarios ya existen, saltando inserción');
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
        addSuccess('   ✅ Configuraciones por defecto insertadas');
        
    } catch (Exception $e) {
        addError('   ❌ Error insertando datos: ' . $e->getMessage());
    }
}

function createStoredProcedures($pdo) {
    addOutput('🔧 Creando procedimientos almacenados...', 'info');
    
    try {
        // Procedimiento para registrar login
        $pdo->exec("DROP PROCEDURE IF EXISTS RegistrarLogin");
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
        addSuccess('   ✅ Procedimiento \'RegistrarLogin\' creado');
        
        // Procedimiento para verificar credenciales
        $pdo->exec("DROP PROCEDURE IF EXISTS VerificarCredenciales");
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
        addSuccess('   ✅ Procedimiento \'VerificarCredenciales\' creado');
        
    } catch (Exception $e) {
        addError('   ❌ Error creando procedimientos: ' . $e->getMessage());
    }
}

function createViews($pdo) {
    addOutput('👁️  Creando vistas...', 'info');
    
    try {
        // Vista de usuarios activos
        $pdo->exec("
            CREATE OR REPLACE VIEW v_usuarios_activos AS
            SELECT 
                id, nombre_usuario, email, nombre_completo, 
                fecha_registro, ultimo_login, rol
            FROM usuarios 
            WHERE activo = TRUE
        ");
        addSuccess('   ✅ Vista \'v_usuarios_activos\' creada');
        
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
        addSuccess('   ✅ Vista \'v_actividad_reciente\' creada');
        
    } catch (Exception $e) {
        addError('   ❌ Error creando vistas: ' . $e->getMessage());
    }
}

function createTriggers($pdo) {
    addOutput('⚡ Creando triggers...', 'info');
    
    try {
        // Trigger para limpiar sesiones expiradas
        $pdo->exec("DROP TRIGGER IF EXISTS limpiar_sesiones_expiradas");
        $pdo->exec("
            CREATE TRIGGER limpiar_sesiones_expiradas
            BEFORE INSERT ON sesiones
            FOR EACH ROW
            BEGIN
                DELETE FROM sesiones 
                WHERE fecha_expiracion < CURRENT_TIMESTAMP OR activa = FALSE;
            END
        ");
        addSuccess('   ✅ Trigger \'limpiar_sesiones_expiradas\' creado');
        
        // Trigger para registrar cambios en usuarios
        $pdo->exec("DROP TRIGGER IF EXISTS registrar_cambio_usuario");
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
        addSuccess('   ✅ Trigger \'registrar_cambio_usuario\' creado');
        
    } catch (Exception $e) {
        addError('   ❌ Error creando triggers: ' . $e->getMessage());
    }
}

// Generar HTML de salida
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Sistema de Control</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .output {
            background: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
            margin: 20px 0;
        }
        .success { color: #48bb78; }
        .error { color: #f56565; }
        .warning { color: #ed8936; }
        .info { color: #4299e1; }
        .credentials {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Instalación del Sistema de Control</h1>
        
        <div class="output">
<?php
foreach ($output as $line) {
    $class = $line['type'];
    echo "<span class=\"$class\">{$line['message']}</span>\n";
}
?>
        </div>
        
        <?php if ($success): ?>
        <div class="credentials">
            <h4>🔐 Credenciales de Prueba</h4>
            <p><strong>Usuario:</strong> admin</p>
            <p><strong>Contraseña:</strong> 123456</p>
        </div>
        
        <div style="text-align: center;">
            <a href="index.html" class="btn">🚀 Ir al Sistema</a>
            <a href="dashboard.html" class="btn">📊 Ver Dashboard</a>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f0fff4; border: 1px solid #9ae6b4; border-radius: 8px;">
            <h4>✅ Instalación Completada</h4>
            <p>El sistema está listo para usar. Puedes acceder con las credenciales de prueba o crear nuevos usuarios desde el panel de administración.</p>
        </div>
        <?php else: ?>
        <div style="margin-top: 20px; padding: 15px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px;">
            <h4>❌ Error en la Instalación</h4>
            <p>Hubo errores durante la instalación. Revisa los mensajes de error arriba y verifica la configuración de la base de datos.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 