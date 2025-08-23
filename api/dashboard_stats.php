<?php
// =====================================================
// API DE ESTADÍSTICAS DEL DASHBOARD
// =====================================================

// Configurar headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Incluir configuración
require_once '../config.php';

// Verificar autenticación
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // =====================================================
    // ESTADÍSTICAS DE FACTURACIÓN
    // =====================================================
    
    // Total de facturas
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM info_factura");
    $stmt->execute();
    $facturas_total = $stmt->fetch()['total'];
    
    // Facturas de hoy
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM info_factura WHERE DATE(fecha_emision) = CURDATE()");
    $stmt->execute();
    $facturas_hoy = $stmt->fetch()['total'];
    
    // Total facturado hoy
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(importe_total), 0) as total FROM info_factura WHERE DATE(fecha_emision) = CURDATE()");
    $stmt->execute();
    $total_facturado_hoy = $stmt->fetch()['total'];
    
    // Total facturado este mes
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(importe_total), 0) as total FROM info_factura WHERE MONTH(fecha_emision) = MONTH(CURDATE()) AND YEAR(fecha_emision) = YEAR(CURDATE())");
    $stmt->execute();
    $total_facturado_mes = $stmt->fetch()['total'];
    
    // =====================================================
    // ESTADÍSTICAS DE PRODUCTOS
    // =====================================================
    
    // Total de productos
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos");
    $stmt->execute();
    $productos_total = $stmt->fetch()['total'];
    
    // Productos con stock bajo
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE stock_actual <= stock_minimo");
    $stmt->execute();
    $productos_stock_bajo = $stmt->fetch()['total'];
    
    // Productos disponibles
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE stock_actual > 0");
    $stmt->execute();
    $productos_disponibles = $stmt->fetch()['total'];
    
    // =====================================================
    // ESTADÍSTICAS DE CLIENTES
    // =====================================================
    
    // Total de clientes
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM clientes");
    $stmt->execute();
    $clientes_total = $stmt->fetch()['total'];
    
    // =====================================================
    // ESTADÍSTICAS DE PAGOS
    // =====================================================
    
    // Total de pagos hoy
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pagos WHERE DATE(fecha_pago) = CURDATE()");
    $stmt->execute();
    $pagos_hoy = $stmt->fetch()['total'];
    
    // Total pagado hoy
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE DATE(fecha_pago) = CURDATE()");
    $stmt->execute();
    $total_pagado_hoy = $stmt->fetch()['total'];
    
    // =====================================================
    // ESTADÍSTICAS DE GASTOS
    // =====================================================
    
    // Total de gastos hoy
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM gastos WHERE DATE(fecha_gasto) = CURDATE()");
    $stmt->execute();
    $gastos_hoy = $stmt->fetch()['total'];
    
    // Total gastado hoy
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM gastos WHERE DATE(fecha_gasto) = CURDATE()");
    $stmt->execute();
    $total_gastado_hoy = $stmt->fetch()['total'];
    
    // =====================================================
    // ESTADÍSTICAS DE DETALLES DE FACTURACIÓN
    // =====================================================
    
    // Productos más vendidos
    $stmt = $pdo->prepare("
        SELECT 
            codigo_principal,
            descripcion,
            SUM(cantidad) as total_vendido,
            SUM(precio_total_sin_impuesto) as total_facturado
        FROM detalle_factura_sri 
        GROUP BY codigo_principal, descripcion 
        ORDER BY total_vendido DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $productos_mas_vendidos = $stmt->fetchAll();
    
    // =====================================================
    // COMPILAR ESTADÍSTICAS
    // =====================================================
    
    $stats = [
        'facturacion' => [
            'total' => $facturas_total,
            'hoy' => $facturas_hoy,
            'total_facturado_hoy' => $total_facturado_hoy,
            'total_facturado_mes' => $total_facturado_mes
        ],
        'productos' => [
            'total' => $productos_total,
            'disponibles' => $productos_disponibles,
            'stock_bajo' => $productos_stock_bajo
        ],
        'clientes' => [
            'total' => $clientes_total
        ],
        'pagos' => [
            'hoy' => $pagos_hoy,
            'total_pagado_hoy' => $total_pagado_hoy
        ],
        'gastos' => [
            'hoy' => $gastos_hoy,
            'total_gastado_hoy' => $total_gastado_hoy
        ],
        'productos_mas_vendidos' => $productos_mas_vendidos
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    logActivity('ERROR', 'Error en dashboard_stats: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
?> 