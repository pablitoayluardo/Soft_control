<?php
// Script para probar directamente la API
header('Content-Type: application/json; charset=utf-8');

// Función para hacer petición POST
function makePostRequest($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'headers' => $headers,
        'body' => $body,
        'raw_response' => $response
    ];
}

// Crear un archivo XML de prueba
$xml_test = '<?xml version="1.0" encoding="UTF-8"?>
<factura>
    <infoTributaria>
        <ambiente>2</ambiente>
        <tipoEmision>1</tipoEmision>
        <razonSocial>Empresa Test</razonSocial>
        <nombreComercial>Empresa Test</nombreComercial>
        <ruc>1234567890001</ruc>
        <claveAcceso>TEST_API_' . time() . '</claveAcceso>
        <codDoc>01</codDoc>
        <estab>001</estab>
        <ptoEmi>001</ptoEmi>
        <secuencial>000000001</secuencial>
        <dirMatriz>Dirección Test</dirMatriz>
    </infoTributaria>
    <infoFactura>
        <fechaEmision>15/01/2024</fechaEmision>
        <dirEstablecimiento>Dirección Establecimiento</dirEstablecimiento>
        <obligadoContabilidad>NO</obligadoContabilidad>
        <tipoIdentificacionComprador>04</tipoIdentificacionComprador>
        <razonSocialComprador>Cliente Test</razonSocialComprador>
        <identificacionComprador>1234567890</identificacionComprador>
        <direccionComprador>Dirección Cliente</direccionComprador>
        <totalSinImpuestos>100.00</totalSinImpuestos>
        <totalDescuento>0.00</totalDescuento>
        <importeTotal>100.00</importeTotal>
        <moneda>USD</moneda>
        <formaPago>01</formaPago>
    </infoFactura>
    <detallesFactura>
        <detalleFactura>
            <codigoPrincipal>001</codigoPrincipal>
            <descripcion>Producto Test</descripcion>
            <cantidad>1</cantidad>
            <precioUnitario>100.00</precioUnitario>
            <descuento>0.00</descuento>
            <precioTotalSinImpuesto>100.00</precioTotalSinImpuesto>
        </detalleFactura>
    </detallesFactura>
</factura>';

// Guardar el archivo XML temporal
$temp_xml = 'test_factura_' . time() . '.xml';
file_put_contents($temp_xml, $xml_test);

// Preparar datos para la petición
$datos_factura = json_encode([
    'clave_acceso' => 'TEST_API_' . time(),
    'secuencial' => '000000001',
    'cliente' => 'Cliente Test',
    'total' => 100.00
]);

// Crear el array de datos para la petición
$postData = [
    'archivo_xml' => new CURLFile($temp_xml, 'text/xml', 'test_factura.xml'),
    'datos_factura' => $datos_factura
];

// URL de la API
$api_url = 'api/upload_factura_individual.php';

// Hacer la petición
$resultado = makePostRequest($api_url, $postData);

// Limpiar archivo temporal
unlink($temp_xml);

// Analizar la respuesta
$response_analysis = [
    'http_code' => $resultado['http_code'],
    'content_type' => '',
    'response_length' => strlen($resultado['body']),
    'response_preview' => substr($resultado['body'], 0, 500),
    'has_json_start' => strpos($resultado['body'], '{') === 0,
    'has_json_end' => strrpos($resultado['body'], '}') === strlen($resultado['body']) - 1,
    'extra_content_before' => '',
    'extra_content_after' => ''
];

// Extraer Content-Type de los headers
if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $resultado['headers'], $matches)) {
    $response_analysis['content_type'] = trim($matches[1]);
}

// Verificar si hay contenido extra antes del JSON
$json_start = strpos($resultado['body'], '{');
if ($json_start > 0) {
    $response_analysis['extra_content_before'] = substr($resultado['body'], 0, $json_start);
}

// Verificar si hay contenido extra después del JSON
$json_end = strrpos($resultado['body'], '}');
if ($json_end !== false && $json_end < strlen($resultado['body']) - 1) {
    $response_analysis['extra_content_after'] = substr($resultado['body'], $json_end + 1);
}

// Intentar parsear JSON
$json_data = null;
$json_error = '';
try {
    $json_data = json_decode($resultado['body'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $json_error = json_last_error_msg();
    }
} catch (Exception $e) {
    $json_error = $e->getMessage();
}

$response_analysis['json_parse_success'] = $json_data !== null;
$response_analysis['json_error'] = $json_error;
$response_analysis['json_data'] = $json_data;

echo json_encode($response_analysis, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?> 