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
    // Conexión directa a la base de datos, igual que en el script de listado que funciona
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Configuración de base de datos incompleta en el servidor.');
    }
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $conn = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // 1. Encontrar la factura usando la clave de acceso
    $sql = "SELECT 
                it.id_info_tributaria,
                it.estab, it.pto_emi, it.secuencial, it.clave_acceso,
                f.id_info_factura AS info_factura_id,
                f.fecha_emision, f.razon_social_comprador, f.identificacion_comprador, 
                f.direccion_comprador, f.importe_total, f.estatus
            FROM info_tributaria it
            JOIN info_factura f ON it.id_info_tributaria = f.id_info_tributaria
            WHERE it.clave_acceso = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$claveAcceso]);
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$factura) {
        echo json_encode(['success' => false, 'message' => 'Factura no encontrada con la clave de acceso proporcionada.']);
        exit;
    }

    // Usar el ID de info_factura que acabamos de obtener para buscar los detalles
    $infoFacturaId = $factura['info_factura_id'];

    // Obtener detalles de la factura (productos/items)
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
