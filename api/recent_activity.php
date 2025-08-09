<?php
// =====================================================
// API DE ACTIVIDAD RECIENTE
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
    
    $allActivities = [];
    
    // =====================================================
    // ACTIVIDAD DE FACTURACIÓN SRI
    // =====================================================
    
    $stmt = $pdo->prepare("
        SELECT 
            'factura_sri' as tipo,
            CONCAT('Factura #', it.secuencial) as descripcion,
            inf_factura.fecha_emision as fecha,
            'fas fa-file-invoice' as icono,
            'success' as color,
            CONCAT('$', FORMAT(inf_factura.importe_total, 2)) as monto,
            inf_factura.razon_social_comprador as cliente
        FROM info_factura inf_factura
        JOIN info_tributaria it ON inf_factura.info_tributaria_id = it.id
        WHERE inf_factura.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY inf_factura.fecha_emision DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $facturas_sri = $stmt->fetchAll();
    
    foreach ($facturas_sri as &$factura) {
        $factura['tiempo'] = formatTimeAgo($factura['fecha']);
    }
    
    $allActivities = array_merge($allActivities, $facturas_sri);
    
    // =====================================================
    // ACTIVIDAD DE PAGOS
    // =====================================================
    
    $stmt = $pdo->prepare("
        SELECT 
            'pago' as tipo,
            CONCAT('Pago $', FORMAT(monto, 2)) as descripcion,
            fecha_pago as fecha,
            'fas fa-credit-card' as icono,
            'info' as color,
            CONCAT('$', FORMAT(monto, 2)) as monto,
            metodo_pago as metodo
        FROM pagos 
        WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY fecha_pago DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $pagos = $stmt->fetchAll();
    
    foreach ($pagos as &$pago) {
        $pago['tiempo'] = formatTimeAgo($pago['fecha']);
    }
    
    $allActivities = array_merge($allActivities, $pagos);
    
    // =====================================================
    // ACTIVIDAD DE GASTOS
    // =====================================================
    
    $stmt = $pdo->prepare("
        SELECT 
            'gasto' as tipo,
            CONCAT('Gasto: ', concepto) as descripcion,
            fecha_gasto as fecha,
            'fas fa-receipt' as icono,
            'warning' as color,
            CONCAT('$', FORMAT(monto, 2)) as monto,
            categoria
        FROM gastos 
        WHERE fecha_gasto >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY fecha_gasto DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $gastos = $stmt->fetchAll();
    
    foreach ($gastos as &$gasto) {
        $gasto['tiempo'] = formatTimeAgo($gasto['fecha']);
    }
    
    $allActivities = array_merge($allActivities, $gastos);
    
    // =====================================================
    // ACTIVIDAD DE PRODUCTOS CON STOCK BAJO
    // =====================================================
    
    $stmt = $pdo->prepare("
        SELECT 
            'stock_bajo' as tipo,
            CONCAT('Stock bajo: ', nombre) as descripcion,
            fecha_actualizacion as fecha,
            'fas fa-exclamation-triangle' as icono,
            'danger' as color,
            CONCAT(stock_actual, ' unidades') as stock,
            categoria
        FROM productos 
        WHERE stock_actual <= stock_minimo AND activo = 1
        ORDER BY fecha_actualizacion DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $productos_stock_bajo = $stmt->fetchAll();
    
    foreach ($productos_stock_bajo as &$producto) {
        $producto['tiempo'] = formatTimeAgo($producto['fecha']);
    }
    
    $allActivities = array_merge($allActivities, $productos_stock_bajo);
    
    // =====================================================
    // ORDENAR POR FECHA
    // =====================================================
    
    usort($allActivities, function($a, $b) {
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });
    
    // Limitar a 10 actividades
    $allActivities = array_slice($allActivities, 0, 10);
    
    echo json_encode([
        'success' => true,
        'data' => $allActivities,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    logActivity('ERROR', 'Error en recent_activity: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}

/**
 * Función para formatear tiempo relativo
 */
function formatTimeAgo($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return 'Hace un momento';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "Hace $minutes minuto" . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "Hace $hours hora" . ($hours > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "Hace $days día" . ($days > 1 ? 's' : '');
    } else {
        return date('d/m/Y', $timestamp);
    }
}
?> 