<?php
// =====================================================
// TEST DE TABLAS DE FACTURACIÓN SRI
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🧪 Test de Tablas de Facturación SRI</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
    
    // =====================================================
    // VERIFICAR ESTRUCTURA DE TABLAS
    // =====================================================
    
    echo "<h3>📋 Verificando estructura de tablas:</h3>";
    
    // Verificar info_tributaria
    $sql = "DESCRIBE info_tributaria";
    $stmt = $pdo->query($sql);
    $columns_tributaria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Tabla info_tributaria:</h4>";
    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='border: 1px solid #ddd; padding: 8px;'>Campo</th><th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th><th style='border: 1px solid #ddd; padding: 8px;'>Null</th><th style='border: 1px solid #ddd; padding: 8px;'>Key</th><th style='border: 1px solid #ddd; padding: 8px;'>Default</th></tr>";
    
    foreach ($columns_tributaria as $column) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar info_factura
    $sql = "DESCRIBE info_factura";
    $stmt = $pdo->query($sql);
    $columns_factura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Tabla info_factura:</h4>";
    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='border: 1px solid #ddd; padding: 8px;'>Campo</th><th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th><th style='border: 1px solid #ddd; padding: 8px;'>Null</th><th style='border: 1px solid #ddd; padding: 8px;'>Key</th><th style='border: 1px solid #ddd; padding: 8px;'>Default</th></tr>";
    
    foreach ($columns_factura as $column) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar detalle_factura_sri
    $sql = "DESCRIBE detalle_factura_sri";
    $stmt = $pdo->query($sql);
    $columns_detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Tabla detalle_factura_sri:</h4>";
    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='border: 1px solid #ddd; padding: 8px;'>Campo</th><th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th><th style='border: 1px solid #ddd; padding: 8px;'>Null</th><th style='border: 1px solid #ddd; padding: 8px;'>Key</th><th style='border: 1px solid #ddd; padding: 8px;'>Default</th></tr>";
    
    foreach ($columns_detalle as $column) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar info_adicional_factura
    $sql = "DESCRIBE info_adicional_factura";
    $stmt = $pdo->query($sql);
    $columns_adicional = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Tabla info_adicional_factura:</h4>";
    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='border: 1px solid #ddd; padding: 8px;'>Campo</th><th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th><th style='border: 1px solid #ddd; padding: 8px;'>Null</th><th style='border: 1px solid #ddd; padding: 8px;'>Key</th><th style='border: 1px solid #ddd; padding: 8px;'>Default</th></tr>";
    
    foreach ($columns_adicional as $column) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // =====================================================
    // VERIFICAR DATOS EXISTENTES
    // =====================================================
    
    echo "<h3>📊 Verificando datos existentes:</h3>";
    
    // Contar registros en cada tabla
    $sql = "SELECT COUNT(*) as total FROM info_tributaria";
    $stmt = $pdo->query($sql);
    $totalTributaria = $stmt->fetch()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM info_factura";
    $stmt = $pdo->query($sql);
    $totalFactura = $stmt->fetch()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM detalle_factura_sri";
    $stmt = $pdo->query($sql);
    $totalDetalle = $stmt->fetch()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM info_adicional_factura";
    $stmt = $pdo->query($sql);
    $totalAdicional = $stmt->fetch()['total'];
    
    echo "<p>📋 info_tributaria: $totalTributaria registros</p>";
    echo "<p>📋 info_factura: $totalFactura registros</p>";
    echo "<p>📋 detalle_factura_sri: $totalDetalle registros</p>";
    echo "<p>📋 info_adicional_factura: $totalAdicional registros</p>";
    
    // =====================================================
    // TEST DE CONSULTA COMPLEJA
    // =====================================================
    
    echo "<h3>🔍 Test de consulta compleja:</h3>";
    
    $sql = "SELECT 
        it.id,
        it.secuencial,
        it.clave_acceso as numero_autorizacion,
        it.fecha_autorizacion,
        inf_factura.fecha_emision,
        inf_factura.razon_social_comprador as cliente,
        inf_factura.identificacion_comprador as ruc,
        inf_factura.importe_total,
        inf_factura.estatus,
        COUNT(dfs.id) as total_detalles
    FROM info_tributaria it
    LEFT JOIN info_factura inf_factura ON it.id = inf_factura.info_tributaria_id
    LEFT JOIN detalle_factura_sri dfs ON inf_factura.id = dfs.info_factura_id
    GROUP BY it.id, inf_factura.id
    ORDER BY inf_factura.fecha_emision DESC
    LIMIT 5";
    
    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($resultados) > 0) {
        echo "<h4>Datos de ejemplo:</h4>";
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Secuencial</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Número Autorización</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Fecha Emisión</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Cliente</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>RUC</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Total</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estatus</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Detalles</th>";
        echo "</tr>";
        
        foreach ($resultados as $row) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['id'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['secuencial'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['numero_autorizacion'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['fecha_emision'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['cliente'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['ruc'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['importe_total'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estatus'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['total_detalles'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay datos en las tablas. Para probar:</p>";
        echo "<ol>";
        echo "<li>Ejecuta <code>setup_facturacion_complete.php</code> para configurar las tablas</li>";
        echo "<li>Sube facturas desde <code>facturacion.html</code></li>";
        echo "<li>O ejecuta <code>load_factura_sri.php</code> para cargar datos de ejemplo</li>";
        echo "</ol>";
    }
    
    // =====================================================
    // TEST DE API
    // =====================================================
    
    echo "<h3>🌐 Test de API:</h3>";
    
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
    
    echo "<h3>🎉 ¡Test completado exitosamente!</h3>";
    
    // Enlaces útiles
    echo "<hr>";
    echo "<h3>🔗 Enlaces Útiles:</h3>";
    echo "<p><a href='facturacion.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📊 Ir a Facturación</a></p>";
    echo "<p><a href='setup_facturacion_complete.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🔧 Setup Tablas</a></p>";
    echo "<p><a href='test_api_simple.html' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🧪 Test API</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Test completado - Sistema de Control GloboCity</em></p>";
?> 