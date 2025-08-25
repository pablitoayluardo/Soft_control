<?php
// =====================================================
// API PARA OBTENER FACTURAS CON SALDO PENDIENTE
// =====================================================

// Configurar headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
    
    // Obtener parámetros de paginación
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = ($page - 1) * $limit;
    
    // Obtener parámetros de ordenamiento
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'fecha_emision';
    $order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';
    
    // Obtener parámetros de filtrado
    $clienteFilter = isset($_GET['cliente']) ? trim($_GET['cliente']) : '';
    $secuencialFilter = isset($_GET['secuencial']) ? trim($_GET['secuencial']) : '';
    
    // Validar parámetros de ordenamiento
    $allowedSortFields = ['secuencial', 'fecha_emision', 'cliente', 'saldo'];
    $allowedOrders = ['ASC', 'DESC'];
    
    if (!in_array($sort, $allowedSortFields)) {
        $sort = 'fecha_emision';
    }
    
    if (!in_array($order, $allowedOrders)) {
        $order = 'DESC';
    }
    
    // Mapear campos de ordenamiento a columnas de la base de datos
    $sortFieldMap = [
        'secuencial' => 'it.secuencial',
        'fecha_emision' => 'f.fecha_emision',
        'cliente' => 'f.razon_social_comprador',
        'saldo' => '(f.importe_total - COALESCE(f.valor_pagado, 0))'
    ];
    
    $sortField = $sortFieldMap[$sort];
    
    // Construir la consulta principal
    $sql = "SELECT 
        it.id_info_tributaria as id,
        it.estab,
        it.pto_emi,
        it.secuencial,
        it.clave_acceso,
        f.fecha_emision,
        f.razon_social_comprador as cliente,
        f.direccion_comprador as direccion,
        f.importe_total as total,
        f.estatus,
        COALESCE(f.retencion, 0) as retencion,
        COALESCE(f.valor_pagado, 0) as valor_pagado,
        (f.importe_total - COALESCE(f.valor_pagado, 0)) as saldo_pendiente,
        f.observacion
    FROM info_factura f 
    JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
    WHERE f.estatus = 'REGISTRADO' 
    AND f.importe_total > COALESCE(f.valor_pagado, 0)";
    
    // Construir WHERE y PARAMS para filtros adicionales
    $whereClauses = [];
    $params = [];
    
    if (!empty($clienteFilter)) {
        $whereClauses[] = "f.razon_social_comprador LIKE ?";
        $params[] = "%$clienteFilter%";
    }
    
    if (!empty($secuencialFilter)) {
        $whereClauses[] = "it.secuencial LIKE ?";
        $params[] = "%$secuencialFilter%";
    }
    
    if (!empty($whereClauses)) {
        $sql .= " AND " . implode(' AND ', $whereClauses);
    }
    
    // Obtener el total de registros con el filtro aplicado
    $sqlCount = "SELECT COUNT(*) as total 
                 FROM info_factura f 
                 JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
                 WHERE f.estatus = 'REGISTRADO' 
                 AND f.importe_total > COALESCE(f.valor_pagado, 0)";
    
    if (!empty($whereClauses)) {
        $sqlCount .= " AND " . implode(' AND ', $whereClauses);
    }
    
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $total = $stmtCount->fetchColumn();
    
    // Añadir ordenamiento y paginación a la consulta principal
    $sql .= " ORDER BY $sortField $order LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // Ejecutar la consulta principal
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $facturas = $stmt->fetchAll();
    
    // Calcular información de paginación
    $totalPages = ceil($total / $limit);
    
    // Formatear datos para la respuesta
    $formattedFacturas = [];
    foreach ($facturas as $factura) {
        $formattedFacturas[] = [
            'id' => $factura['id'],
            'clave_acceso' => $factura['clave_acceso'],
            'estab' => $factura['estab'] ?? 'N/A',
            'pto_emi' => $factura['pto_emi'] ?? 'N/A',
            'secuencial' => $factura['secuencial'] ?? 'N/A',
            'fecha_emision' => $factura['fecha_emision'] ? date('d/m/Y', strtotime($factura['fecha_emision'])) : 'N/A',
            'cliente' => $factura['cliente'] ?? 'N/A',
            'direccion' => $factura['direccion'] ?? 'N/A',
            'total' => number_format($factura['total'] ?? 0, 2),
            'estatus' => $factura['estatus'] ?? 'REGISTRADO',
            'retencion' => number_format($factura['retencion'] ?? 0, 2),
            'valor_pagado' => number_format($factura['valor_pagado'] ?? 0, 2),
            'saldo_pendiente' => number_format($factura['saldo_pendiente'] ?? 0, 2),
            'observacion' => $factura['observacion'] ?? ''
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formattedFacturas,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'start' => min($offset + 1, $total),
            'end' => min($offset + $limit, $total)
        ],
        'sorting' => [
            'sort' => $sort,
            'order' => $order
        ],
        'filtering' => [
            'cliente' => $clienteFilter,
            'secuencial' => $secuencialFilter
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage(),
        'debug' => [
            'exception_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
