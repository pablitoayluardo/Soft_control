<?php
/**
 * Script para verificar las tablas creadas
 */

require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Verificar Tablas</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .info { color: #17a2b8; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f8f9fa; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🔍 Verificación de Tablas Creadas</h1>";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("❌ Error: No se pudo conectar a la base de datos");
    }
    
    echo "<div class='success'>✅ Conexión establecida correctamente</div>";
    echo "<div class='info'>📊 Base de datos: " . DB_NAME . "</div><br>";
    
    // Listar TODAS las tablas
    echo "<h2>📋 TODAS las tablas en la base de datos:</h2>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<table>";
    echo "<tr><th>Nombre de Tabla</th><th>Existe</th></tr>";
    
    $tablasEsperadas = ['TiposImpuesto', 'CodigosRetencion', 'ComprobantesRetencion', 'Contribuyentes', 'DetalleRetenciones', 'DocumentosSustento'];
    
    foreach ($tablas as $tabla) {
        $existe = in_array($tabla, $tablasEsperadas);
        $color = $existe ? 'success' : 'info';
        echo "<tr>";
        echo "<td><strong>$tabla</strong></td>";
        echo "<td class='$color'>" . ($existe ? '✅ Esperada' : 'ℹ️ Otra tabla') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar específicamente las tablas catálogo
    echo "<h2>🎯 Verificación específica de tablas catálogo:</h2>";
    
    $tablasCatalogo = ['TiposImpuesto', 'CodigosRetencion'];
    
    foreach ($tablasCatalogo as $tabla) {
        echo "<h3>📊 Tabla: $tabla</h3>";
        
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "<div class='success'>✅ Tabla '$tabla' existe</div>";
            
            // Mostrar estructura
            $stmt = $pdo->query("DESCRIBE `$tabla`");
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th><th>Extra</th></tr>";
            foreach ($columnas as $columna) {
                echo "<tr>";
                echo "<td><strong>{$columna['Field']}</strong></td>";
                echo "<td>{$columna['Type']}</td>";
                echo "<td>{$columna['Null']}</td>";
                echo "<td>{$columna['Key']}</td>";
                echo "<td>{$columna['Default']}</td>";
                echo "<td>{$columna['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='info'>📈 Total de registros: {$count['total']}</div>";
            
            // Mostrar algunos registros de ejemplo
            if ($count['total'] > 0) {
                $stmt = $pdo->query("SELECT * FROM `$tabla` LIMIT 5");
                $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h4>📝 Primeros 5 registros:</h4>";
                echo "<table>";
                if (!empty($registros)) {
                    // Mostrar encabezados
                    echo "<tr>";
                    foreach (array_keys($registros[0]) as $campo) {
                        echo "<th>$campo</th>";
                    }
                    echo "</tr>";
                    
                    // Mostrar datos
                    foreach ($registros as $registro) {
                        echo "<tr>";
                        foreach ($registro as $valor) {
                            echo "<td>$valor</td>";
                        }
                        echo "</tr>";
                    }
                }
                echo "</table>";
            }
            
        } else {
            echo "<div class='error'>❌ Tabla '$tabla' NO existe</div>";
        }
        echo "<br>";
    }
    
    // Buscar tablas con nombres similares
    echo "<h2>🔍 Búsqueda de tablas con nombres similares:</h2>";
    
    $patrones = ['%tipo%', '%impuesto%', '%codigo%', '%retencion%'];
    
    foreach ($patrones as $patron) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$patron'");
        $tablasSimilares = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($tablasSimilares)) {
            echo "<h4>Patrón '$patron':</h4>";
            echo "<ul>";
            foreach ($tablasSimilares as $tabla) {
                echo "<li><strong>$tabla</strong></li>";
            }
            echo "</ul>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
