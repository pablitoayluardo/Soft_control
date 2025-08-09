<?php
// =====================================================
// FIX INFO_TRIBUTARIA - HOSTING
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔧 Fix Info_Tributaria - Hosting</h2>";

try {
    $pdo = getDBConnection();

    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    echo "<p style='color: green;'>✅ <strong>Conexión exitosa al hosting</strong></p>";

    // Verificar si la tabla info_tributaria existe
    $sql = "SHOW TABLES LIKE 'info_tributaria'";
    $stmt = $pdo->query($sql);
    $tableExists = $stmt->fetch();

    if (!$tableExists) {
        echo "<p style='color: red;'>❌ La tabla info_tributaria no existe</p>";
        echo "<p>Creando tabla info_tributaria...</p>";
        
        // Crear tabla info_tributaria completa
        $sql = "CREATE TABLE IF NOT EXISTS info_tributaria (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ambiente VARCHAR(10) NOT NULL,
            tipo_emision VARCHAR(10) NOT NULL,
            razon_social VARCHAR(255) NOT NULL,
            nombre_comercial VARCHAR(255),
            ruc VARCHAR(13) NOT NULL,
            clave_acceso VARCHAR(50) UNIQUE NOT NULL,
            cod_doc VARCHAR(10) NOT NULL,
            estab VARCHAR(10) NOT NULL,
            pto_emi VARCHAR(10) NOT NULL,
            secuencial VARCHAR(20) NOT NULL,
            dir_matriz VARCHAR(255),
            numero_autorizacion VARCHAR(100),
            fecha_autorizacion DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Tabla info_tributaria creada</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabla info_tributaria existe</p>";
    }

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

    // Verificar columnas específicas que faltan
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
            try {
                switch ($column) {
                    case 'numero_autorizacion':
                        $sql = "ALTER TABLE info_tributaria ADD COLUMN numero_autorizacion VARCHAR(100) AFTER dir_matriz";
                        break;
                    case 'fecha_autorizacion':
                        $sql = "ALTER TABLE info_tributaria ADD COLUMN fecha_autorizacion DATE AFTER numero_autorizacion";
                        break;
                }
                
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

    // Verificar que las columnas estén disponibles para consultas
    echo "<h3>🔍 Test de consulta con numero_autorizacion:</h3>";
    try {
        $sql = "SELECT id, secuencial, numero_autorizacion, fecha_autorizacion FROM info_tributaria LIMIT 1";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch();
        
        if ($result) {
            echo "<p style='color: green;'>✅ Consulta exitosa - Columnas disponibles</p>";
            echo "<pre>" . print_r($result, true) . "</pre>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Tabla vacía - Columnas creadas correctamente</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en consulta: " . $e->getMessage() . "</p>";
    }

    echo "<p style='color: green;'>✅ <strong>Fix completado exitosamente</strong></p>";
    echo "<p>🎯 Ahora puedes registrar facturas sin errores de 'numero_autorizacion'</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 