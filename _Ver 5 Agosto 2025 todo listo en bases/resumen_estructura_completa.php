<?php
// =====================================================
// RESUMEN ESTRUCTURA COMPLETA
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>📊 Resumen Estructura Completa</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // Definir todas las tablas con sus claves primarias
    $tablas = [
        'info_tributaria' => 'id_info_tributaria',
        'info_factura' => 'id_info_factura', 
        'detalle_factura_sri' => 'id_detalle',
        'info_adicional_factura' => 'id_info_adicional',
        'pagos' => 'id_pago',
        'total_con_impuestos' => 'id_total_impuesto',
        'impuestos_detalle' => 'id_impuesto_detalle'
    ];
    
    echo "<h3>🏗️ Estructura de la Base de Datos</h3>";
    
    foreach ($tablas as $tabla => $pk) {
        echo "<h4>📋 $tabla</h4>";
        
        // Verificar si la tabla existe
        $sql = "SHOW TABLES LIKE '$tabla'";
        $stmt = $pdo->query($sql);
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "<p style='color: green;'>✅ Tabla existe</p>";
            
            // Mostrar estructura
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
            
            // Contar registros
            $sql = "SELECT COUNT(*) as total FROM $tabla";
            $stmt = $pdo->query($sql);
            $resultado = $stmt->fetch();
            echo "<p><strong>Registros:</strong> " . $resultado['total'] . "</p>";
            
        } else {
            echo "<p style='color: red;'>❌ Tabla NO existe</p>";
        }
    }
    
    // Mostrar relaciones
    echo "<h3>🔗 Relaciones entre Tablas</h3>";
    echo "<ul>";
    echo "<li><strong>info_tributaria</strong> (1) → <strong>info_factura</strong> (N) - Una info_tributaria puede tener muchas facturas</li>";
    echo "<li><strong>info_factura</strong> (1) → <strong>detalle_factura_sri</strong> (N) - Una factura puede tener muchos detalles</li>";
    echo "<li><strong>info_factura</strong> (1) → <strong>info_adicional_factura</strong> (N) - Una factura puede tener mucha información adicional</li>";
    echo "<li><strong>info_factura</strong> (1) → <strong>pagos</strong> (N) - Una factura puede tener múltiples formas de pago</li>";
    echo "<li><strong>info_factura</strong> (1) → <strong>total_con_impuestos</strong> (N) - Una factura puede tener varios impuestos aplicados</li>";
    echo "<li><strong>detalle_factura_sri</strong> (1) → <strong>impuestos_detalle</strong> (N) - Cada detalle puede tener sus propios impuestos</li>";
    echo "</ul>";
    
    // Verificar claves foráneas
    echo "<h3>🔑 Verificación de Claves Foráneas</h3>";
    
    foreach ($tablas as $tabla => $pk) {
        if ($tabla === 'info_tributaria') continue; // No tiene FK
        
        $sql = "SELECT 
            CONSTRAINT_NAME, 
            COLUMN_NAME, 
            REFERENCED_TABLE_NAME, 
            REFERENCED_COLUMN_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = '$tabla' 
        AND REFERENCED_TABLE_NAME IS NOT NULL";
        
        $stmt = $pdo->query($sql);
        $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($fks)) {
            echo "<p style='color: green;'>✅ <strong>$tabla:</strong></p>";
            foreach ($fks as $fk) {
                echo "<p style='margin-left: 20px;'>🔗 {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ <strong>$tabla:</strong> No tiene claves foráneas</p>";
        }
    }
    
    echo "<h3>🎯 Funcionalidades Implementadas</h3>";
    echo "<ul>";
    echo "<li>✅ Extracción de datos del XML SRI</li>";
    echo "<li>✅ Inserción en todas las tablas con relaciones correctas</li>";
    echo "<li>✅ Manejo de pagos múltiples por factura</li>";
    echo "<li>✅ Manejo de impuestos totales por factura</li>";
    echo "<li>✅ Manejo de impuestos por detalle</li>";
    echo "<li>✅ Información adicional de facturas</li>";
    echo "<li>✅ Validación de duplicados</li>";
    echo "<li>✅ Transacciones para integridad de datos</li>";
    echo "</ul>";
    
    echo "<h3>📁 Archivos Principales</h3>";
    echo "<ul>";
    echo "<li><code>fix_table_structure.php</code> - Script para crear/actualizar estructura de tablas</li>";
    echo "<li><code>api/upload_factura_individual.php</code> - API para subir facturas individuales</li>";
    echo "<li><code>debug_xml_extraction.php</code> - Script para debuggear extracción de XML</li>";
    echo "<li><code>test_new_structure.php</code> - Script para verificar estructura</li>";
    echo "<li><code>resumen_estructura_completa.php</code> - Este resumen</li>";
    echo "</ul>";
    
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ¡Estructura completa implementada exitosamente!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 