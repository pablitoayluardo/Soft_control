<?php
// =====================================================
// TEST DIRECTO DE FACTURAS - DIAGNÓSTICO RÁPIDO
// =====================================================

header('Content-Type: text/html; charset=utf-8');
echo "<h2>🔍 Test Directo de Facturas</h2>";
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
                f.fecha_emision as fecha_emision,
                f.razon_social_comprador as cliente,
                f.direccion_comprador as direccion,
                f.importe_total as total,
                f.estatus,
                f.retencion,
                f.valor_pagado,
                f.observacion
            FROM info_factura f
            JOIN info_tributaria it ON f.info_tributaria_id = it.id
            ORDER BY f.fecha_emision DESC
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
echo "<h3>🔧 Enlaces útiles:</h3>";
echo "<ul>";
echo "<li><a href='facturacion.html'>📊 Ir a Facturación</a></li>";
echo "<li><a href='api/get_facturas_simple.php'>🔍 Ver API directamente</a></li>";
echo "<li><a href='verificar_facturas_hosting.php'>🔍 Verificación completa</a></li>";
echo "</ul>";
?> 