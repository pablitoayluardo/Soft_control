<?php
// Script de diagnóstico para identificar problemas de JSON
header('Content-Type: application/json; charset=utf-8');

// Función para limpiar salida
function cleanOutput() {
    if (ob_get_length()) {
        ob_clean();
    }
}

// Función para devolver respuesta JSON
function returnJsonResponse($success, $message, $data = null) {
    cleanOutput();
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar si hay salida antes de los headers
$output_before_headers = ob_get_contents();
if (!empty($output_before_headers)) {
    returnJsonResponse(false, 'Hay salida antes de los headers: ' . substr($output_before_headers, 0, 100));
}

// Incluir configuración
require_once 'config.php';

// Verificar si config.php causó salida
$output_after_config = ob_get_contents();
if (!empty($output_after_config)) {
    returnJsonResponse(false, 'config.php causó salida: ' . substr($output_after_config, 0, 100));
}

// Simular el proceso de upload_factura_individual.php
try {
    // Simular datos de factura
    $facturaInfo = [
        'clave_acceso' => 'TEST_' . time(),
        'secuencial' => '000000001',
        'razon_social_comprador' => 'Cliente Test',
        'importe_total' => 100.00
    ];
    
    // Conectar a la base de datos
    $pdo = getDBConnection();
    
    if (!$pdo) {
        returnJsonResponse(false, 'Error de conexión a la base de datos');
    }
    
    // Verificar si hay salida después de la conexión
    $output_after_connection = ob_get_contents();
    if (!empty($output_after_connection)) {
        returnJsonResponse(false, 'La conexión a la BD causó salida: ' . substr($output_after_connection, 0, 100));
    }
    
    // Simular inserción en info_tributaria
    $stmt = $pdo->prepare("
        INSERT INTO info_tributaria (
            ambiente, tipo_emision, razon_social, nombre_comercial, ruc,
            clave_acceso, cod_doc, estab, pto_emi, secuencial, dir_matriz,
            fecha_autorizacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $resultado = $stmt->execute([
        '2', '1', 'Empresa Test', 'Empresa Test', '1234567890001',
        $facturaInfo['clave_acceso'], '01', '001', '001', $facturaInfo['secuencial'],
        'Dirección Test', date('Y-m-d')
    ]);
    
    if (!$resultado) {
        returnJsonResponse(false, 'Error al insertar información tributaria de prueba');
    }
    
    $infoTributariaId = $pdo->lastInsertId();
    
    // Verificar si hay salida después de la inserción
    $output_after_insert = ob_get_contents();
    if (!empty($output_after_insert)) {
        returnJsonResponse(false, 'La inserción causó salida: ' . substr($output_after_insert, 0, 100));
    }
    
    // Simular inserción en info_factura
    $stmt = $pdo->prepare("
        INSERT INTO info_factura (
            id_info_tributaria, fecha_emision, dir_establecimiento,
            obligado_contabilidad, tipo_identificacion_comprador,
            razon_social_comprador, identificacion_comprador,
            direccion_comprador, total_sin_impuestos, total_descuento,
            importe_total, moneda, forma_pago, estatus, retencion,
            valor_pagado, observacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $resultado = $stmt->execute([
        $infoTributariaId, date('Y-m-d'), 'Dirección Establecimiento',
        'NO', '04', $facturaInfo['razon_social_comprador'], '1234567890',
        'Dirección Cliente', 100.00, 0.00, $facturaInfo['importe_total'],
        'USD', '01', 'REGISTRADA', 0.00, $facturaInfo['importe_total'],
        'Prueba de diagnóstico'
    ]);
    
    if (!$resultado) {
        returnJsonResponse(false, 'Error al insertar información de factura de prueba');
    }
    
    $infoFacturaId = $pdo->lastInsertId();
    
    // Verificar si hay salida final
    $output_final = ob_get_contents();
    if (!empty($output_final)) {
        returnJsonResponse(false, 'Hay salida final no esperada: ' . substr($output_final, 0, 100));
    }
    
    // Respuesta exitosa
    $responseData = [
        'clave_acceso' => $facturaInfo['clave_acceso'],
        'secuencial' => $facturaInfo['secuencial'],
        'cliente' => $facturaInfo['razon_social_comprador'],
        'total' => $facturaInfo['importe_total'],
        'info_tributaria_id' => $infoTributariaId,
        'info_factura_id' => $infoFacturaId,
        'diagnostico' => 'Prueba exitosa - No hay salida no deseada'
    ];
    
    returnJsonResponse(true, 'Diagnóstico completado exitosamente', $responseData);
    
} catch (Exception $e) {
    returnJsonResponse(false, 'Error en diagnóstico: ' . $e->getMessage());
}
?> 