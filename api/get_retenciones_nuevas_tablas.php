<?php
/**
 * API para obtener retenciones usando las nuevas tablas
 * ComprobantesRetencion, Contribuyentes, DetalleRetenciones, DocumentosSustento
 */

require_once '../config.php';

// Función para devolver respuesta JSON
function returnJsonResponse($data, $httpCode = 200) {
    if (function_exists('ob_get_length') && ob_get_length()) {
        @ob_clean();
    }
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    echo json_encode($data);
    exit();
}

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    returnJsonResponse(['success' => true, 'message' => 'OPTIONS request handled']);
}

try {
    // Inicializar conexión a BD
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    // Parámetros de filtro y paginación
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;

    // Filtros
    $filtros = [];
    $params = [];

    if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
        $filtros[] = "cr.fecha_emision >= ?";
        $params[] = $_GET['fecha_desde'];
    }

    if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
        $filtros[] = "cr.fecha_emision <= ?";
        $params[] = $_GET['fecha_hasta'];
    }

    if (isset($_GET['emisor']) && !empty($_GET['emisor'])) {
        $filtros[] = "emisor.razon_social LIKE ?";
        $params[] = '%' . $_GET['emisor'] . '%';
    }

    if (isset($_GET['receptor']) && !empty($_GET['receptor'])) {
        $filtros[] = "receptor.razon_social LIKE ?";
        $params[] = '%' . $_GET['receptor'] . '%';
    }

    if (isset($_GET['numero_comprobante']) && !empty($_GET['numero_comprobante'])) {
        $filtros[] = "cr.numero_comprobante LIKE ?";
        $params[] = '%' . $_GET['numero_comprobante'] . '%';
    }

    if (isset($_GET['estado']) && !empty($_GET['estado'])) {
        $filtros[] = "cr.estado = ?";
        $params[] = $_GET['estado'];
    }

    // Construir WHERE clause
    $whereClause = '';
    if (!empty($filtros)) {
        $whereClause = 'WHERE ' . implode(' AND ', $filtros);
    }

    // Consulta principal con JOINs
    $sql = "
        SELECT 
            cr.id,
            cr.numero_comprobante,
            cr.fecha_emision,
            cr.fecha_autorizacion,
            cr.estado,
            cr.periodo_fiscal,
            emisor.razon_social AS emisor_razon_social,
            emisor.identificacion AS emisor_ruc,
            receptor.razon_social AS receptor_razon_social,
            receptor.identificacion AS receptor_ruc,
            ds.numero_documento_sustento,
            ds.fecha_emision_sustento,
            ds.total_sin_impuestos,
            ds.importe_total,
            GROUP_CONCAT(DISTINCT dr.codigo_retencion ORDER BY dr.codigo_retencion SEPARATOR ', ') AS codigos_retencion,
            GROUP_CONCAT(DISTINCT codigos.descripcion ORDER BY dr.codigo_retencion SEPARATOR ', ') AS tipos_retencion,
            SUM(dr.valor_retenido) AS total_valor_retenido,
            COUNT(dr.id) AS cantidad_retenciones
        FROM 
            ComprobantesRetencion cr
        JOIN 
            Contribuyentes emisor ON cr.emisor_id = emisor.id
        JOIN 
            Contribuyentes receptor ON cr.receptor_id = receptor.id
        LEFT JOIN 
            DocumentosSustento ds ON cr.id = ds.comprobante_retencion_id
        LEFT JOIN 
            DetalleRetenciones dr ON cr.id = dr.comprobante_retencion_id
        LEFT JOIN 
            CodigosRetencion codigos ON dr.codigo_retencion = codigos.codigo
        $whereClause
        GROUP BY cr.id
        ORDER BY cr.fecha_emision DESC, cr.id DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $retenciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contar total de registros para paginación
    $countSql = "
        SELECT COUNT(DISTINCT cr.id) as total
        FROM 
            ComprobantesRetencion cr
        JOIN 
            Contribuyentes emisor ON cr.emisor_id = emisor.id
        JOIN 
            Contribuyentes receptor ON cr.receptor_id = receptor.id
        LEFT JOIN 
            DocumentosSustento ds ON cr.id = ds.comprobante_retencion_id
        LEFT JOIN 
            DetalleRetenciones dr ON cr.id = dr.comprobante_retencion_id
        LEFT JOIN 
            CodigosRetencion codigos ON dr.codigo_retencion = codigos.codigo
        $whereClause
    ";

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRegistros = $countStmt->fetchColumn();

    // Calcular estadísticas
    $statsSql = "
        SELECT 
            COUNT(DISTINCT cr.id) as total_retenciones,
            SUM(dr.valor_retenido) as total_valor_retenido,
            AVG(dr.valor_retenido) as promedio_valor_retenido
        FROM 
            ComprobantesRetencion cr
        LEFT JOIN 
            DetalleRetenciones dr ON cr.id = dr.comprobante_retencion_id
        $whereClause
    ";

    $statsStmt = $pdo->prepare($statsSql);
    $statsStmt->execute($params);
    $estadisticas = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Formatear datos para el frontend
    foreach ($retenciones as &$retencion) {
        // Formatear fechas
        if ($retencion['fecha_emision']) {
            $retencion['fecha_emision_formatted'] = date('d/m/Y', strtotime($retencion['fecha_emision']));
        }
        if ($retencion['fecha_autorizacion']) {
            $retencion['fecha_autorizacion_formatted'] = date('d/m/Y H:i', strtotime($retencion['fecha_autorizacion']));
        }
        if ($retencion['fecha_emision_sustento']) {
            $retencion['fecha_emision_sustento_formatted'] = date('d/m/Y', strtotime($retencion['fecha_emision_sustento']));
        }

        // Formatear montos
        $retencion['total_sin_impuestos_formatted'] = number_format($retencion['total_sin_impuestos'], 2);
        $retencion['importe_total_formatted'] = number_format($retencion['importe_total'], 2);
        $retencion['total_valor_retenido_formatted'] = number_format($retencion['total_valor_retenido'], 2);

        // Clase CSS para estado
        $retencion['estado_clase'] = strtolower($retencion['estado']);
    }

    // Respuesta exitosa
    returnJsonResponse([
        'success' => true,
        'retenciones' => $retenciones,
        'paginacion' => [
            'pagina_actual' => $page,
            'total_paginas' => ceil($totalRegistros / $limit),
            'total_registros' => $totalRegistros,
            'registros_por_pagina' => $limit
        ],
        'estadisticas' => $estadisticas
    ]);

} catch (Exception $e) {
    error_log("Error en get_retenciones_nuevas_tablas: " . $e->getMessage());
    returnJsonResponse([
        'success' => false,
        'message' => 'Error al obtener las retenciones: ' . $e->getMessage()
    ], 500);
}
?>
