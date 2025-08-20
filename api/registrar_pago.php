<?php
header('Content-Type: application/json');
require_once '../config.php';

// Validar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Recibir y sanitizar datos del formulario
$factura_id = filter_input(INPUT_POST, 'factura_id', FILTER_VALIDATE_INT);
$monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
$forma_pago = sanitizeInput($_POST['forma_pago'] ?? '');
$nombre_banco = sanitizeInput($_POST['nombre_banco'] ?? null);
$numero_documento = sanitizeInput($_POST['numero_documento'] ?? null);
$fecha_pago = $_POST['fecha_pago'] ?? date('Y-m-d');
$descripcion = sanitizeInput($_POST['descripcion'] ?? null);
$usuario_registro = sanitizeInput($_POST['usuario_registro'] ?? 'sistema');

// Validar datos requeridos
if (!$factura_id || !$monto || !$forma_pago || !$fecha_pago) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos.']);
    exit;
}

try {
    // Conexión a la base de datos
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Error de conexión a la base de datos.');
    }

    // Iniciar transacción
    $pdo->beginTransaction();

    // 1. Insertar el nuevo registro en la tabla de pagos
    $sql_insert_pago = "INSERT INTO pagos (
        id_info_factura, monto, forma_pago, nombre_banco, 
        numero_documento, fecha_pago, descripcion, usuario_registro
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_insert = $pdo->prepare($sql_insert_pago);
    $stmt_insert->execute([
        $factura_id, $monto, $forma_pago, $nombre_banco, 
        $numero_documento, $fecha_pago, $descripcion, $usuario_registro
    ]);

    // 2. Actualizar el campo valor_pagado en la tabla info_factura
    $sql_update_factura = "UPDATE info_factura 
                           SET valor_pagado = valor_pagado + ? 
                           WHERE id_info_factura = ?";
                           
    $stmt_update = $pdo->prepare($sql_update_factura);
    $stmt_update->execute([$monto, $factura_id]);
    
    // Opcional: Verificar si la factura está completamente pagada para cambiar el estatus
    $sql_check_status = "SELECT importe_total, valor_pagado, retencion FROM info_factura WHERE id_info_factura = ?";
    $stmt_check = $pdo->prepare($sql_check_status);
    $stmt_check->execute([$factura_id]);
    $factura = $stmt_check->fetch();

    if ($factura && ($factura['valor_pagado'] + $factura['retencion']) >= $factura['importe_total']) {
        $sql_set_paid = "UPDATE info_factura SET estatus = 'PAGADO' WHERE id_info_factura = ?";
        $stmt_paid = $pdo->prepare($sql_set_paid);
        $stmt_paid->execute([$factura_id]);
    }
    
    // Confirmar transacción
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Pago registrado exitosamente.']);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    error_log('Error en registrar_pago.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
