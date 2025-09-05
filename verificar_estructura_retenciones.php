<?php
// =====================================================
// VERIFICAR ESTRUCTURA DE TABLAS DE RETENCIONES
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔍 Verificación de Estructura de Tablas de Retenciones</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
    
    // Verificar si existen las tablas (buscar con diferentes variaciones)
    $tablasABuscar = [
        'Rete_Cabe' => ['Rete_Cabe', 'rete_cabe', 'RETE_CABE'],
        'Rete_deta' => ['Rete_deta', 'rete_deta', 'RETE_DETA']
    ];
    
    $tablasEncontradas = [];
    
    foreach ($tablasABuscar as $nombreTabla => $variaciones) {
        $encontrada = false;
        
        foreach ($variaciones as $variacion) {
            $sql = "SHOW TABLES LIKE '$variacion'";
            $stmt = $pdo->query($sql);
            $existe = $stmt->fetch();
            
            if ($existe) {
                echo "<p style='color: green;'>✅ Tabla <strong>$variacion</strong> existe</p>";
                $tablasEncontradas[$nombreTabla] = $variacion;
                $encontrada = true;
                break;
            }
        }
        
        if (!$encontrada) {
            echo "<p style='color: red;'>❌ Tabla <strong>$nombreTabla</strong> NO existe (buscada como: " . implode(', ', $variaciones) . ")</p>";
        }
    }
    
    // Mostrar estructura de las tablas encontradas
    foreach ($tablasEncontradas as $nombreTabla => $tablaReal) {
        // Mostrar estructura de la tabla
        $sql = "DESCRIBE $tablaReal";
        $stmt = $pdo->query($sql);
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Estructura de $tablaReal:</h3>";
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Campo</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Tipo</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Null</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Key</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Default</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Extra</th>";
        echo "</tr>";
        
        foreach ($columnas as $columna) {
            echo "<tr>";
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
        $sql = "SELECT COUNT(*) as total FROM $tablaReal";
        $stmt = $pdo->query($sql);
        $total = $stmt->fetch()['total'];
        echo "<p><strong>Total de registros en $tablaReal:</strong> $total</p>";
    }
    
    // Verificar relaciones entre tablas
    echo "<h3>🔗 Verificación de Relaciones:</h3>";
    
    if (count($tablasEncontradas) > 0) {
        $tablasParaBuscar = array_values($tablasEncontradas);
        $tablasStr = "'" . implode("', '", $tablasParaBuscar) . "'";
        
        $sql = "SELECT 
                    CONSTRAINT_NAME,
                    TABLE_NAME,
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND REFERENCED_TABLE_NAME IS NOT NULL
                AND (TABLE_NAME IN ($tablasStr) OR REFERENCED_TABLE_NAME IN ($tablasStr))";
        
        $stmt = $pdo->query($sql);
        $relaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($relaciones) > 0) {
            echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr style='background: #28a745; color: white;'>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Restricción</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Tabla</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Columna</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Tabla Referenciada</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Columna Referenciada</th>";
            echo "</tr>";
            
            foreach ($relaciones as $relacion) {
                echo "<tr>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $relacion['CONSTRAINT_NAME'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $relacion['TABLE_NAME'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $relacion['COLUMN_NAME'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $relacion['REFERENCED_TABLE_NAME'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $relacion['REFERENCED_COLUMN_NAME'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No se encontraron relaciones definidas entre las tablas</p>";
        }
        
        // Verificar índices
        echo "<h3>📊 Verificación de Índices:</h3>";
        
        foreach ($tablasEncontradas as $nombreTabla => $tablaReal) {
            try {
                $sql = "SHOW INDEX FROM $tablaReal";
                $stmt = $pdo->query($sql);
                $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($indices) > 0) {
                    echo "<h4>Índices de $tablaReal:</h4>";
                    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
                    echo "<tr style='background: #ffc107; color: black;'>";
                    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Índice</th>";
                    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Columna</th>";
                    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Tipo</th>";
                    echo "</tr>";
                    
                    foreach ($indices as $indice) {
                        echo "<tr>";
                        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $indice['Key_name'] . "</td>";
                        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $indice['Column_name'] . "</td>";
                        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($indice['Non_unique'] ? 'No único' : 'Único') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p style='color: orange;'>⚠️ No se encontraron índices en $tablaReal</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error al verificar índices de $tablaReal: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontraron tablas de retenciones para verificar</p>";
    }
    
    // Enlaces útiles
    echo "<hr>";
    echo "<h3>🔗 Enlaces Útiles:</h3>";
    echo "<p><a href='diagnostico_tablas_hosting.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🔍 Diagnóstico Completo</a></p>";
    echo "<p><a href='test_retenciones_rete_tables.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🧪 Test Tablas</a></p>";
    echo "<p><a href='retenciones.html' style='background: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📊 Ir a Retenciones</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Verificación de estructura - Sistema de Control GloboCity</em></p>";
?>
