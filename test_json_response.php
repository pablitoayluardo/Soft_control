<?php
// Script de prueba para verificar la respuesta JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Función para devolver respuesta JSON
function returnJsonResponse($success, $message, $data = null) {
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

// Simular una respuesta exitosa
returnJsonResponse(true, 'Prueba de respuesta JSON exitosa', [
    'test' => 'data',
    'timestamp' => date('Y-m-d H:i:s')
]);
?> 