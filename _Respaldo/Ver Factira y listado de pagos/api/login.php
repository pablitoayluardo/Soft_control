<?php
// =====================================================
// API DE LOGIN - SISTEMA DE CONTROL
// Endpoint: /api/login.php
// Método: POST
// =====================================================

// Incluir configuración
require_once '../config.php';

// Configurar headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit();
}

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

// Si no hay datos JSON, intentar con $_POST
if (!$input) {
    $input = $_POST;
}

// Validar datos de entrada
if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Usuario y contraseña son requeridos'
    ]);
    exit();
}

$username = sanitizeInput($input['username']);
$password = $input['password'];
$rememberMe = isset($input['rememberMe']) ? (bool)$input['rememberMe'] : false;

// Validaciones básicas
if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario y contraseña no pueden estar vacíos'
    ]);
    exit();
}

// Verificar límite de intentos de login
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$attemptKey = "login_attempts_$ipAddress";

if (isset($_SESSION[$attemptKey])) {
    $attempts = $_SESSION[$attemptKey];
    if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS && 
        (time() - $attempts['time']) < LOCKOUT_TIME) {
        
        $remainingTime = LOCKOUT_TIME - (time() - $attempts['time']);
        echo json_encode([
            'success' => false,
            'message' => "Demasiados intentos fallidos. Intente nuevamente en " . ceil($remainingTime / 60) . " minutos"
        ]);
        exit();
    }
}

// Verificar credenciales
$result = verifyLoginCredentials($username, $password);

if ($result['success']) {
    // Login exitoso
    $user = $result['user'];
    
    // Crear sesión
    $token = createUserSession($user);
    
    // Configurar "Recordarme" si está habilitado
    if ($rememberMe) {
        $rememberToken = bin2hex(random_bytes(32));
        setcookie('remember_token', $rememberToken, time() + (30 * 24 * 60 * 60), '/', '', true, true);
        
        // Guardar token de recordar en base de datos
        $pdo = getDBConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE usuarios SET token_recuperacion = ?, token_expiracion = ? WHERE id = ?");
                $stmt->execute([
                    $rememberToken,
                    date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)),
                    $user['id']
                ]);
            } catch (PDOException $e) {
                logActivity('REMEMBER_ERROR', 'Error al guardar token de recordar: ' . $e->getMessage());
            }
        }
    }
    
    // Limpiar intentos fallidos
    unset($_SESSION[$attemptKey]);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Inicio de sesión exitoso',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'fullName' => $user['fullName'],
            'email' => $user['email'],
            'role' => $user['role']
        ],
        'token' => $token,
        'redirect' => BASE_URL . '/dashboard.html'
    ]);
    
} else {
    // Login fallido
    $attempts = isset($_SESSION[$attemptKey]) ? $_SESSION[$attemptKey] : ['count' => 0, 'time' => time()];
    $attempts['count']++;
    $attempts['time'] = time();
    $_SESSION[$attemptKey] = $attempts;
    
    $remainingAttempts = MAX_LOGIN_ATTEMPTS - $attempts['count'];
    
    if ($remainingAttempts > 0) {
        $message = $result['message'] . ". Intentos restantes: $remainingAttempts";
    } else {
        $message = $result['message'] . ". Cuenta bloqueada temporalmente.";
    }
    
    echo json_encode([
        'success' => false,
        'message' => $message,
        'remainingAttempts' => max(0, $remainingAttempts)
    ]);
}
?> 