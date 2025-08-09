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
    
    // Verificar si las tablas existen
    $sql = "SHOW TABLES LIKE 'info_factura'";
    $stmt = $pdo->query($sql);
    $infoFacturaExists = $stmt->fetch();
    
    if (!$infoFacturaExists) {
        echo json_encode([
            'success' => false,
            'message' => 'La tabla info_factura no existe',
            'debug' => [
                'info_factura_exists' => false,
                'db_host' => DB_HOST,
                'db_name' => DB_NAME,
                'db_user' => DB_USER
            ]
        ]);
        exit;
    }
    
    // Detectar nombres correctos de columnas
    $sql = "DESCRIBE info_tributaria";
    $stmt = $pdo->query($sql);
    $tributaria_columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    
    $sql = "DESCRIBE info_factura";
    $stmt = $pdo->query($sql);
    $factura_columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    
    // Determinar nombres correctos de columnas
    $tributaria_id_column = 'id_info_tributaria';
    if (!in_array('id_info_tributaria', $tributaria_columns)) {
        if (in_array('id', $tributaria_columns)) {
            $tributaria_id_column = 'id';
        }
    }
    
    $factura_tributaria_id_column = 'id_info_tributaria';
    if (!in_array('id_info_tributaria', $factura_columns)) {
        if (in_array('info_tributaria_id', $factura_columns)) {
            $factura_tributaria_id_column = 'info_tributaria_id';
        }
    }
    
    // Contar registros en la tabla info_factura
    $sql = "SELECT COUNT(*) as total FROM info_factura";
    $stmt = $pdo->query($sql);
    $totalFacturas = $stmt->fetch()['total'];
    
    if ($totalFacturas == 0) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => 0,
                'pages' => 0,
                'has_prev' => false,
                'has_next' => false
            ],
            'debug' => [
                'info_factura_count' => $totalFacturas,
                'message' => 'No hay facturas registradas',
                'tributaria_id_column' => $tributaria_id_column,
                'factura_tributaria_id_column' => $factura_tributaria_id_column
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    // Consulta principal para obtener facturas con los campos específicos solicitados
    // Usando los nombres detectados de columnas
    $sql = "SELECT 
        it.estab,
        it.pto_emi,
        it.secuencial,
        f.fecha_emision as fecha_emision,
        f.razon_social_comprador as cliente,
        f.direccion_comprador as direccion,
        f.importe_total as total,
        f.estatus,
        f.retencion,
        f.valor_pagado,
        f.observacion
    FROM info_factura f 
    JOIN info_tributaria it ON f.$factura_tributaria_id_column = it.$tributaria_id_column";
    
    // Agregar filtro de estatus si se especifica
    $params = [];
    if (!empty($statusFilter)) {
        $sql .= " WHERE f.estatus = ?";
        $params[] = $statusFilter;
    }
    
    $sql .= " ORDER BY $sortField $order LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar total de facturas (considerando filtros)
    $sqlCount = "SELECT COUNT(*) as total FROM info_factura f";
    $countParams = [];
    
    if (!empty($statusFilter)) {
        $sqlCount .= " WHERE f.estatus = ?";
        $countParams[] = $statusFilter;
    }
    
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($countParams);
    $total = $stmtCount->fetch()['total'];
    
    // Calcular información de paginación
    $totalPages = ceil($total / $limit);
    $hasPrev = $page > 1;
    $hasNext = $page < $totalPages;
    
    // Formatear datos para la respuesta con los campos específicos solicitados
    $formattedFacturas = [];
    foreach ($facturas as $factura) {
        // Validación: Facturas con estatus REGISTRADO deben tener 0 en retención y valor pagado
        $retencion = $factura['estatus'] === 'REGISTRADO' ? 0.00 : ($factura['retencion'] ?: 0.00);
        $valorPagado = $factura['estatus'] === 'REGISTRADO' ? 0.00 : ($factura['valor_pagado'] ?: 0.00);
        
        $formattedFacturas[] = [
            'estab' => $factura['estab'] ?: 'N/A',
            'pto_emi' => $factura['pto_emi'] ?: 'N/A',
            'secuencial' => $factura['secuencial'] ?: 'N/A',
            'fecha_emision' => $factura['fecha_emision'] ? date('d/m/Y', strtotime($factura['fecha_emision'])) : 'N/A',
            'cliente' => $factura['cliente'] ?: 'N/A',
            'direccion' => $factura['direccion'] ?: 'N/A',
            'total' => number_format($factura['total'] ?: 0, 2),
            'estatus' => $factura['estatus'] ?: 'REGISTRADO',
            'retencion' => number_format($retencion, 2),
            'valor_pagado' => number_format($valorPagado, 2),
            'observacion' => $factura['observacion'] ?: 'Factura registrada desde XML'
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
            'has_prev' => $hasPrev,
            'has_next' => $hasNext,
            'start' => $offset + 1,
            'end' => min($offset + $limit, $total)
        ],
        'sorting' => [
            'sort' => $sort,
            'order' => $order
        ],
        'filtering' => [
            'status' => $statusFilter
        ],
        'debug' => [
            'info_factura_count' => $totalFacturas,
            'query_limit' => $limit,
            'query_offset' => $offset,
            'results_count' => count($facturas),
            'tributaria_id_column' => $tributaria_id_column,
            'factura_tributaria_id_column' => $factura_tributaria_id_column,
            'sort_field' => $sortField,
            'sort_order' => $order,
            'status_filter' => $statusFilter,
            'message' => 'Consulta exitosa usando nombres detectados de columnas'
        ],
        'timestamp' => date('Y-m-d H:i:s')
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