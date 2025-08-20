<?php
// api/get_factura_details.php

header('Content-Type: application/json');
require_once '../config.php';

$claveAcceso = $_GET['clave_acceso'] ?? null;

if (!$claveAcceso) {
    echo json_encode(['success' => false, 'message' => 'Clave de acceso no proporcionada.']);
    exit;
}

try {
    $conn = getDBConnection();

    // 1. Encontrar el id_info_tributaria y id_info_factura usando la clave de acceso
    $sql_ids = "SELECT it.id_info_tributaria, f.id_info_factura 
                FROM info_tributaria it
                JOIN info_factura f ON it.id_info_tributaria = f.id_info_tributaria
                WHERE it.clave_acceso = ?";
    $stmt_ids = $conn->prepare($sql_ids);
    $stmt_ids->execute([$claveAcceso]);
    $ids = $stmt_ids->fetch(PDO::FETCH_ASSOC);

    if (!$ids) {
        echo json_encode(['success' => false, 'message' => 'Factura no encontrada.']);
        exit;
    }
    
    $infoTributariaId = $ids['id_info_tributaria'];
    $infoFacturaId = $ids['id_info_factura'];

    // 2. Obtener los datos generales de la factura
    $sql_factura = "SELECT 
                       it.estab, it.pto_emi, it.secuencial, it.clave_acceso,
                       f.fecha_emision, f.razon_social_comprador, f.identificacion_comprador, 
                       f.direccion_comprador, f.importe_total, f.estatus
                    FROM info_tributaria it
                    JOIN info_factura f ON it.id_info_tributaria = f.id_info_tributaria
                    WHERE it.id_info_tributaria = ?";
    
    $stmt_factura = $conn->prepare($sql_factura);
    $stmt_factura->execute([$infoTributariaId]);
    $factura = $stmt_factura->fetch(PDO::FETCH_ASSOC);

    if (!$factura) {
        // Esto sería raro si la consulta anterior tuvo éxito, pero es una buena verificación
        echo json_encode(['success' => false, 'message' => 'No se encontraron datos de la factura.']);
        exit;
    }

    // 3. Obtener los detalles de la factura
    $sql_detalles = "SELECT 
                        codigo_principal, descripcion, cantidad, precio_unitario, 
                        descuento, precio_total_sin_impuesto
                     FROM detalle_factura_sri
                     WHERE id_info_factura = ?";
                     
    $stmt_detalles = $conn->prepare($sql_detalles);
    $stmt_detalles->execute([$infoFacturaId]);
    $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
    
    $factura['detalles'] = $detalles;
    
    echo json_encode(['success' => true, 'data' => $factura]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
