<?php
// =====================================================
// DEBUG DE FACTURAS - VERIFICACIÓN RÁPIDA
// =====================================================

// Configuración directa de base de datos
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';
$charset = 'utf8mb4';

echo "<h2>🔍 Debug de Facturas</h2>";

try {
    // Conexión directa a la base de datos
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // 1. Verificar si las tablas existen
    echo "<h3>1. Verificación de tablas:</h3>";
    
    $tables = ['info_tributaria', 'info_factura', 'detalle_factura_sri', 'info_adicional_factura'];
    
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $pdo->query($sql);
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "<p style='color: green;'>✅ Tabla $table existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabla $table NO existe</p>";
        }
    }
    
    // 2. Contar registros en cada tabla
    echo "<h3>2. Conteo de registros:</h3>";
    
    foreach ($tables as $table) {
        $sql = "SELECT COUNT(*) as total FROM $table";
        $stmt = $pdo->query($sql);
        $count = $stmt->fetch()['total'];
        
        echo "<p>📊 $table: $count registros</p>";
    }
    
    // 3. Verificar datos en info_factura
    echo "<h3>3. Datos en info_factura:</h3>";
    
    $sql = "SELECT id, razon_social_comprador, importe_total, estatus, created_at FROM info_factura LIMIT 5";
    $stmt = $pdo->query($sql);
    $facturas = $stmt->fetchAll();
    
    if (count($facturas) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Cliente</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Total</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estatus</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Creado</th>";
        echo "</tr>";
        
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['id'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['razon_social_comprador'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['importe_total'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['estatus'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay datos en info_factura</p>";
    }
    
    // 4. Verificar datos en info_tributaria
    echo "<h3>4. Datos en info_tributaria:</h3>";
    
    $sql = "SELECT id, estab, pto_emi, secuencial FROM info_tributaria LIMIT 5";
    $stmt = $pdo->query($sql);
    $tributaria = $stmt->fetchAll();
    
    if (count($tributaria) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estab</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Pto Emi</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Secuencial</th>";
        echo "</tr>";
        
        foreach ($tributaria as $row) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['id'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estab'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['pto_emi'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['secuencial'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay datos en info_tributaria</p>";
    }
    
    // 5. Test de la consulta JOIN
    echo "<h3>5. Test de consulta JOIN:</h3>";
    
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
    ORDER BY f.created_at DESC
    LIMIT 5";
    
    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll();
    
    if (count($resultados) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estab</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Pto Emi</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Secuencial</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Cliente</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Total</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estatus</th>";
        echo "</tr>";
        
        foreach ($resultados as $row) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estab'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['pto_emi'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['secuencial'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['cliente'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['total'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estatus'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ La consulta JOIN no devuelve resultados</p>";
        
        // Verificar si hay datos pero no coinciden en el JOIN
        $sql1 = "SELECT COUNT(*) as total FROM info_factura";
        $stmt1 = $pdo->query($sql1);
        $count1 = $stmt1->fetch()['total'];
        
        $sql2 = "SELECT COUNT(*) as total FROM info_tributaria";
        $stmt2 = $pdo->query($sql2);
        $count2 = $stmt2->fetch()['total'];
        
        echo "<p>📊 info_factura: $count1 registros</p>";
        echo "<p>📊 info_tributaria: $count2 registros</p>";
        
        if ($count1 > 0 && $count2 > 0) {
            echo "<p style='color: orange;'>⚠️ Hay datos en ambas tablas pero no coinciden en el JOIN</p>";
            
            // Verificar info_tributaria_id en info_factura
            $sql3 = "SELECT info_tributaria_id FROM info_factura WHERE info_tributaria_id IS NOT NULL LIMIT 5";
            $stmt3 = $pdo->query($sql3);
            $ids = $stmt3->fetchAll();
            
            echo "<p>IDs de info_tributaria en info_factura:</p>";
            foreach ($ids as $id) {
                echo "<span style='background: #f0f0f0; padding: 2px 5px; margin: 2px; border-radius: 3px;'>" . $id['info_tributaria_id'] . "</span>";
            }
        }
    }
    
    echo "<h3>🎯 Resumen:</h3>";
    echo "<p>Si no ves datos en el listado, puede ser por:</p>";
    echo "<ul>";
    echo "<li>No hay datos en las tablas</li>";
    echo "<li>Los datos no coinciden en el JOIN (info_tributaria_id)</li>";
    echo "<li>Problema en la API get_facturas_simple.php</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='facturacion.html'>📊 Ir a Facturación</a></p>";
?> 