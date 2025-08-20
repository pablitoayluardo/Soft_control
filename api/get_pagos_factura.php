<?php
header('Content-Type: application/json');
require_once '../config.php';

// Validar que se reciba el ID de la factura
$id_info_factura = filter_input(INPUT_GET, 'id_info_factura', FILTER_VALIDATE_INT);
if (!$id_info_factura) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de factura no proporcionado o inválido.']);
    exit;
}

try {
    // Conexión a la base de datos
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Error de conexión a la base de datos.');
    }

    // Consulta para obtener los pagos de la factura
    $sql = "SELECT 
                monto,
                forma_pago,
                nombre_banco,
                numero_documento,
                DATE_FORMAT(fecha_pago, '%d/%m/%Y') as fecha_pago,
                descripcion,
                usuario_registro
            FROM pagos 
            WHERE id_info_factura = ?
            ORDER BY fecha_pago DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_info_factura]);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $pagos]);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error en get_pagos_factura.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
