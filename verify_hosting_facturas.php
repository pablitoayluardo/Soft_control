<?php
/**
 * Script para verificar facturas en el hosting
 * Sube este archivo a tu hosting y ejecútalo
 */

echo "<h2>🔍 Verificación de Facturas en el Hosting</h2>";

// Incluir configuración del hosting
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        echo "<p style='color: red;'>❌ Error de conexión a la base de datos</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";
    
    // Verificar tablas existentes
    echo "<h3>📋 Tablas existentes:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    
    echo "<ul>";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<li>$tableName</li>";
    }
    echo "</ul>";
    
    // Verificar tabla facturas
    echo "<h3>📊 Tabla 'facturas':</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM facturas");
    $count = $stmt->fetch();
    echo "<p>Total de registros en 'facturas': <strong>" . $count['total'] . "</strong></p>";
    
    if ($count['total'] > 0) {
        echo "<h4>📄 Últimas 5 facturas en tabla 'facturas':</h4>";
        $stmt = $pdo->query("SELECT id, numero_factura, cliente, total, fecha_emision, fecha_registro FROM facturas ORDER BY fecha_registro DESC LIMIT 5");
        $facturas = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Número Factura</th><th>Cliente</th><th>Total</th><th>Fecha Emisión</th><th>Fecha Registro</th>";
        echo "</tr>";
        
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td>" . $factura['id'] . "</td>";
            echo "<td>" . $factura['numero_factura'] . "</td>";
            echo "<td>" . $factura['cliente'] . "</td>";
            echo "<td>" . $factura['total'] . "</td>";
            echo "<td>" . $factura['fecha_emision'] . "</td>";
            echo "<td>" . $factura['fecha_registro'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar tabla info_tributaria
    echo "<h3>📊 Tabla 'info_tributaria':</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_tributaria");
    $count = $stmt->fetch();
    echo "<p>Total de registros en 'info_tributaria': <strong>" . $count['total'] . "</strong></p>";
    
    if ($count['total'] > 0) {
        echo "<h4>📄 Últimas 5 facturas en tabla 'info_tributaria':</h4>";
        $stmt = $pdo->query("SELECT id, secuencial, clave_acceso, ruc FROM info_tributaria ORDER BY id DESC LIMIT 5");
        $tributarias = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Secuencial</th><th>Clave Acceso</th><th>RUC</th>";
        echo "</tr>";
        
        foreach ($tributarias as $tributaria) {
            echo "<tr>";
            echo "<td>" . $tributaria['id'] . "</td>";
            echo "<td>" . $tributaria['secuencial'] . "</td>";
            echo "<td>" . $tributaria['clave_acceso'] . "</td>";
            echo "<td>" . $tributaria['ruc'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar tabla info_factura
    echo "<h3>📊 Tabla 'info_factura':</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_factura");
    $count = $stmt->fetch();
    echo "<p>Total de registros en 'info_factura': <strong>" . $count['total'] . "</strong></p>";
    
    if ($count['total'] > 0) {
        echo "<h4>📄 Últimas 5 facturas en tabla 'info_factura':</h4>";
        $stmt = $pdo->query("SELECT info_tributaria_id, fecha_emision, razon_social_comprador, importe_total FROM info_factura ORDER BY fecha_emision DESC LIMIT 5");
        $facturas = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Tributaria ID</th><th>Fecha Emisión</th><th>Cliente</th><th>Total</th>";
        echo "</tr>";
        
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td>" . $factura['info_tributaria_id'] . "</td>";
            echo "<td>" . $factura['fecha_emision'] . "</td>";
            echo "<td>" . $factura['razon_social_comprador'] . "</td>";
            echo "<td>" . $factura['importe_total'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar tabla factura_detalles
    echo "<h3>📊 Tabla 'factura_detalles':</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM factura_detalles");
    $count = $stmt->fetch();
    echo "<p>Total de registros en 'factura_detalles': <strong>" . $count['total'] . "</strong></p>";
    
    if ($count['total'] > 0) {
        echo "<h4>📄 Últimos 5 detalles en tabla 'factura_detalles':</h4>";
        $stmt = $pdo->query("SELECT factura_id, codigo_principal, descripcion, cantidad, precio_unitario FROM factura_detalles ORDER BY id DESC LIMIT 5");
        $detalles = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Factura ID</th><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Precio</th>";
        echo "</tr>";
        
        foreach ($detalles as $detalle) {
            echo "<tr>";
            echo "<td>" . $detalle['factura_id'] . "</td>";
            echo "<td>" . $detalle['codigo_principal'] . "</td>";
            echo "<td>" . $detalle['descripcion'] . "</td>";
            echo "<td>" . $detalle['cantidad'] . "</td>";
            echo "<td>" . $detalle['precio_unitario'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>✅ Verificación completada</h3>";
    echo "<p><strong>Instrucciones:</strong></p>";
    echo "<ul>";
    echo "<li>Si ves datos en la tabla 'facturas', el problema está en el archivo get_facturas_simple.php</li>";
    echo "<li>Si no ves datos en 'facturas' pero sí en 'info_tributaria', las facturas están en el sistema antiguo</li>";
    echo "<li>Si no ves datos en ninguna tabla, las facturas no se están guardando correctamente</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 