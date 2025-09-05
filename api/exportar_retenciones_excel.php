<?php
// =====================================================
// EXPORTAR RETENCIONES A EXCEL
// =====================================================

// Configuración de la base de datos
require_once '../config.php';

// Verificar que se haya solicitado la exportación
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Parámetros de filtro
    $filtroPeriodo = isset($_GET['periodo']) ? $_GET['periodo'] : '';
    $filtroRuc = isset($_GET['ruc']) ? $_GET['ruc'] : '';
    $filtroFechaDesde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
    $filtroFechaHasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
    $filtroTipoRetencion = isset($_GET['tipo_retencion']) ? $_GET['tipo_retencion'] : '';
    
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
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Consulta para obtener todas las retenciones con detalles
    $query = "
        SELECT 
            rc.id,
            rc.numero_autorizacion,
            DATE_FORMAT(rc.fecha_autorizacion, '%d/%m/%Y') as fecha_autorizacion,
            rc.ambiente,
            rc.razon_social_emisor,
            rc.ruc_emisor,
            rc.clave_acceso,
            rc.razon_social_retenido,
            rc.identificacion_retenido,
            rc.periodo_fiscal,
            DATE_FORMAT(rc.fecha_emision, '%d/%m/%Y') as fecha_emision,
            rd.num_doc_sustento,
            DATE_FORMAT(rd.fecha_emision_doc_sustento, '%d/%m/%Y') as fecha_emision_doc_sustento,
            rd.cod_doc_sustento,
            rd.codigo_retencion,
            rd.base_imponible,
            rd.porcentaje_retener,
            rd.valor_retenido,
            CASE 
                WHEN rd.codigo_retencion = '1' THEN 'IVA'
                WHEN rd.codigo_retencion = '2' THEN 'Renta'
                WHEN rd.codigo_retencion = '6' THEN 'ISD'
                ELSE CONCAT('Otro(', rd.codigo_retencion, ')')
            END as tipo_retencion_descripcion
        FROM rete_cabe rc
        LEFT JOIN rete_deta rd ON rc.id = rd.id_rete_cabe
        $whereClause
        ORDER BY rc.fecha_autorizacion DESC, rc.numero_autorizacion
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $retenciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($retenciones)) {
        echo json_encode(['success' => false, 'message' => 'No hay datos para exportar']);
        exit;
    }
    
    // Generar nombre del archivo
    $fechaExportacion = date('Y-m-d_H-i-s');
    $nombreArchivo = "retenciones_exportadas_{$fechaExportacion}.csv";
    
    // Configurar headers para descarga
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    // Crear archivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados del CSV
    $headers = [
        'ID',
        'N° Autorización',
        'Fecha Autorización',
        'Ambiente',
        'Razón Social Emisor',
        'RUC Emisor',
        'Clave Acceso',
        'Razón Social Sujeto Retenido',
        'Identificación Sujeto Retenido',
        'Período Fiscal',
        'Fecha Emisión',
        'N° Documento Sustento',
        'Fecha Emisión Doc. Sustento',
        'Código Doc. Sustento',
        'Código Retención',
        'Base Imponible',
        'Porcentaje Retener',
        'Valor Retenido',
        'Tipo Retención'
    ];
    
    fputcsv($output, $headers, ';');
    
    // Datos de las retenciones
    foreach ($retenciones as $retencion) {
        $row = [
            $retencion['id'],
            $retencion['numero_autorizacion'],
            $retencion['fecha_autorizacion'],
            $retencion['ambiente'],
            $retencion['razon_social_emisor'],
            $retencion['ruc_emisor'],
            $retencion['clave_acceso'],
            $retencion['razon_social_retenido'],
            $retencion['identificacion_retenido'],
            $retencion['periodo_fiscal'],
            $retencion['fecha_emision'],
            $retencion['num_doc_sustento'],
            $retencion['fecha_emision_doc_sustento'],
            $retencion['cod_doc_sustento'],
            $retencion['codigo_retencion'],
            $retencion['base_imponible'],
            $retencion['porcentaje_retener'],
            $retencion['valor_retenido'],
            $retencion['tipo_retencion_descripcion']
        ];
        
        fputcsv($output, $row, ';');
    }
    
    fclose($output);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
