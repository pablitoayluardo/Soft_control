<?php
/**
 * Archivo de configuración de ejemplo para el Sistema de Control
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'config.php'
 * 2. Modifica las credenciales según tu configuración
 * 3. Asegúrate de que config.php esté en .gitignore
 */

// =====================================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// =====================================================
define('DB_HOST', 'localhost');                    // Host de la base de datos
define('DB_NAME', 'globocit_soft_control');        // Nombre de la base de datos
define('DB_USER', 'globocit_globocit');           // Usuario de la base de datos
define('DB_PASS', 'Correo2026+@');                // Contraseña de la base de datos
define('DB_CHARSET', 'utf8mb4');                   // Charset de la base de datos

// =====================================================
// CONFIGURACIÓN DE LA APLICACIÓN
// =====================================================
define('BASE_URL', 'https://www.globocity.com.ec/soft_control');  // URL base de la aplicación
define('APP_NAME', 'Sistema de Control');          // Nombre de la aplicación
define('APP_VERSION', '1.0.0');                    // Versión de la aplicación
define('DEBUG_MODE', false);                       // Modo debug (true/false)
define('TIMEZONE', 'America/Guayaquil');           // Zona horaria

// =====================================================
// CONFIGURACIÓN DE SEGURIDAD
// =====================================================
define('JWT_SECRET', 'tu_jwt_secret_super_seguro_aqui');  // Clave secreta para JWT
define('SESSION_EXPIRATION', 3600);                // Tiempo de expiración de sesión (segundos)
define('MAX_LOGIN_ATTEMPTS', 5);                   // Máximo intentos de login
define('LOCKOUT_TIME', 900);                       // Tiempo de bloqueo (segundos)
define('PASSWORD_MIN_LENGTH', 8);                  // Longitud mínima de contraseña
define('PASSWORD_REQUIRE_SPECIAL', true);          // Requerir caracteres especiales

// =====================================================
// CONFIGURACIÓN DE EMAIL (OPCIONAL)
// =====================================================
define('SMTP_HOST', 'smtp.gmail.com');             // Servidor SMTP
define('SMTP_PORT', 587);                          // Puerto SMTP
define('SMTP_USER', 'tu_email@gmail.com');         // Usuario SMTP
define('SMTP_PASS', 'tu_password_app');            // Contraseña SMTP
define('SMTP_SECURE', 'tls');                      // Tipo de seguridad (tls/ssl)

// =====================================================
// CONFIGURACIÓN DE ARCHIVOS
// =====================================================
define('UPLOAD_PATH', 'uploads/');                 // Ruta de subida de archivos
define('MAX_FILE_SIZE', 5242880);                  // Tamaño máximo de archivo (5MB)
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// =====================================================
// CONFIGURACIÓN DE LOGS
// =====================================================
define('LOG_PATH', 'logs/');                       // Ruta de logs
define('LOG_LEVEL', 'INFO');                       // Nivel de log (DEBUG, INFO, WARNING, ERROR)
define('LOG_MAX_SIZE', 10485760);                  // Tamaño máximo de log (10MB)

// =====================================================
// FUNCIONES DE UTILIDAD
// =====================================================

/**
 * Conexión a la base de datos
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        return false;
    }
}

/**
 * Función de logging
 */
function logActivity($message, $level = 'INFO', $user_id = null) {
    $logFile = LOG_PATH . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] [$user_id] $message" . PHP_EOL;
    
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Sanitizar entrada de usuario
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generar token JWT
 */
function generateJWT($payload) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($payload);
    
    $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, JWT_SECRET, true);
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64Header . "." . $base64Payload . "." . $base64Signature;
}

/**
 * Verificar token JWT
 */
function verifyJWT($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    
    $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], JWT_SECRET, true);
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return hash_equals($base64Signature, $parts[2]);
}

/**
 * Verificar autenticación
 */
function isAuthenticated() {
    session_start();
    return isset($_SESSION['user_id']) && isset($_SESSION['token']);
}

/**
 * Verificar permisos de usuario
 */
function hasPermission($permission) {
    if (!isAuthenticated()) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'] ?? 'usuario';
    $permissions = [
        'admin' => ['all'],
        'moderador' => ['read', 'write', 'delete'],
        'usuario' => ['read', 'write']
    ];
    
    return in_array($permission, $permissions[$userRole]) || in_array('all', $permissions[$userRole]);
}

/**
 * Configurar headers de seguridad
 */
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');
}

/**
 * Configurar sesión segura
 */
function configureSecureSession() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);

// Configurar headers de seguridad
setSecurityHeaders();

// Configurar sesión segura
configureSecureSession();

// =====================================================
// CONFIGURACIÓN DE ZONA HORARIA
// =====================================================

// Lista de zonas horarias comunes
$timezones = [
    'America/Mexico_City' => 'Ciudad de México',
    'America/New_York' => 'Nueva York',
    'America/Los_Angeles' => 'Los Ángeles',
    'Europe/Madrid' => 'Madrid',
    'Europe/London' => 'Londres',
    'Asia/Tokyo' => 'Tokio',
    'Australia/Sydney' => 'Sídney'
];

// =====================================================
// CONFIGURACIÓN DE IDIOMAS
// =====================================================

// Idiomas soportados
$supportedLanguages = [
    'es' => 'Español',
    'en' => 'English',
    'fr' => 'Français',
    'de' => 'Deutsch'
];

// Idioma por defecto
define('DEFAULT_LANGUAGE', 'es');

// =====================================================
// CONFIGURACIÓN DE PAGINACIÓN
// =====================================================

// Elementos por página
define('ITEMS_PER_PAGE', 20);

// =====================================================
// CONFIGURACIÓN DE CACHE
// =====================================================

// Tiempo de cache en segundos
define('CACHE_TIME', 3600); // 1 hora

// Directorio de cache
define('CACHE_DIR', __DIR__ . '/cache/');

// =====================================================
// MENSAJES DE ERROR
// =====================================================

$errorMessages = [
    'db_connection' => 'Error de conexión a la base de datos',
    'invalid_credentials' => 'Usuario o contraseña incorrectos',
    'account_locked' => 'Cuenta bloqueada temporalmente',
    'session_expired' => 'Sesión expirada',
    'permission_denied' => 'Permisos insuficientes',
    'file_upload_error' => 'Error al subir archivo',
    'invalid_file_type' => 'Tipo de archivo no permitido',
    'file_too_large' => 'Archivo demasiado grande'
];

// =====================================================
// CONFIGURACIÓN DE API
// =====================================================

// Rate limiting
define('API_RATE_LIMIT', 100); // requests per hour
define('API_RATE_WINDOW', 3600); // 1 hour in seconds

// =====================================================
// CONFIGURACIÓN DE BACKUP
// =====================================================

// Directorio de backups
define('BACKUP_DIR', __DIR__ . '/backups/');

// Frecuencia de backup (en días)
define('BACKUP_FREQUENCY', 7);

// Retener backups por (en días)
define('BACKUP_RETENTION', 30);

// =====================================================
// FINALIZACIÓN
// =====================================================

// Marcar que la configuración se cargó correctamente
define('CONFIG_LOADED', true);

?> 