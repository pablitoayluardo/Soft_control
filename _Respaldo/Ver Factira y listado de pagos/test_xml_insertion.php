<?php
// =====================================================
// TEST XML INSERTION
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🧪 Test XML Insertion</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // Verificar tablas
    $tablas = ['info_tributaria', 'info_factura', 'detalle_factura_sri', 'info_adicional_factura'];
    
    foreach ($tablas as $tabla) {
        $sql = "SHOW TABLES LIKE '$tabla'";
        $stmt = $pdo->query($sql);
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "<p style='color: green;'>✅ Tabla $tabla existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabla $tabla NO existe</p>";
        }
    }
    
    // Mostrar estructura de las tablas
    echo "<h3>📋 Estructura de las tablas:</h3>";
    
    foreach ($tablas as $tabla) {
        echo "<h4>$tabla:</h4>";
        $sql = "DESCRIBE $tabla";
        $stmt = $pdo->query($sql);
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Campo</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Null</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Key</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Default</th>";
        echo "</tr>";
        
        foreach ($columnas as $columna) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Field'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Type'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Null'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Key'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar datos existentes
    echo "<h3>📊 Datos existentes:</h3>";
    
    foreach ($tablas as $tabla) {
        $sql = "SELECT COUNT(*) as total FROM $tabla";
        $stmt = $pdo->query($sql);
        $resultado = $stmt->fetch();
        
        echo "<p><strong>$tabla:</strong> " . $resultado['total'] . " registros</p>";
    }
    
    // Mostrar últimos registros
    echo "<h3>📄 Últimos registros:</h3>";
    
    foreach ($tablas as $tabla) {
        echo "<h4>$tabla (últimos 3):</h4>";
        $sql = "SELECT * FROM $tabla ORDER BY id DESC LIMIT 3";
        $stmt = $pdo->query($sql);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($registros)) {
            echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr style='background: #f0f0f0;'>";
            foreach (array_keys($registros[0]) as $columna) {
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>$columna</th>";
            }
            echo "</tr>";
            
            foreach ($registros as $registro) {
                echo "<tr>";
                foreach ($registro as $valor) {
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . substr($valor, 0, 50) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>No hay registros en $tabla</p>";
        }
    }
    
    echo "<h3>🎯 Próximos pasos:</h3>";
    echo "<ol>";
    echo "<li>Sube un archivo XML usando <code>debug_xml_extraction.php</code></li>";
    echo "<li>Verifica que los datos se extraen correctamente</li>";
    echo "<li>Confirma que se insertan en las tablas</li>";
    echo "<li>Revisa los registros en la base de datos</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 