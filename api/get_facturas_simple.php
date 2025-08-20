<?php
// =====================================================
// API SIMPLIFICADA PARA OBTENER LISTA DE FACTURAS SRI
// =====================================================

// Configurar headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Incluir configuración del hosting
require_once '../config.php';

try {
    // Verificar que las constantes estén definidas
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Configuración de base de datos incompleta');
    }
    
    // Usar las constantes definidas en config.php para el hosting
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Obtener parámetros de paginación
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20; // Cambiado a 20 por defecto
    $offset = ($page - 1) * $limit;
    
    // Obtener parámetros de ordenamiento
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'fecha_emision';
    $order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';
    
    // Obtener parámetros de filtrado
    $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
    $paraPagoFilter = isset($_GET['para_pago']) && $_GET['para_pago'] === 'true';
    
    // Validar parámetros de ordenamiento
    $allowedSortFields = ['secuencial', 'fecha_emision', 'cliente'];
    $allowedOrders = ['ASC', 'DESC'];
    
    if (!in_array($sort, $allowedSortFields)) {
        $sort = 'fecha_emision';
    }
    
    if (!in_array($order, $allowedOrders)) {
        $order = 'DESC';
    }
    
    // Validar parámetros de filtrado
    $allowedStatusFilters = ['', 'REGISTRADO', 'PAGADO', 'ANULADO', 'NOTA CR'];
    if (!in_array($statusFilter, $allowedStatusFilters)) {
        $statusFilter = '';
    }
    
    // Mapear campos de ordenamiento a columnas de la base de datos
    $sortFieldMap = [
        'secuencial' => 'it.secuencial',
        'fecha_emision' => 'f.fecha_emision',
        'cliente' => 'f.razon_social_comprador'
    ];
    
    $sortField = $sortFieldMap[$sort];
    
    // Detectar nombres correctos de columnas para el JOIN
    $tributaria_cols = array_column($pdo->query("DESCRIBE info_tributaria")->fetchAll(), 'Field');
    $factura_cols = array_column($pdo->query("DESCRIBE info_factura")->fetchAll(), 'Field');
    
    $tributaria_id_col = in_array('id_info_tributaria', $tributaria_cols) ? 'id_info_tributaria' : 'id';
    $factura_tributaria_id_col = in_array('info_tributaria_id', $factura_cols) ? 'info_tributaria_id' : 'id_info_tributaria';

    // Construir la consulta principal
    $sql = "SELECT 
        it.$tributaria_id_col as id,
        f.id_info_factura,
        it.estab,
        it.pto_emi,
        it.secuencial,
        it.clave_acceso,
        f.fecha_emision,
        f.razon_social_comprador as cliente,
        f.direccion_comprador as direccion,
        f.importe_total as total,
        f.estatus,
        f.retencion,
        COALESCE(f.valor_pagado, 0) as valor_pagado,
        f.observacion
    FROM info_factura f 
    JOIN info_tributaria it ON f.$factura_tributaria_id_col = it.$tributaria_id_col";
    
    // Construir WHERE y PARAMS para la consulta principal y la de conteo
    $whereClauses = [];
    $params = [];
    
    if ($paraPagoFilter) {
        $whereClauses[] = "f.estatus = 'REGISTRADO'";
        $whereClauses[] = "f.importe_total > f.valor_pagado";
    } elseif (!empty($statusFilter)) {
        $whereClauses[] = "f.estatus = ?";
        $params[] = $statusFilter;
    }

    $whereClause = '';
    if (!empty($whereClauses)) {
        $whereClause = " WHERE " . implode(' AND ', $whereClauses);
    }
    
    // Obtener el total de registros con el filtro aplicado
    $sqlCount = "SELECT COUNT(*) as total FROM info_factura f" . $whereClause;
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $total = $stmtCount->fetchColumn();
    
    // Añadir ordenamiento y paginación a la consulta principal
    $sql .= $whereClause . " ORDER BY $sortField $order LIMIT ? OFFSET ?";
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
            'id_info_factura' => $factura['id_info_factura'],
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
            'observacion' => $factura['observacion'] ?? 'Factura registrada desde XML'
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
            'status' => $statusFilter,
            'para_pago' => $paraPagoFilter
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
            'line' => $e->getLine(),
            'db_host' => defined('DB_HOST') ? DB_HOST : 'NO_DEFINIDO',
            'db_name' => defined('DB_NAME') ? DB_NAME : 'NO_DEFINIDO',
            'db_user' => defined('DB_USER') ? DB_USER : 'NO_DEFINIDO'
        ]
    ]);
}
?> 