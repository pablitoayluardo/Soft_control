<?php
// =====================================================
// TEST DE LISTADO DE FACTURAS CON CAMPOS ESPECÍFICOS
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>📋 Test de Listado de Facturas</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
    
    // =====================================================
    // CONSULTA CON CAMPOS ESPECÍFICOS
    // =====================================================
    
    echo "<h3>🔍 Consultando facturas con campos específicos:</h3>";
    
    $sql = "SELECT 
        it.estab,
        it.pto_emi,
        it.secuencial,
        f.created_at as fecha_creacion,
        f.razon_social_comprador as cliente,
        f.direccion_comprador as direccion,
        f.importe_total as total,
        f.estatus,
        f.retencion,
        f.valor_pagado,
        f.observacion
    FROM info_factura f 
    JOIN info_tributaria it ON f.info_tributaria_id = it.id
    ORDER BY f.created_at DESC, f.fecha_emision DESC
    LIMIT 10";
    
    $stmt = $pdo->query($sql);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($facturas) > 0) {
        echo "<h4>📊 Datos de facturas encontradas:</h4>";
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ESTAB</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>PTO EMI</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>SECUENCIAL</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>FECHA CREACIÓN</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>CLIENTE</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>DIRECCIÓN</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>TOTAL</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ESTATUS</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>RETENCIÓN</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>VALOR PAGADO</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>OBSERVACIÓN</th>";
        echo "</tr>";
        
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($factura['estab'] ?: 'N/A') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($factura['pto_emi'] ?: 'N/A') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($factura['secuencial'] ?: 'N/A') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($factura['fecha_creacion'] ? date('d/m/Y H:i:s', strtotime($factura['fecha_creacion'])) : 'N/A') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($factura['cliente'] ?: 'N/A') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($factura['direccion'] ?: 'N/A') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>$" . number_format($factura['total'] ?: 0, 2) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: " . ($factura['estatus'] === 'REGISTRADA' ? '#28a745' : '#dc3545') . ";'>" . ($factura['estatus'] ?: 'PENDIENTE') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>$" . number_format($factura['retencion'] ?: 0, 2) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>$" . number_format($factura['valor_pagado'] ?: 0, 2) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($factura['observacion'] ?: 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p style='color: green;'>✅ <strong>Se encontraron " . count($facturas) . " facturas</strong></p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ No hay facturas registradas en las tablas.</p>";
        echo "<p>Para probar el sistema:</p>";
        echo "<ol>";
        echo "<li>Ejecuta <code>setup_facturacion_complete.php</code> para configurar las tablas</li>";
        echo "<li>Sube facturas desde <code>facturacion.html</code></li>";
        echo "<li>O ejecuta <code>load_factura_sri.php</code> para cargar datos de ejemplo</li>";
        echo "</ol>";
    }
    
    // =====================================================
    // TEST DE API
    // =====================================================
    
    echo "<h3>🌐 Test de API get_facturas_simple.php:</h3>";
    
    // Simular llamada a la API
    $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/get_facturas_simple.php';
    echo "<p>API URL: <code>$apiUrl</code></p>";
    
    echo "<h4>Respuesta de la API:</h4>";
    echo "<div style='background: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 5px;'>";
    echo "<pre style='margin: 0; white-space: pre-wrap;'>";
    
    // Hacer llamada a la API
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = file_get_contents($apiUrl, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo "Error al conectar con la API";
    }
    
    echo "</pre>";
    echo "</div>";
    
    // =====================================================
    // VERIFICAR ESTRUCTURA DE TABLAS
    // =====================================================
    
    echo "<h3>📋 Verificando campos en las tablas:</h3>";
    
    // Verificar campos en info_tributaria
    $sql = "DESCRIBE info_tributaria";
    $stmt = $pdo->query($sql);
    $columns_tributaria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Campos en info_tributaria:</h4>";
    echo "<ul>";
    foreach ($columns_tributaria as $column) {
        $required = in_array($column['Field'], ['estab', 'pto_emi', 'secuencial']) ? ' <strong style="color: #28a745;">(REQUERIDO)</strong>' : '';
        echo "<li>" . $column['Field'] . " - " . $column['Type'] . $required . "</li>";
    }
    echo "</ul>";
    
    // Verificar campos en info_factura
    $sql = "DESCRIBE info_factura";
    $stmt = $pdo->query($sql);
    $columns_factura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Campos en info_factura:</h4>";
    echo "<ul>";
    foreach ($columns_factura as $column) {
        $required = in_array($column['Field'], ['created_at', 'razon_social_comprador', 'direccion_comprador', 'importe_total', 'estatus', 'retencion', 'valor_pagado', 'observacion']) ? ' <strong style="color: #28a745;">(REQUERIDO)</strong>' : '';
        echo "<li>" . $column['Field'] . " - " . $column['Type'] . $required . "</li>";
    }
    echo "</ul>";
    
    echo "<h3>🎉 ¡Test completado exitosamente!</h3>";
    
    // Enlaces útiles
    echo "<hr>";
    echo "<h3>🔗 Enlaces Útiles:</h3>";
    echo "<p><a href='facturacion.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📊 Ir a Facturación</a></p>";
    echo "<p><a href='setup_facturacion_complete.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🔧 Setup Tablas</a></p>";
    echo "<p><a href='test_facturacion_tables.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🧪 Test Tablas</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Test completado - Sistema de Control GloboCity</em></p>";
?> 