<?php
// =====================================================
// ACTUALIZAR TABLA INFO_FACTURA - AGREGAR CAMPOS FALTANTES
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔧 Actualizando Tabla info_factura</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
    
    // Verificar si los campos ya existen
    $sql = "SHOW COLUMNS FROM info_factura LIKE 'estatus'";
    $stmt = $pdo->query($sql);
    $estatusExists = $stmt->fetch();
    
    if (!$estatusExists) {
        // Agregar campo estatus
        $sql = "ALTER TABLE info_factura ADD COLUMN estatus VARCHAR(50) DEFAULT 'PENDIENTE' AFTER forma_pago";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Campo 'estatus' agregado</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Campo 'estatus' ya existe</p>";
    }
    
    // Verificar campo retencion
    $sql = "SHOW COLUMNS FROM info_factura LIKE 'retencion'";
    $stmt = $pdo->query($sql);
    $retencionExists = $stmt->fetch();
    
    if (!$retencionExists) {
        // Agregar campo retencion
        $sql = "ALTER TABLE info_factura ADD COLUMN retencion DECIMAL(10,2) DEFAULT 0.00 AFTER estatus";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Campo 'retencion' agregado</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Campo 'retencion' ya existe</p>";
    }
    
    // Verificar campo valor_pagado
    $sql = "SHOW COLUMNS FROM info_factura LIKE 'valor_pagado'";
    $stmt = $pdo->query($sql);
    $valorPagadoExists = $stmt->fetch();
    
    if (!$valorPagadoExists) {
        // Agregar campo valor_pagado
        $sql = "ALTER TABLE info_factura ADD COLUMN valor_pagado DECIMAL(10,2) DEFAULT 0.00 AFTER retencion";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Campo 'valor_pagado' agregado</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Campo 'valor_pagado' ya existe</p>";
    }
    
    // Verificar campo observacion
    $sql = "SHOW COLUMNS FROM info_factura LIKE 'observacion'";
    $stmt = $pdo->query($sql);
    $observacionExists = $stmt->fetch();
    
    if (!$observacionExists) {
        // Agregar campo observacion
        $sql = "ALTER TABLE info_factura ADD COLUMN observacion TEXT AFTER valor_pagado";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Campo 'observacion' agregado</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Campo 'observacion' ya existe</p>";
    }
    
    // Verificar campo created_at
    $sql = "SHOW COLUMNS FROM info_factura LIKE 'created_at'";
    $stmt = $pdo->query($sql);
    $createdAtExists = $stmt->fetch();
    
    if (!$createdAtExists) {
        // Agregar campo created_at
        $sql = "ALTER TABLE info_factura ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER observacion";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Campo 'created_at' agregado</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Campo 'created_at' ya existe</p>";
    }
    
    echo "<h3>🎉 ¡Tabla info_factura actualizada exitosamente!</h3>";
    
    // Mostrar estructura actualizada
    echo "<h3>📋 Estructura actual de la tabla info_factura:</h3>";
    $sql = "DESCRIBE info_factura";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll();
    
    echo "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Campo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Tipo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Nulo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Llave</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Por defecto</th>";
    echo "</tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar enlaces útiles
    echo "<hr>";
    echo "<h3>🔗 Enlaces Útiles:</h3>";
    echo "<p><a href='facturacion.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📊 Ir a Facturación</a></p>";
    echo "<p><a href='dashboard.html' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📈 Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Actualización de tabla completada - Sistema de Control GloboCity</em></p>";
?> 