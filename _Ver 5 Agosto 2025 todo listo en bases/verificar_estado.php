<?php
// Script para verificar el estado actual del sistema
header('Content-Type: application/json; charset=utf-8');

$status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'server_info' => [
        'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'
    ],
    'files_status' => [],
    'database_status' => 'unknown',
    'api_status' => 'unknown'
];

// Verificar archivos importantes
$important_files = [
    'config.php',
    'api/upload_factura_individual.php',
    'api/get_facturas_simple.php'
];

foreach ($important_files as $file) {
    $status['files_status'][$file] = [
        'exists' => file_exists($file),
        'readable' => is_readable($file),
        'size' => file_exists($file) ? filesize($file) : 0,
        'modified' => file_exists($file) ? date('Y-m-d H:i:s', filemtime($file)) : null
    ];
}

// Verificar conexión a la base de datos
try {
    require_once 'config.php';
    $pdo = getDBConnection();
    
    if ($pdo) {
        $status['database_status'] = 'connected';
        
        // Verificar tablas importantes
        $tables = ['info_tributaria', 'info_factura', 'detalle_factura_sri'];
        $status['database_tables'] = [];
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $result = $stmt->fetch();
                $status['database_tables'][$table] = [
                    'exists' => true,
                    'count' => $result['count']
                ];
            } catch (Exception $e) {
                $status['database_tables'][$table] = [
                    'exists' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
    } else {
        $status['database_status'] = 'connection_failed';
    }
} catch (Exception $e) {
    $status['database_status'] = 'error';
    $status['database_error'] = $e->getMessage();
}

// Verificar API sin ejecutarla
$api_file = 'api/upload_factura_individual.php';
if (file_exists($api_file)) {
    $content = file_get_contents($api_file);
    $status['api_status'] = [
        'file_exists' => true,
        'has_ob_start' => strpos($content, 'ob_start()') !== false,
        'has_returnJsonResponse' => strpos($content, 'returnJsonResponse') !== false,
        'has_echo_debug' => strpos($content, 'echo') !== false && strpos($content, '🔧') !== false,
        'content_length' => strlen($content),
        'first_line' => substr($content, 0, 100)
    ];
} else {
    $status['api_status'] = ['file_exists' => false];
}

echo json_encode($status, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?> 