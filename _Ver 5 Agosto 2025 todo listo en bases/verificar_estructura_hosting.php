<?php
// Script para verificar la estructura exacta de las tablas en el hosting
require_once 'config.php';

try {
    // Usar las constantes definidas en config.php para el hosting
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Verificación de Estructura de Tablas en Hosting</h2>";
    
    // Verificar estructura de info_tributaria
    echo "<h3>1. Estructura de info_tributaria:</h3>";
    $sql = "DESCRIBE info_tributaria";
    $stmt = $pdo->query($sql);
    $tributaria_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Campo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Null</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Key</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Default</th>";
    echo "</tr>";
    
    foreach ($tributaria_columns as $column) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar estructura de info_factura
    echo "<h3>2. Estructura de info_factura:</h3>";
    $sql = "DESCRIBE info_factura";
    $stmt = $pdo->query($sql);
    $factura_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Campo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Null</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Key</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Default</th>";
    echo "</tr>";
    
    foreach ($factura_columns as $column) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar datos existentes
    echo "<h3>3. Datos existentes:</h3>";
    
    // Contar registros
    $sql = "SELECT COUNT(*) as total FROM info_tributaria";
    $stmt = $pdo->query($sql);
    $total_tributaria = $stmt->fetch()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM info_factura";
    $stmt = $pdo->query($sql);
    $total_factura = $stmt->fetch()['total'];
    
    echo "<p><strong>Total registros en info_tributaria:</strong> $total_tributaria</p>";
    echo "<p><strong>Total registros en info_factura:</strong> $total_factura</p>";
    
    if ($total_tributaria > 0 && $total_factura > 0) {
        echo "<h3>4. Prueba de JOIN:</h3>";
        
        // Intentar el JOIN con diferentes nombres de columnas
        $join_queries = [
            'id_info_tributaria' => "SELECT COUNT(*) as total FROM info_factura f JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria",
            'info_tributaria_id' => "SELECT COUNT(*) as total FROM info_factura f JOIN info_tributaria it ON f.info_tributaria_id = it.id",
            'info_tributaria_id_alt' => "SELECT COUNT(*) as total FROM info_factura f JOIN info_tributaria it ON f.info_tributaria_id = it.id_info_tributaria"
        ];
        
        foreach ($join_queries as $name => $query) {
            try {
                $stmt = $pdo->query($query);
                $result = $stmt->fetch()['total'];
                echo "<p><strong>$name:</strong> $result registros</p>";
            } catch (Exception $e) {
                echo "<p><strong>$name:</strong> ❌ Error: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<p><a href='facturacion.html'>📊 Ir a Facturación</a></p>";
    echo "<p><a href='api/get_facturas_simple.php' target='_blank'>🔍 Ver API directamente</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?> 