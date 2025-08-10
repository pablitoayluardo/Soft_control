<?php
// =====================================================
// TEST DE CONEXIÓN SIMPLE
// =====================================================

// Configuración directa de base de datos
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';
$charset = 'utf8mb4';

echo "<h2>🔍 Test de Conexión Simple</h2>";

try {
    // Conexión directa a la base de datos
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";
    
    // Verificar tablas
    $tables = ['info_tributaria', 'info_factura', 'detalle_factura_sri', 'info_adicional_factura'];
    
    echo "<h3>📋 Verificación de tablas:</h3>";
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $pdo->query($sql);
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "<p style='color: green;'>✅ Tabla $table existe</p>";
            
            // Contar registros
            $sql = "SELECT COUNT(*) as total FROM $table";
            $stmt = $pdo->query($sql);
            $count = $stmt->fetch()['total'];
            echo "<p>📊 $table: $count registros</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabla $table NO existe</p>";
        }
    }
    
    // Verificar datos específicos
    echo "<h3>🔍 Verificación de datos:</h3>";
    
    // Verificar info_factura
    $sql = "SELECT COUNT(*) as total FROM info_factura";
    $stmt = $pdo->query($sql);
    $countFactura = $stmt->fetch()['total'];
    
    if ($countFactura > 0) {
        echo "<p style='color: green;'>✅ Hay $countFactura registros en info_factura</p>";
        
        // Mostrar algunos datos
        $sql = "SELECT id, razon_social_comprador, importe_total, estatus, info_tributaria_id FROM info_factura LIMIT 3";
        $stmt = $pdo->query($sql);
        $facturas = $stmt->fetchAll();
        
        echo "<h4>Datos de ejemplo en info_factura:</h4>";
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Cliente</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Total</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estatus</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>info_tributaria_id</th>";
        echo "</tr>";
        
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['id'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['razon_social_comprador'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['importe_total'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['estatus'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['info_tributaria_id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay registros en info_factura</p>";
    }
    
    // Verificar info_tributaria
    $sql = "SELECT COUNT(*) as total FROM info_tributaria";
    $stmt = $pdo->query($sql);
    $countTributaria = $stmt->fetch()['total'];
    
    if ($countTributaria > 0) {
        echo "<p style='color: green;'>✅ Hay $countTributaria registros en info_tributaria</p>";
        
        // Mostrar algunos datos
        $sql = "SELECT id, estab, pto_emi, secuencial FROM info_tributaria LIMIT 3";
        $stmt = $pdo->query($sql);
        $tributaria = $stmt->fetchAll();
        
        echo "<h4>Datos de ejemplo en info_tributaria:</h4>";
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
        echo "<p style='color: orange;'>⚠️ No hay registros en info_tributaria</p>";
    }
    
    // Test de JOIN
    echo "<h3>🔗 Test de JOIN:</h3>";
    
    if ($countFactura > 0 && $countTributaria > 0) {
        $sql = "SELECT 
            it.estab,
            it.pto_emi,
            it.secuencial,
            f.razon_social_comprador as cliente,
            f.importe_total as total,
            f.estatus
        FROM info_factura f 
        JOIN info_tributaria it ON f.info_tributaria_id = it.id
        LIMIT 3";
        
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll();
        
        if (count($resultados) > 0) {
            echo "<p style='color: green;'>✅ JOIN exitoso - Se encontraron " . count($resultados) . " registros</p>";
            
            echo "<h4>Datos del JOIN:</h4>";
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
            echo "<p style='color: red;'>❌ JOIN no devuelve resultados</p>";
            echo "<p>Esto significa que los datos no coinciden entre las tablas.</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No se puede hacer JOIN porque faltan datos en las tablas</p>";
    }
    
    echo "<h3>🎯 Diagnóstico:</h3>";
    if ($countFactura == 0) {
        echo "<p style='color: red;'>❌ No hay datos en info_factura - Necesitas subir facturas primero</p>";
    } elseif ($countTributaria == 0) {
        echo "<p style='color: red;'>❌ No hay datos en info_tributaria - Problema en la carga de datos</p>";
    } elseif (count($resultados) == 0) {
        echo "<p style='color: red;'>❌ Los datos no coinciden en el JOIN - Problema en info_tributaria_id</p>";
    } else {
        echo "<p style='color: green;'>✅ Todo parece estar bien - El problema podría estar en la API o el frontend</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='facturacion.html'>📊 Ir a Facturación</a></p>";
echo "<p><a href='debug_facturas.php'>🔍 Debug Completo</a></p>";
?> 