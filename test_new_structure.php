<?php
// =====================================================
// TEST NEW STRUCTURE
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🧪 Test New Structure</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // Verificar tablas con nueva estructura
    $tablas = [
        'info_tributaria' => 'id_info_tributaria',
        'info_factura' => 'id_info_factura', 
        'detalle_factura_sri' => 'id_detalle',
        'info_adicional_factura' => 'id_info_adicional',
        'pagos' => 'id_pago',
        'total_con_impuestos' => 'id_total_impuesto',
        'impuestos_detalle' => 'id_impuesto_detalle'
    ];
    
    foreach ($tablas as $tabla => $pk) {
        $sql = "SHOW TABLES LIKE '$tabla'";
        $stmt = $pdo->query($sql);
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "<p style='color: green;'>✅ Tabla $tabla existe</p>";
            
            // Verificar clave primaria
            $sql = "SHOW KEYS FROM $tabla WHERE Key_name = 'PRIMARY'";
            $stmt = $pdo->query($sql);
            $pkInfo = $stmt->fetch();
            
            if ($pkInfo && $pkInfo['Column_name'] === $pk) {
                echo "<p style='color: green;'>✅ Clave primaria $pk correcta en $tabla</p>";
            } else {
                echo "<p style='color: red;'>❌ Clave primaria incorrecta en $tabla</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Tabla $tabla NO existe</p>";
        }
    }
    
    // Verificar relaciones
    echo "<h3>🔗 Verificando relaciones:</h3>";
    
    // Verificar FK en info_factura
    $sql = "SELECT 
        CONSTRAINT_NAME, 
        COLUMN_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'info_factura' 
    AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $stmt = $pdo->query($sql);
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($fks)) {
        foreach ($fks as $fk) {
            echo "<p style='color: green;'>✅ FK {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontraron claves foráneas en info_factura</p>";
    }
    
    // Verificar FK en detalle_factura_sri
    $sql = "SELECT 
        CONSTRAINT_NAME, 
        COLUMN_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'detalle_factura_sri' 
    AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $stmt = $pdo->query($sql);
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($fks)) {
        foreach ($fks as $fk) {
            echo "<p style='color: green;'>✅ FK {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontraron claves foráneas en detalle_factura_sri</p>";
    }
    
    // Verificar FK en info_adicional_factura
    $sql = "SELECT 
        CONSTRAINT_NAME, 
        COLUMN_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'info_adicional_factura' 
    AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $stmt = $pdo->query($sql);
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($fks)) {
        foreach ($fks as $fk) {
            echo "<p style='color: green;'>✅ FK {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontraron claves foráneas en info_adicional_factura</p>";
    }

    // Verificar FK en pagos
    $sql = "SELECT 
        CONSTRAINT_NAME, 
        COLUMN_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'pagos' 
    AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $stmt = $pdo->query($sql);
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($fks)) {
        foreach ($fks as $fk) {
            echo "<p style='color: green;'>✅ FK {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontraron claves foráneas en pagos</p>";
    }

    // Verificar FK en total_con_impuestos
    $sql = "SELECT 
        CONSTRAINT_NAME, 
        COLUMN_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'total_con_impuestos' 
    AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $stmt = $pdo->query($sql);
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($fks)) {
        foreach ($fks as $fk) {
            echo "<p style='color: green;'>✅ FK {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontraron claves foráneas en total_con_impuestos</p>";
    }

    // Verificar FK en impuestos_detalle
    $sql = "SELECT 
        CONSTRAINT_NAME, 
        COLUMN_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'impuestos_detalle' 
    AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $stmt = $pdo->query($sql);
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($fks)) {
        foreach ($fks as $fk) {
            echo "<p style='color: green;'>✅ FK {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontraron claves foráneas en impuestos_detalle</p>";
    }
    
    // Mostrar estructura final
    echo "<h3>📋 Estructura final de las tablas:</h3>";
    
    foreach ($tablas as $tabla => $pk) {
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
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Extra</th>";
        echo "</tr>";
        
        foreach ($columnas as $columna) {
            $bgColor = ($columna['Key'] === 'PRI') ? 'background: #28a745; color: white;' : '';
            echo "<tr style='$bgColor'>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Field'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Type'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Null'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Key'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Default'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>🎯 Próximos pasos:</h3>";
    echo "<ol>";
    echo "<li>Ejecuta <code>fix_table_structure.php</code> para actualizar la estructura</li>";
    echo "<li>Prueba registrar una factura desde la interfaz</li>";
    echo "<li>Verifica que los datos se insertan correctamente</li>";
    echo "<li>Revisa las relaciones entre las tablas</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 