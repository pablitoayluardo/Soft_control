<?php
// api/get_factura_details.php

header('Content-Type: application/json');
require_once '../config.php';

$facturaId = $_GET['id'] ?? null;

if (!$facturaId) {
    echo json_encode(['success' => false, 'message' => 'ID de factura no proporcionado.']);
    exit;
}

try {
    $conn = getDBConnection();

    // Obtener datos principales de la factura
    $sql = "SELECT 
                it.estab, it.pto_emi, it.secuencial, it.clave_acceso,
                f.fecha_emision, f.razon_social_comprador, f.identificacion_comprador, 
                f.direccion_comprador, f.importe_total, f.estatus
            FROM info_factura f
            JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
            WHERE f.id_info_tributaria = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$facturaId]);
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$factura) {
        echo json_encode(['success' => false, 'message' => 'Factura no encontrada.']);
        exit;
    }

    // Obtener detalles de la factura (productos/items)
    $sql_detalles = "SELECT 
                        codigo_principal, descripcion, cantidad, precio_unitario, 
                        descuento, precio_total_sin_impuesto
                     FROM detalle_facturas
                     WHERE id_info_factura = ?";
                     
    $stmt_detalles = $conn->prepare($sql_detalles);
    $stmt_detalles->execute([$facturaId]);
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
