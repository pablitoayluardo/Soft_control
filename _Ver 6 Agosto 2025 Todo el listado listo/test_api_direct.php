<?php
// Script para probar directamente la API sin cURL
header('Content-Type: application/json; charset=utf-8');

// Simular una petición POST a la API
$_SERVER['REQUEST_METHOD'] = 'POST';
$_FILES['archivo_xml'] = [
    'name' => 'test.xml',
    'type' => 'text/xml',
    'tmp_name' => 'test.xml',
    'error' => 0,
    'size' => 1000
];

$_POST['datos_factura'] = json_encode([
    'clave_acceso' => 'TEST_DIRECT_' . time(),
    'secuencial' => '000000001',
    'cliente' => 'Cliente Test',
    'total' => 100.00
]);

// Crear un archivo XML temporal
$xml_test = '<?xml version="1.0" encoding="UTF-8"?>
<factura>
    <infoTributaria>
        <ambiente>2</ambiente>
        <tipoEmision>1</tipoEmision>
        <razonSocial>Empresa Test</razonSocial>
        <nombreComercial>Empresa Test</nombreComercial>
        <ruc>1234567890001</ruc>
        <claveAcceso>TEST_DIRECT_' . time() . '</claveAcceso>
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

file_put_contents('test.xml', $xml_test);

// Capturar la salida de la API
ob_start();
include 'api/upload_factura_individual.php';
$api_output = ob_get_clean();

// Limpiar archivo temporal
unlink('test.xml');

// Analizar la salida
$analysis = [
    'output_length' => strlen($api_output),
    'output_preview' => substr($api_output, 0, 500),
    'has_json_start' => strpos($api_output, '{') === 0,
    'has_json_end' => strrpos($api_output, '}') === strlen($api_output) - 1,
    'extra_content_before' => '',
    'extra_content_after' => '',
    'json_parse_success' => false,
    'json_error' => '',
    'json_data' => null
];

// Verificar si hay contenido extra antes del JSON
$json_start = strpos($api_output, '{');
if ($json_start > 0) {
    $analysis['extra_content_before'] = substr($api_output, 0, $json_start);
}

// Verificar si hay contenido extra después del JSON
$json_end = strrpos($api_output, '}');
if ($json_end !== false && $json_end < strlen($api_output) - 1) {
    $analysis['extra_content_after'] = substr($api_output, $json_end + 1);
}

// Intentar parsear JSON
try {
    $json_data = json_decode($api_output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $analysis['json_error'] = json_last_error_msg();
    } else {
        $analysis['json_parse_success'] = true;
        $analysis['json_data'] = $json_data;
    }
} catch (Exception $e) {
    $analysis['json_error'] = $e->getMessage();
}

echo json_encode($analysis, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?> 