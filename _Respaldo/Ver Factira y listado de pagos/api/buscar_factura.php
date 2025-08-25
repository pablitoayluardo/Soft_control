<?php
// =====================================================
// API PARA BUSCAR FACTURA POR ESTAB, PTO_EMI Y SECUENCIAL
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
    
    // Obtener parámetros de búsqueda
    $estab = isset($_GET['estab']) ? trim($_GET['estab']) : '';
    $ptoEmi = isset($_GET['pto_emi']) ? trim($_GET['pto_emi']) : '';
    $secuencial = isset($_GET['secuencial']) ? trim($_GET['secuencial']) : '';
    
    // Validar parámetros requeridos
    if (empty($estab) || empty($ptoEmi) || empty($secuencial)) {
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son requeridos: estab, pto_emi, secuencial'
        ]);
        exit;
    }
    
    // Verificar si las tablas existen
    $sql = "SHOW TABLES LIKE 'info_factura'";
    $stmt = $pdo->query($sql);
    $infoFacturaExists = $stmt->fetch();
    
    if (!$infoFacturaExists) {
        echo json_encode([
            'success' => false,
            'message' => 'La tabla info_factura no existe'
        ]);
        exit;
    }
    
    // Consulta simplificada para buscar la factura
    // Intentar con diferentes estructuras de columnas (ordenadas por probabilidad de éxito)
    $joinAttempts = [
        "f.id_info_tributaria = it.id_info_tributaria",  // Primera opción - más probable
        "f.info_tributaria_id = it.id",                  // Segunda opción
        "f.info_tributaria_id = it.id_info_tributaria"   // Tercera opción
    ];
    
    $factura = null;
    
    foreach ($joinAttempts as $joinCondition) {
        try {
            $sql = "SELECT 
                it.estab,
                it.pto_emi,
                it.secuencial,
                f.fecha_emision,
                f.razon_social_comprador as cliente,
                f.direccion_comprador as direccion,
                f.importe_total as total,
                f.estatus,
                f.retencion,
                f.valor_pagado,
                f.observacion
            FROM info_factura f 
            JOIN info_tributaria it ON $joinCondition
            WHERE it.estab = ? AND it.pto_emi = ? AND it.secuencial = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$estab, $ptoEmi, $secuencial]);
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($factura) {
                break; // Si encontramos la factura, salir del bucle
            }
        } catch (Exception $e) {
            // Continuar con la siguiente combinación si esta falla
            continue;
        }
    }
    
    if (!$factura) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró la factura con los datos proporcionados: Estab=' . $estab . ', PtoEmi=' . $ptoEmi . ', Secuencial=' . $secuencial
        ]);
        exit;
    }
    
    // Formatear datos para la respuesta
    $formattedFactura = [
        'estab' => $factura['estab'] ?: 'N/A',
        'pto_emi' => $factura['pto_emi'] ?: 'N/A',
        'secuencial' => $factura['secuencial'] ?: 'N/A',
        'fecha_emision' => $factura['fecha_emision'] ? date('d/m/Y', strtotime($factura['fecha_emision'])) : 'N/A',
        'cliente' => $factura['cliente'] ?: 'N/A',
        'direccion' => $factura['direccion'] ?: 'N/A',
        'total' => number_format($factura['total'] ?: 0, 2),
        'estatus' => $factura['estatus'] ?: 'REGISTRADO',
        'retencion' => number_format($factura['retencion'] ?: 0, 2),
        'valor_pagado' => number_format($factura['valor_pagado'] ?: 0, 2),
        'observacion' => $factura['observacion'] ?: 'N/A'
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $formattedFactura,
        'message' => 'Factura encontrada exitosamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al buscar la factura: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?> 