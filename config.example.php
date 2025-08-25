<?php
// =====================================================
// ARCHIVO DE CONFIGURACIÓN DE EJEMPLO
// =====================================================
// 
// INSTRUCCIONES:
// 1. Copiar este archivo como 'config.php'
// 2. Modificar las credenciales según tu entorno
// 3. Nunca subir config.php al repositorio
// 
// =====================================================

// Configuración de Base de Datos
define('DB_HOST', 'localhost');           // Host de la base de datos
define('DB_NAME', 'tu_base_datos');       // Nombre de la base de datos
define('DB_USER', 'tu_usuario');          // Usuario de la base de datos
define('DB_PASS', 'tu_password');         // Contraseña de la base de datos
define('DB_CHARSET', 'utf8mb4');          // Charset de la base de datos

// Configuración de la Aplicación
define('APP_NAME', 'Sistema de Pagos - GloboCity');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'America/Guayaquil');  // Zona horaria

// Configuración de Seguridad
define('JWT_SECRET', 'cambiar_esta_clave_secreta_en_produccion');
define('SESSION_EXPIRATION', 3600);       // Tiempo de sesión en segundos

// Configuración de URLs
define('BASE_URL', 'http://tu-dominio.com');  // URL base de la aplicación
define('API_URL', BASE_URL . '/api');         // URL base de las APIs

// Configuración de Logs
define('LOG_ENABLED', true);              // Habilitar logs
define('LOG_LEVEL', 'INFO');              // Nivel de log (DEBUG, INFO, WARNING, ERROR)

// Configuración de Paginación
define('DEFAULT_PAGE_SIZE', 20);          // Tamaño de página por defecto
define('MAX_PAGE_SIZE', 100);             // Tamaño máximo de página

// Configuración de Validaciones
define('MAX_FILE_SIZE', 5242880);         // Tamaño máximo de archivo (5MB)
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

// Configuración de Moneda
define('CURRENCY', 'USD');                // Moneda del sistema
define('CURRENCY_SYMBOL', '$');           // Símbolo de la moneda

// Configuración de Facturación
define('DEFAULT_IVA', 12);                // Porcentaje de IVA por defecto
define('INVOICE_PREFIX', 'FAC');          // Prefijo para números de factura

// Configuración de Email (opcional)
define('SMTP_HOST', 'smtp.tu-servidor.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'tu-email@dominio.com');
define('SMTP_PASS', 'tu-password-email');
define('SMTP_SECURE', 'tls');

// Configuración de Desarrollo
define('DEBUG_MODE', false);              // Modo debug (true para desarrollo)
define('SHOW_ERRORS', false);             // Mostrar errores (true para desarrollo)

// Configuración de Backup
define('BACKUP_ENABLED', true);           // Habilitar backups automáticos
define('BACKUP_RETENTION_DAYS', 30);      // Días de retención de backups

// =====================================================
// NO MODIFICAR DESDE AQUÍ HACIA ABAJO
// =====================================================

// Configurar zona horaria
if (defined('TIMEZONE')) {
    date_default_timezone_set(TIMEZONE);
}

// Configurar manejo de errores
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configurar límites de memoria y tiempo
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

// Configurar headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Configurar headers CORS para APIs
if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

// Función de utilidad para logging
function log_message($level, $message, $context = []) {
    if (!defined('LOG_ENABLED') || !LOG_ENABLED) {
        return;
    }
    
    $log_file = 'logs/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? ' ' . json_encode($context) : '';
    
    $log_entry = "[$timestamp] [$level] $message$context_str" . PHP_EOL;
    
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Función de utilidad para validar configuración
function validate_config() {
    $required_constants = [
        'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'
    ];
    
    $missing = [];
    foreach ($required_constants as $constant) {
        if (!defined($constant)) {
            $missing[] = $constant;
        }
    }
    
    if (!empty($missing)) {
        die('Error de configuración: Faltan las siguientes constantes: ' . implode(', ', $missing));
    }
}

// Validar configuración al cargar
validate_config();
?> 