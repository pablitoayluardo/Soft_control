<?php
// api/anular_factura.php
header('Content-Type: application/json');
require_once '../config.php'; // Corregido para usar el archivo de configuración principal

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);
$facturaId = $data['id'] ?? null;

if (!$facturaId) {
    echo json_encode(['success' => false, 'message' => 'ID de factura no proporcionado.']);
    exit;
}

try {
    $conn = getDBConnection(); // Usar la función de conexión existente

    // Verificar si la factura existe
    $stmt = $conn->prepare('SELECT * FROM info_factura WHERE id_info_tributaria = ?');
    $stmt->execute([$facturaId]);
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$factura) {
        echo json_encode(['success' => false, 'message' => 'La factura no existe.']);
        exit;
    }

    // Cambiar el estado a 'ANULADO'
    $stmt = $conn->prepare("UPDATE info_factura SET estatus = 'ANULADO' WHERE id_info_tributaria = ?");
    if ($stmt->execute([$facturaId])) {
        echo json_encode(['success' => true, 'message' => 'Factura anulada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al anular la factura.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
