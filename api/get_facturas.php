<?php
// =====================================================
// API PARA OBTENER LISTA DE FACTURAS SRI
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
    
    // Obtener parámetros de paginación
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    
    // Consulta principal para obtener facturas
    $sql = "SELECT 
        it.id,
        it.secuencial,
        it.clave_acceso,
        it.ruc,
        inf_factura.fecha_emision,
        inf_factura.razon_social_comprador as cliente,
        inf_factura.direccion_comprador,
        inf_factura.importe_total,
        inf_factura.estatus,
        inf_factura.retencion,
        inf_factura.valor_pagado,
        inf_factura.observacion,
        inf_factura.created_at
    FROM info_tributaria it
    JOIN info_factura inf_factura ON it.id = inf_factura.info_tributaria_id
    ORDER BY inf_factura.fecha_emision DESC
    LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit, $offset]);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar total de facturas
    $sqlCount = "SELECT COUNT(*) as total FROM info_tributaria it
                  JOIN info_factura inf_factura ON it.id = inf_factura.info_tributaria_id";
    $stmtCount = $pdo->query($sqlCount);
    $total = $stmtCount->fetch()['total'];
    
    // Formatear datos para la respuesta
    $formattedFacturas = [];
    foreach ($facturas as $factura) {
        $formattedFacturas[] = [
            'id' => $factura['id'],
            'fecha' => date('d/m/Y', strtotime($factura['fecha_emision'])),
            'secuencia' => $factura['secuencial'],
            'numero_factura' => $factura['clave_acceso'],
            'cliente' => $factura['cliente'],
            'direccion' => $factura['direccion_comprador'],
            'total_fac' => number_format($factura['importe_total'], 2),
            'estatus' => $factura['estatus'] ?: 'PENDIENTE',
            'retencion' => $factura['retencion'] ?: '0.00',
            'valor_pagado' => $factura['valor_pagado'] ?: '0.00',
            'observacion' => $factura['observacion'] ?: '',
            'ruc' => $factura['ruc']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formattedFacturas,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    logActivity('ERROR', 'Error en get_facturas: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
?> 