<?php
// =====================================================
// VERIFICAR ESTRUCTURA DE INFO_TRIBUTARIA
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔍 Verificando estructura de info_tributaria</h2>";

try {
    $pdo = getDBConnection();

    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";

    // Verificar si la tabla existe
    $sql = "SHOW TABLES LIKE 'info_tributaria'";
    $stmt = $pdo->query($sql);
    $tableExists = $stmt->fetch();

    if (!$tableExists) {
        echo "<p style='color: red;'>❌ La tabla info_tributaria no existe</p>";
        echo "<p>Ejecutando setup_facturacion_complete.php...</p>";
        include 'setup_facturacion_complete.php';
        exit;
    }

    echo "<p style='color: green;'>✅ Tabla info_tributaria existe</p>";

    // Mostrar estructura actual
    echo "<h3>📋 Estructura actual de info_tributaria:</h3>";
    $sql = "DESCRIBE info_tributaria";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Campo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Null</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Key</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Default</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Extra</th>";
    echo "</tr>";

    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Verificar columnas específicas
    $requiredColumns = ['numero_autorizacion', 'fecha_autorizacion'];
    $missingColumns = [];

    foreach ($requiredColumns as $column) {
        $sql = "SHOW COLUMNS FROM info_tributaria LIKE '$column'";
        $stmt = $pdo->query($sql);
        $exists = $stmt->fetch();
        
        if (!$exists) {
            $missingColumns[] = $column;
            echo "<p style='color: red;'>❌ Columna '$column' NO existe</p>";
        } else {
            echo "<p style='color: green;'>✅ Columna '$column' existe</p>";
        }
    }

    // Agregar columnas faltantes
    if (!empty($missingColumns)) {
        echo "<h3>🔧 Agregando columnas faltantes:</h3>";
        
        foreach ($missingColumns as $column) {
            switch ($column) {
                case 'numero_autorizacion':
                    $sql = "ALTER TABLE info_tributaria ADD COLUMN numero_autorizacion VARCHAR(100) AFTER dir_matriz";
                    break;
                case 'fecha_autorizacion':
                    $sql = "ALTER TABLE info_tributaria ADD COLUMN fecha_autorizacion DATE AFTER numero_autorizacion";
                    break;
            }
            
            try {
                $pdo->exec($sql);
                echo "<p style='color: green;'>✅ Columna '$column' agregada exitosamente</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error al agregar columna '$column': " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p style='color: green;'>✅ Todas las columnas requeridas existen</p>";
    }

    // Verificar estructura final
    echo "<h3>📋 Estructura final de info_tributaria:</h3>";
    $sql = "DESCRIBE info_tributaria";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Campo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Null</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Key</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Default</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Extra</th>";
    echo "</tr>";

    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<p style='color: green;'>✅ Verificación completada</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 