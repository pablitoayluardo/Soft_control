<?php
// =====================================================
// API DE LOGOUT - SISTEMA DE CONTROL
// Endpoint: /api/logout.php
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

// Verificar si el usuario está autenticado
if (!isAuthenticated()) {
    echo json_encode([
        'success' => false,
        'message' => 'No hay sesión activa'
    ]);
    exit();
}

// Obtener información del usuario antes de cerrar sesión
$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_name'] ?? 'Usuario';

// Cerrar sesión
$logoutResult = logoutUser();

// Limpiar cookies de "Recordarme"
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Registrar actividad de logout
if ($userId) {
    logActivity('LOGOUT', 'Cierre de sesión exitoso', $userId);
}

// Respuesta exitosa
echo json_encode([
    'success' => true,
    'message' => 'Sesión cerrada exitosamente',
    'redirect' => BASE_URL . '/index.html'
]);
?> 