<?php
// =====================================================
// FIX TABLE STRUCTURE - CLAVES PRIMARIAS Y FORÁNEAS
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔧 Fix Table Structure - Claves Primarias y Foráneas</h2>";

try {
    $pdo = getDBConnection();

    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    echo "<p style='color: green;'>✅ <strong>Conexión exitosa al hosting</strong></p>";

    // Iniciar transacción
    $pdo->beginTransaction();

    // 1. Recrear tabla info_tributaria con estructura correcta
    echo "<h3>🔧 Recreando tabla info_tributaria:</h3>";
    
    // Primero eliminar la tabla si existe (cuidado con los datos)
    $pdo->exec("DROP TABLE IF EXISTS impuestos_detalle");
    $pdo->exec("DROP TABLE IF EXISTS total_con_impuestos");
    $pdo->exec("DROP TABLE IF EXISTS pagos");
    $pdo->exec("DROP TABLE IF EXISTS info_adicional_factura");
    $pdo->exec("DROP TABLE IF EXISTS detalle_factura_sri");
    $pdo->exec("DROP TABLE IF EXISTS info_factura");
    $pdo->exec("DROP TABLE IF EXISTS info_tributaria");
    
    // Crear tabla info_tributaria con estructura correcta
    $sql = "CREATE TABLE info_tributaria (
        id_info_tributaria INT AUTO_INCREMENT PRIMARY KEY,
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
        fecha_autorizacion DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla info_tributaria creada con id_info_tributaria como PK</p>";

    // 2. Crear tabla info_factura con estructura correcta
    echo "<h3>🔧 Creando tabla info_factura:</h3>";
    
    $sql = "CREATE TABLE info_factura (
        id_info_factura INT AUTO_INCREMENT PRIMARY KEY,
        id_info_tributaria INT NOT NULL,
        fecha_emision DATE NOT NULL,
        dir_establecimiento VARCHAR(255),
        obligado_contabilidad VARCHAR(10),
        tipo_identificacion_comprador VARCHAR(10),
        razon_social_comprador VARCHAR(255),
        identificacion_comprador VARCHAR(13),
        direccion_comprador VARCHAR(255),
        total_sin_impuestos DECIMAL(10,2),
        total_descuento DECIMAL(10,2),
        importe_total DECIMAL(10,2) NOT NULL,
        moneda VARCHAR(10),
        forma_pago VARCHAR(50),
        estatus VARCHAR(50) DEFAULT 'PENDIENTE',
        retencion DECIMAL(10,2) DEFAULT 0.00,
        valor_pagado DECIMAL(10,2) DEFAULT 0.00,
        observacion TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_info_tributaria) REFERENCES info_tributaria(id_info_tributaria) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla info_factura creada con id_info_factura como PK y FK a info_tributaria</p>";

    // 3. Crear tabla detalle_factura_sri con estructura correcta
    echo "<h3>🔧 Creando tabla detalle_factura_sri:</h3>";
    
    $sql = "CREATE TABLE detalle_factura_sri (
        id_detalle INT AUTO_INCREMENT PRIMARY KEY,
        id_info_factura INT NOT NULL,
        codigo_principal VARCHAR(50),
        descripcion TEXT NOT NULL,
        cantidad DECIMAL(10,2) NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        descuento DECIMAL(10,2) DEFAULT 0.00,
        precio_total_sin_impuesto DECIMAL(10,2) NOT NULL,
        codigo_impuesto VARCHAR(10),
        codigo_porcentaje VARCHAR(10),
        tarifa DECIMAL(5,2),
        base_imponible DECIMAL(10,2),
        valor_impuesto DECIMAL(10,2),
        informacion_adicional TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_info_factura) REFERENCES info_factura(id_info_factura) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla detalle_factura_sri creada con id_detalle como PK y FK a info_factura</p>";

    // 4. Crear tabla info_adicional_factura con estructura correcta
    echo "<h3>🔧 Creando tabla info_adicional_factura:</h3>";
    
    $sql = "CREATE TABLE info_adicional_factura (
        id_info_adicional INT AUTO_INCREMENT PRIMARY KEY,
        id_info_factura INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        valor TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_info_factura) REFERENCES info_factura(id_info_factura) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla info_adicional_factura creada con id_info_adicional como PK y FK a info_factura</p>";

    // 5. Crear tabla pagos con estructura correcta
    echo "<h3>🔧 Creando tabla pagos:</h3>";
    
    $sql = "CREATE TABLE pagos (
        id_pago INT AUTO_INCREMENT PRIMARY KEY,
        id_info_factura INT NOT NULL,
        formaPago VARCHAR(50) NOT NULL,
        total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_info_factura) REFERENCES info_factura(id_info_factura) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla pagos creada con id_pago como PK y FK a info_factura</p>";

    // 6. Crear tabla total_con_impuestos con estructura correcta
    echo "<h3>🔧 Creando tabla total_con_impuestos:</h3>";
    
    $sql = "CREATE TABLE total_con_impuestos (
        id_total_impuesto INT AUTO_INCREMENT PRIMARY KEY,
        id_info_factura INT NOT NULL,
        codigo VARCHAR(10) NOT NULL,
        codigoPorcentaje VARCHAR(10) NOT NULL,
        baseImponible DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_info_factura) REFERENCES info_factura(id_info_factura) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla total_con_impuestos creada con id_total_impuesto como PK y FK a info_factura</p>";

    // 7. Crear tabla impuestos_detalle con estructura correcta
    echo "<h3>🔧 Creando tabla impuestos_detalle:</h3>";
    
    $sql = "CREATE TABLE impuestos_detalle (
        id_impuesto_detalle INT AUTO_INCREMENT PRIMARY KEY,
        id_detalle INT NOT NULL,
        codigo VARCHAR(10) NOT NULL,
        codigoPorcentaje VARCHAR(10) NOT NULL,
        tarifa DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        baseImponible DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_detalle) REFERENCES detalle_factura_sri(id_detalle) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla impuestos_detalle creada con id_impuesto_detalle como PK y FK a detalle_factura_sri</p>";

    // Confirmar transacción
    $pdo->commit();
    echo "<p style='color: green;'>✅ <strong>Transacción completada exitosamente</strong></p>";

    // Mostrar estructura final
    echo "<h3>📋 Estructura final de las tablas:</h3>";
    
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

    // Mostrar relaciones
    echo "<h3>🔗 Relaciones establecidas:</h3>";
    echo "<ul>";
    echo "<li><strong>info_tributaria</strong> (1) → <strong>info_factura</strong> (N) - Una info_tributaria puede tener muchas facturas</li>";
    echo "<li><strong>info_factura</strong> (1) → <strong>detalle_factura_sri</strong> (N) - Una factura puede tener muchos detalles</li>";
    echo "<li><strong>info_factura</strong> (1) → <strong>info_adicional_factura</strong> (N) - Una factura puede tener mucha información adicional</li>";
    echo "<li><strong>info_factura</strong> (1) → <strong>pagos</strong> (N) - Una factura puede tener múltiples formas de pago</li>";
    echo "<li><strong>info_factura</strong> (1) → <strong>total_con_impuestos</strong> (N) - Una factura puede tener varios impuestos aplicados</li>";
    echo "<li><strong>detalle_factura_sri</strong> (1) → <strong>impuestos_detalle</strong> (N) - Cada detalle puede tener sus propios impuestos</li>";
    echo "</ul>";

    echo "<p style='color: green;'>✅ <strong>Estructura de tablas actualizada correctamente</strong></p>";
    echo "<p>🎯 Ahora puedes registrar facturas con las relaciones correctas</p>";

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 