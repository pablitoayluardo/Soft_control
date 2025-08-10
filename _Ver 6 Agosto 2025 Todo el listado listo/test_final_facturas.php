<?php
// =====================================================
// TEST FINAL DE FACTURAS - VERIFICACIÓN COMPLETA
// =====================================================

header('Content-Type: text/html; charset=utf-8');
echo "<h2>🎯 Test Final de Facturas</h2>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. Verificar conexión
try {
    $host = 'localhost';
    $dbname = 'globocity_softcontrol';
    $username = 'globocity_softcontrol';
    $password = 'GloboCity2024!';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// 2. Verificar datos
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_factura");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>📊 Total de facturas en info_factura: <strong>$total</strong></p>";
    
    if ($total > 0) {
        $stmt = $pdo->query("
            SELECT 
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
            ORDER BY f.created_at DESC
            LIMIT 5
        ");
        
        $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Últimas facturas:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Estab</th><th>Pto Emi</th><th>Secuencial</th><th>Cliente</th><th>Total</th><th>Estatus</th>";
        echo "</tr>";
        
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($factura['estab']) . "</td>";
            echo "<td>" . htmlspecialchars($factura['pto_emi']) . "</td>";
            echo "<td>" . htmlspecialchars($factura['secuencial']) . "</td>";
            echo "<td>" . htmlspecialchars($factura['cliente']) . "</td>";
            echo "<td>$" . number_format($factura['total'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($factura['estatus']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error consultando datos: " . $e->getMessage() . "</p>";
}

// 3. Test de la API
echo "<h3>🌐 Test de la API get_facturas_simple.php</h3>";
try {
    $api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/api/get_facturas_simple.php';
    $api_response = file_get_contents($api_url);
    
    if ($api_response === false) {
        echo "<p style='color: red;'>❌ No se pudo acceder a la API</p>";
    } else {
        $api_data = json_decode($api_response, true);
        
        if ($api_data) {
            echo "<p style='color: green;'>✅ API responde correctamente</p>";
            echo "<p><strong>Success:</strong> " . ($api_data['success'] ? 'true' : 'false') . "</p>";
            echo "<p><strong>Datos encontrados:</strong> " . count($api_data['data'] ?? []) . "</p>";
            
            if (!empty($api_data['data'])) {
                echo "<h4>📋 Datos de la API:</h4>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background: #f0f0f0;'>";
                echo "<th>Estab</th><th>Pto Emi</th><th>Secuencial</th><th>Cliente</th><th>Total</th><th>Estatus</th>";
                echo "</tr>";
                
                foreach ($api_data['data'] as $factura) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($factura['estab'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($factura['pto_emi'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($factura['secuencial'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($factura['cliente'] ?? 'N/A') . "</td>";
                    echo "<td>$" . number_format($factura['total'] ?? 0, 2) . "</td>";
                    echo "<td>" . htmlspecialchars($factura['estatus'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error decodificando respuesta de la API</p>";
            echo "<p><strong>Respuesta raw:</strong> " . htmlspecialchars(substr($api_response, 0, 500)) . "...</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error testeando API: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🎯 Resumen del Problema Resuelto</h3>";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
echo "<h4>✅ Problema Identificado y Resuelto</h4>";
echo "<p><strong>Problema:</strong> Las facturas no aparecían en la lista porque:</p>";
echo "<ul>";
echo "<li>La página cargaba por defecto en la sección 'dashboard' en lugar de 'ver-facturas'</li>";
echo "<li>La función loadFacturasList() solo se llamaba cuando se hacía clic en el botón 'Ver Facturas'</li>";
echo "<li>No se cargaba automáticamente la lista de facturas al cargar la página</li>";
echo "</ul>";
echo "<p><strong>Solución implementada:</strong></p>";
echo "<ul>";
echo "<li>✅ Cambié la sección activa por defecto de 'dashboard' a 'ver-facturas'</li>";
echo "<li>✅ Agregué loadFacturasList() al DOMContentLoaded para cargar automáticamente</li>";
echo "<li>✅ Modifiqué showSection() para aceptar parámetros opcionales</li>";
echo "<li>✅ Actualicé los botones de navegación para reflejar el cambio</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h3>🔧 Enlaces útiles:</h3>";
echo "<ul>";
echo "<li><a href='facturacion.html' target='_blank'>📊 Ir a Facturación (NUEVA VENTANA)</a></li>";
echo "<li><a href='api/get_facturas_simple.php' target='_blank'>🔍 Ver API directamente</a></li>";
echo "<li><a href='verificar_facturas_hosting.php' target='_blank'>🔍 Verificación completa</a></li>";
echo "</ul>";

echo "<hr>";
echo "<h3>🎉 ¡LISTO!</h3>";
echo "<p>El sistema de facturas ahora debería funcionar correctamente. Las facturas aparecerán automáticamente cuando accedas a <strong>facturacion.html</strong>.</p>";
?> 