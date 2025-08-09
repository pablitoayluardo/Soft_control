<?php
// Script para verificar que el archivo está correcto
header('Content-Type: application/json; charset=utf-8');

// Verificar si el archivo existe
$archivo = 'api/upload_factura_individual.php';
if (!file_exists($archivo)) {
    echo json_encode([
        'success' => false,
        'message' => 'El archivo api/upload_factura_individual.php no existe'
    ]);
    exit;
}

// Leer el contenido del archivo
$contenido = file_get_contents($archivo);

// Verificar que no tiene echo de debug
if (strpos($contenido, '🔧 Inserta') !== false) {
    echo json_encode([
        'success' => false,
        'message' => 'El archivo contiene texto de debug no deseado'
    ]);
    exit;
}

// Verificar que tiene ob_start()
if (strpos($contenido, 'ob_start()') === false) {
    echo json_encode([
        'success' => false,
        'message' => 'El archivo no tiene ob_start()'
    ]);
    exit;
}

// Verificar que tiene returnJsonResponse
if (strpos($contenido, 'returnJsonResponse') === false) {
    echo json_encode([
        'success' => false,
        'message' => 'El archivo no tiene la función returnJsonResponse'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'El archivo api/upload_factura_individual.php está correcto',
    'data' => [
        'tamaño' => strlen($contenido),
        'líneas' => substr_count($contenido, "\n")
    ]
]);
?> 