<?php
// =====================================================
// TEST SCRIPT PARA VERIFICAR API DE FACTURAS
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔍 Test de API de Facturas</h2>";

try {
    $pdo = getDBConnection();

    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";

    // Verificar si las tablas existen
    $tables = ['info_tributaria', 'info_factura', 'detalle_factura_sri', 'info_adicional_factura'];
    
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $pdo->query($sql);
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "<p style='color: green;'>✅ Tabla '$table' existe</p>";
            
            // Contar registros
            $sql = "SELECT COUNT(*) as total FROM $table";
            $stmt = $pdo->query($sql);
            $count = $stmt->fetch()['total'];
            echo "<p style='color: blue;'>📊 $table: $count registros</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabla '$table' NO existe</p>";
        }
    }

    // Verificar estructura de info_factura
    echo "<h3>📋 Estructura de info_factura:</h3>";
    $sql = "DESCRIBE info_factura";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll();

    echo "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Campo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Tipo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Nulo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Llave</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Por defecto</th>";
    echo "</tr>";

    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Verificar datos de facturas
    echo "<h3>📄 Datos de Facturas:</h3>";
    $sql = "SELECT 
        it.id,
        it.secuencial,
        it.clave_acceso,
        inf_factura.fecha_emision,
        inf_factura.razon_social_comprador as cliente,
        inf_factura.importe_total,
        inf_factura.estatus,
        inf_factura.retencion,
        inf_factura.valor_pagado,
        inf_factura.observacion
    FROM info_tributaria it
    JOIN info_factura inf_factura ON it.id = inf_factura.info_tributaria_id
    ORDER BY inf_factura.fecha_emision DESC
    LIMIT 5";

    $stmt = $pdo->query($sql);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($facturas) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Secuencial</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Clave Acceso</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Fecha</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Cliente</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Total</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Estatus</th>";
        echo "</tr>";

        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['id'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['secuencial'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['clave_acceso'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['fecha_emision'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['cliente'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['importe_total'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['estatus'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay facturas en la base de datos</p>";
    }

    // Test de la API directamente
    echo "<h3>🧪 Test de API get_facturas.php:</h3>";
    
    // Simular autenticación
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test_token';
    
    // Incluir la API
    ob_start();
    include 'api/get_facturas.php';
    $apiResponse = ob_get_clean();
    
    echo "<p><strong>Respuesta de la API:</strong></p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($apiResponse);
    echo "</pre>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Test completado - Sistema de Control GloboCity</em></p>";
?> 