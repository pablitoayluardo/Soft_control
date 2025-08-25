<?php
// Script de diagnóstico completo para identificar problemas de JSON
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
    returnJsonResponse(false, 'Hay salida antes de los headers: ' . substr($output_before_headers, 0, 200));
}

// Verificar archivos .htaccess
$htaccess_files = [];
if (file_exists('.htaccess')) {
    $htaccess_content = file_get_contents('.htaccess');
    if (strpos($htaccess_content, 'echo') !== false || strpos($htaccess_content, 'print') !== false) {
        $htaccess_files[] = '.htaccess contiene salida';
    }
}

if (file_exists('api/.htaccess')) {
    $htaccess_api_content = file_get_contents('api/.htaccess');
    if (strpos($htaccess_api_content, 'echo') !== false || strpos($htaccess_api_content, 'print') !== false) {
        $htaccess_files[] = 'api/.htaccess contiene salida';
    }
}

// Verificar archivos de configuración
$config_files = [];
$files_to_check = [
    'config.php',
    'api/config.php',
    '../config.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'echo') !== false || strpos($content, 'print') !== false) {
            $config_files[] = $file . ' contiene salida';
        }
    }
}

// Simular el proceso completo de upload_factura_individual.php
try {
    // Incluir configuración
    require_once 'config.php';
    
    // Verificar si config.php causó salida
    $output_after_config = ob_get_contents();
    if (!empty($output_after_config)) {
        returnJsonResponse(false, 'config.php causó salida: ' . substr($output_after_config, 0, 200));
    }
    
    // Conectar a la base de datos
    $pdo = getDBConnection();
    
    if (!$pdo) {
        returnJsonResponse(false, 'Error de conexión a la base de datos');
    }
    
    // Verificar si hay salida después de la conexión
    $output_after_connection = ob_get_contents();
    if (!empty($output_after_connection)) {
        returnJsonResponse(false, 'La conexión a la BD causó salida: ' . substr($output_after_connection, 0, 200));
    }
    
    // Simular inserción en info_tributaria
    $stmt = $pdo->prepare("
        INSERT INTO info_tributaria (
            ambiente, tipo_emision, razon_social, nombre_comercial, ruc,
            clave_acceso, cod_doc, estab, pto_emi, secuencial, dir_matriz,
            fecha_autorizacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $test_clave_acceso = 'TEST_DIAG_' . time();
    $resultado = $stmt->execute([
        '2', '1', 'Empresa Test', 'Empresa Test', '1234567890001',
        $test_clave_acceso, '01', '001', '001', '000000001',
        'Dirección Test', date('Y-m-d')
    ]);
    
    if (!$resultado) {
        returnJsonResponse(false, 'Error al insertar información tributaria de prueba');
    }
    
    $infoTributariaId = $pdo->lastInsertId();
    
    // Verificar si hay salida después de la inserción
    $output_after_insert = ob_get_contents();
    if (!empty($output_after_insert)) {
        returnJsonResponse(false, 'La inserción causó salida: ' . substr($output_after_insert, 0, 200));
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
        'NO', '04', 'Cliente Test', '1234567890',
        'Dirección Cliente', 100.00, 0.00, 100.00,
        'USD', '01', 'REGISTRADA', 0.00, 100.00,
        'Prueba de diagnóstico completo'
    ]);
    
    if (!$resultado) {
        returnJsonResponse(false, 'Error al insertar información de factura de prueba');
    }
    
    $infoFacturaId = $pdo->lastInsertId();
    
    // Verificar si hay salida final
    $output_final = ob_get_contents();
    if (!empty($output_final)) {
        returnJsonResponse(false, 'Hay salida final no esperada: ' . substr($output_final, 0, 200));
    }
    
    // Respuesta exitosa con información detallada
    $responseData = [
        'clave_acceso' => $test_clave_acceso,
        'secuencial' => '000000001',
        'cliente' => 'Cliente Test',
        'total' => 100.00,
        'info_tributaria_id' => $infoTributariaId,
        'info_factura_id' => $infoFacturaId,
        'diagnostico' => 'Prueba exitosa - No hay salida no deseada',
        'archivos_problematicos' => [
            'htaccess' => $htaccess_files,
            'config' => $config_files
        ],
        'conclusion' => 'El sistema está funcionando correctamente. El problema podría estar en el frontend o en la comunicación con el servidor.'
    ];
    
    returnJsonResponse(true, 'Diagnóstico completo completado exitosamente', $responseData);
    
} catch (Exception $e) {
    returnJsonResponse(false, 'Error en diagnóstico completo: ' . $e->getMessage());
}
?> 