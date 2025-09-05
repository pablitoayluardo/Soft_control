<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de la base de datos
require_once '../config.php';

try {
    // Parámetros de paginación
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
    $offset = ($pagina - 1) * $porPagina;
    
    // Filtros mejorados
    $filtroPeriodo = isset($_GET['periodo']) ? $_GET['periodo'] : '';
    $filtroRuc = isset($_GET['ruc']) ? $_GET['ruc'] : '';
    $filtroFechaDesde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
    $filtroFechaHasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
    $filtroTipoRetencion = isset($_GET['tipo_retencion']) ? $_GET['tipo_retencion'] : '';
    $filtroValorMinimo = isset($_GET['valor_minimo']) ? (float)$_GET['valor_minimo'] : 0;
    $filtroValorMaximo = isset($_GET['valor_maximo']) ? (float)$_GET['valor_maximo'] : 0;
    $ordenarPor = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'fecha_autorizacion';
    $orden = isset($_GET['orden']) ? $_GET['orden'] : 'DESC';
    
    // Validar parámetros de ordenamiento
    $camposPermitidos = ['fecha_autorizacion', 'numero_autorizacion', 'razon_social_emisor', 'valor_retenido', 'base_imponible'];
    $ordenarPor = in_array($ordenarPor, $camposPermitidos) ? $ordenarPor : 'fecha_autorizacion';
    $orden = strtoupper($orden) === 'ASC' ? 'ASC' : 'DESC';
    
    // Construir condiciones WHERE
    $whereConditions = [];
    $params = [];
    
    if (!empty($filtroPeriodo)) {
        $whereConditions[] = "rc.periodo_fiscal = ?";
        $params[] = $filtroPeriodo;
    }
    
    if (!empty($filtroRuc)) {
        $whereConditions[] = "(rc.ruc_emisor LIKE ? OR rc.identificacion_retenido LIKE ? OR rc.razon_social_emisor LIKE ? OR rc.razon_social_retenido LIKE ?)";
        $params[] = "%$filtroRuc%";
        $params[] = "%$filtroRuc%";
        $params[] = "%$filtroRuc%";
        $params[] = "%$filtroRuc%";
    }
    
    if (!empty($filtroFechaDesde)) {
        $whereConditions[] = "rc.fecha_autorizacion >= ?";
        $params[] = $filtroFechaDesde;
    }
    
    if (!empty($filtroFechaHasta)) {
        $whereConditions[] = "rc.fecha_autorizacion <= ?";
        $params[] = $filtroFechaHasta;
    }
    
    if (!empty($filtroTipoRetencion)) {
        $whereConditions[] = "rd.codigo_retencion = ?";
        $params[] = $filtroTipoRetencion;
    }
    
    if ($filtroValorMinimo > 0) {
        $whereConditions[] = "COALESCE(SUM(rd.valor_retenido), 0) >= ?";
        $params[] = $filtroValorMinimo;
    }
    
    if ($filtroValorMaximo > 0) {
        $whereConditions[] = "COALESCE(SUM(rd.valor_retenido), 0) <= ?";
        $params[] = $filtroValorMaximo;
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Consulta optimizada para obtener retenciones con información resumida
    $query = "
        SELECT 
            rc.id,
            rc.numero_autorizacion,
            rc.fecha_autorizacion,
            rc.ambiente,
            rc.razon_social_emisor,
            rc.ruc_emisor,
            rc.clave_acceso,
            rc.razon_social_retenido,
            rc.identificacion_retenido,
            rc.periodo_fiscal,
            rc.fecha_emision,
            COUNT(rd.id) as total_documentos_sustento,
            COALESCE(SUM(rd.valor_retenido), 0) as total_valor_retenido,
            COALESCE(SUM(rd.base_imponible), 0) as total_base_imponible,
            GROUP_CONCAT(DISTINCT 
                CASE 
                    WHEN rd.codigo_retencion = '1' THEN 'IVA'
                    WHEN rd.codigo_retencion = '2' THEN 'Renta'
                    WHEN rd.codigo_retencion = '6' THEN 'ISD'
                    ELSE CONCAT('Otro(', rd.codigo_retencion, ')')
                END
                ORDER BY rd.codigo_retencion
                SEPARATOR ', '
            ) as tipos_retencion,
            GROUP_CONCAT(DISTINCT rd.num_doc_sustento ORDER BY rd.num_doc_sustento SEPARATOR ', ') as documentos_sustento
        FROM rete_cabe rc
        LEFT JOIN rete_deta rd ON rc.id = rd.id_rete_cabe
        $whereClause
        GROUP BY rc.id, rc.numero_autorizacion, rc.fecha_autorizacion, rc.ambiente,
                 rc.razon_social_emisor, rc.ruc_emisor, rc.clave_acceso, rc.razon_social_retenido,
                 rc.identificacion_retenido, rc.periodo_fiscal, rc.fecha_emision
        ORDER BY rc.$ordenarPor $orden
        LIMIT ? OFFSET ?
    ";
    
    // Agregar parámetros de paginación
    $params[] = $porPagina;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $retenciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Consulta para obtener el total de registros
    $countQuery = "
        SELECT COUNT(DISTINCT rc.id) as total
        FROM rete_cabe rc
        LEFT JOIN rete_deta rd ON rc.id = rd.id_rete_cabe
        $whereClause
    ";
    
    $countParams = array_slice($params, 0, -2); // Remover parámetros de paginación
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($countParams);
    $totalRegistros = $stmt->fetch()['total'];
    
    // Calcular total de páginas
    $totalPaginas = ceil($totalRegistros / $porPagina);
    
    // Obtener estadísticas generales
    $statsQuery = "
        SELECT 
            COUNT(DISTINCT rc.id) as total_retenciones,
            COALESCE(SUM(rd.valor_retenido), 0) as total_valor_retenido,
            COALESCE(SUM(rd.base_imponible), 0) as total_base_imponible,
            COUNT(DISTINCT rc.periodo_fiscal) as total_periodos,
            COUNT(DISTINCT rc.ruc_emisor) as total_emisores,
            COUNT(DISTINCT rc.identificacion_retenido) as total_sujetos_retenidos
        FROM rete_cabe rc
        LEFT JOIN rete_deta rd ON rc.id = rd.id_rete_cabe
    ";
    
    $stmt = $pdo->query($statsQuery);
    $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Formatear fechas y valores numéricos
    foreach ($retenciones as &$retencion) {
        // Formatear fecha de autorización
        if ($retencion['fecha_autorizacion']) {
            $fecha = new DateTime($retencion['fecha_autorizacion']);
            $retencion['fecha_autorizacion'] = $fecha->format('Y-m-d');
        }
        
        // Formatear fecha de emisión
        if ($retencion['fecha_emision']) {
            $fecha = new DateTime($retencion['fecha_emision']);
            $retencion['fecha_emision'] = $fecha->format('Y-m-d');
        }
        
        // Formatear valores numéricos
        $retencion['total_valor_retenido'] = number_format((float)$retencion['total_valor_retenido'], 2);
        $retencion['total_base_imponible'] = number_format((float)$retencion['total_base_imponible'], 2);
        
        // Agregar clase CSS para el ambiente (en lugar de estado)
        $retencion['ambiente_clase'] = '';
        switch (strtoupper($retencion['ambiente'])) {
            case '1':
                $retencion['ambiente_clase'] = 'ambiente-pruebas';
                break;
            case '2':
                $retencion['ambiente_clase'] = 'ambiente-produccion';
                break;
            default:
                $retencion['ambiente_clase'] = 'ambiente-desconocido';
        }
    }
    
    // Respuesta exitosa con datos mejorados
    echo json_encode([
        'success' => true,
        'retenciones' => $retenciones,
        'paginacion' => [
            'pagina_actual' => $pagina,
            'por_pagina' => $porPagina,
            'total_registros' => $totalRegistros,
            'total_paginas' => $totalPaginas
        ],
        'estadisticas' => [
            'total_retenciones' => (int)$estadisticas['total_retenciones'],
            'total_valor_retenido' => number_format((float)$estadisticas['total_valor_retenido'], 2),
            'total_base_imponible' => number_format((float)$estadisticas['total_base_imponible'], 2),
            'total_periodos' => (int)$estadisticas['total_periodos'],
            'total_emisores' => (int)$estadisticas['total_emisores'],
            'total_sujetos_retenidos' => (int)$estadisticas['total_sujetos_retenidos']
        ],
        'filtros_aplicados' => [
            'periodo' => $filtroPeriodo,
            'ruc' => $filtroRuc,
            'fecha_desde' => $filtroFechaDesde,
            'fecha_hasta' => $filtroFechaHasta,
            'tipo_retencion' => $filtroTipoRetencion,
            'valor_minimo' => $filtroValorMinimo,
            'valor_maximo' => $filtroValorMaximo,
            'ordenar_por' => $ordenarPor,
            'orden' => $orden
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
