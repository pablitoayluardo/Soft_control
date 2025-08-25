<?php
// =====================================================
// API PARA OBTENER HISTORIAL DE PAGOS DE UNA FACTURA
// =====================================================

// Configurar headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir configuración
require_once '../config.php';

try {
    // Verificar que las constantes estén definidas
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Configuración de base de datos incompleta');
    }
    
    // Usar las constantes definidas en config.php
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Método no permitido');
    }
    
    // Obtener parámetros
    $claveAcceso = isset($_GET['clave_acceso']) ? trim($_GET['clave_acceso']) : '';
    $facturaId = isset($_GET['factura_id']) ? (int)$_GET['factura_id'] : 0;
    
    if (empty($claveAcceso) && $facturaId <= 0) {
        throw new Exception('Se requiere clave_acceso o factura_id');
    }
    
    // Construir la consulta
    $sql = "
        SELECT 
            p.id,
            p.factura_id,
            p.clave_acceso,
            p.monto,
            p.metodo_pago,
            p.institucion,
            p.documento,
            p.referencia,
            p.observacion,
            p.fecha_pago,
            p.estado,
            f.estab,
            f.pto_emi,
            f.secuencial,
            f.razon_social_comprador,
            f.importe_total,
            f.saldo,
            f.valor_pagado
        FROM pagos p
        INNER JOIN facturas f ON p.factura_id = f.id
        WHERE p.estado = 'confirmado'
    ";
    
    $params = [];
    
    if (!empty($claveAcceso)) {
        $sql .= " AND p.clave_acceso = ?";
        $params[] = $claveAcceso;
    } else {
        $sql .= " AND p.factura_id = ?";
        $params[] = $facturaId;
    }
    
    $sql .= " ORDER BY p.fecha_pago DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pagos = $stmt->fetchAll();
    
    // Calcular totales
    $totalPagado = 0;
    foreach ($pagos as $pago) {
        $totalPagado += floatval($pago['monto']);
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => [
            'pagos' => $pagos,
            'resumen' => [
                'total_pagos' => count($pagos),
                'total_pagado' => $totalPagado,
                'factura_info' => count($pagos) > 0 ? [
                    'estab' => $pagos[0]['estab'],
                    'pto_emi' => $pagos[0]['pto_emi'],
                    'secuencial' => $pagos[0]['secuencial'],
                    'cliente' => $pagos[0]['razon_social_comprador'],
                    'total_factura' => $pagos[0]['importe_total'],
                    'saldo_actual' => $pagos[0]['saldo'],
                    'valor_pagado' => $pagos[0]['valor_pagado']
                ] : null
            ]
        ]
    ]);
    
} catch (Exception $e) {
    // Respuesta de error
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}
?>
