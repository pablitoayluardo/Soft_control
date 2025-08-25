<?php
// diagnostico_tablas.php - Diagnóstico de estructura de tablas

// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

echo "<h1>Diagnóstico de Estructura de Tablas</h1>";

try {
    // Conexión a la base de datos
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ Conexión a la base de datos exitosa</p>";
    
    // Verificar si existen las tablas
    echo "<h2>1. Verificando existencia de tablas...</h2>";
    
    $tables = ['info_tributaria', 'info_factura', 'detalle_factura_sri'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "<p style='color: green;'>✅ Tabla <strong>$table</strong> existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabla <strong>$table</strong> NO existe</p>";
        }
    }
    
    // Verificar estructura de info_tributaria
    echo "<h2>2. Estructura de info_tributaria:</h2>";
    
    try {
        $stmt = $pdo->query("DESCRIBE info_tributaria");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th>";
        echo "</tr>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "<td>{$col['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Buscar la columna de ID principal
        $primary_key = null;
        foreach ($columns as $col) {
            if ($col['Key'] == 'PRI') {
                $primary_key = $col['Field'];
                break;
            }
        }
        
        if ($primary_key) {
            echo "<p style='color: green;'>✅ Clave primaria de info_tributaria: <strong>$primary_key</strong></p>";
        } else {
            echo "<p style='color: red;'>❌ No se encontró clave primaria en info_tributaria</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al describir info_tributaria: " . $e->getMessage() . "</p>";
    }
    
    // Verificar estructura de info_factura
    echo "<h2>3. Estructura de info_factura:</h2>";
    
    try {
        $stmt = $pdo->query("DESCRIBE info_factura");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th>";
        echo "</tr>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "<td>{$col['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Buscar la columna de ID principal y la columna de relación
        $primary_key = null;
        $foreign_key = null;
        
        foreach ($columns as $col) {
            if ($col['Key'] == 'PRI') {
                $primary_key = $col['Field'];
            }
            if (strpos($col['Field'], 'tributaria') !== false || strpos($col['Field'], 'info_tributaria') !== false) {
                $foreign_key = $col['Field'];
            }
        }
        
        if ($primary_key) {
            echo "<p style='color: green;'>✅ Clave primaria de info_factura: <strong>$primary_key</strong></p>";
        }
        
        if ($foreign_key) {
            echo "<p style='color: green;'>✅ Columna de relación con info_tributaria: <strong>$foreign_key</strong></p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No se encontró columna de relación con info_tributaria</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al describir info_factura: " . $e->getMessage() . "</p>";
    }
    
    // Verificar estructura de detalle_factura_sri
    echo "<h2>4. Estructura de detalle_factura_sri:</h2>";
    
    try {
        $stmt = $pdo->query("DESCRIBE detalle_factura_sri");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th>";
        echo "</tr>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "<td>{$col['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Buscar la columna de relación con info_factura
        $foreign_key = null;
        foreach ($columns as $col) {
            if (strpos($col['Field'], 'factura') !== false || strpos($col['Field'], 'info_factura') !== false) {
                $foreign_key = $col['Field'];
                break;
            }
        }
        
        if ($foreign_key) {
            echo "<p style='color: green;'>✅ Columna de relación con info_factura: <strong>$foreign_key</strong></p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No se encontró columna de relación con info_factura</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al describir detalle_factura_sri: " . $e->getMessage() . "</p>";
    }
    
    // Verificar datos de ejemplo
    echo "<h2>5. Datos de ejemplo:</h2>";
    
    try {
        // Verificar si hay datos en info_tributaria
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_tributaria");
        $total_tributaria = $stmt->fetch()['total'];
        echo "<p>Total de registros en info_tributaria: <strong>$total_tributaria</strong></p>";
        
        if ($total_tributaria > 0) {
            // Mostrar un registro de ejemplo
            $stmt = $pdo->query("SELECT * FROM info_tributaria LIMIT 1");
            $ejemplo = $stmt->fetch();
            
            echo "<p>Ejemplo de registro en info_tributaria:</p>";
            echo "<pre>" . print_r($ejemplo, true) . "</pre>";
        }
        
        // Verificar si hay datos en info_factura
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_factura");
        $total_factura = $stmt->fetch()['total'];
        echo "<p>Total de registros en info_factura: <strong>$total_factura</strong></p>";
        
        if ($total_factura > 0) {
            // Mostrar un registro de ejemplo
            $stmt = $pdo->query("SELECT * FROM info_factura LIMIT 1");
            $ejemplo = $stmt->fetch();
            
            echo "<p>Ejemplo de registro en info_factura:</p>";
            echo "<pre>" . print_r($ejemplo, true) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al verificar datos: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error durante el diagnóstico</h2>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}
?>
