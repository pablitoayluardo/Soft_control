<?php
// =====================================================
// SETUP COMPLETO DE TABLAS DE FACTURACIÓN
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔧 Setup Completo de Tablas de Facturación</h2>";

try {
    $pdo = getDBConnection();

    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";

    // 1. Crear tabla info_tributaria si no existe
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
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla info_tributaria verificada/creada</p>";

    // Verificar y agregar campos faltantes en info_tributaria
    $columns_tributaria = ['numero_autorizacion', 'fecha_autorizacion', 'created_at'];
    
    foreach ($columns_tributaria as $column) {
        $sql = "SHOW COLUMNS FROM info_tributaria LIKE '$column'";
        $stmt = $pdo->query($sql);
        $exists = $stmt->fetch();
        
        if (!$exists) {
            switch ($column) {
                case 'numero_autorizacion':
                    $sql = "ALTER TABLE info_tributaria ADD COLUMN numero_autorizacion VARCHAR(100) AFTER dir_matriz";
                    break;
                case 'fecha_autorizacion':
                    $sql = "ALTER TABLE info_tributaria ADD COLUMN fecha_autorizacion DATE AFTER numero_autorizacion";
                    break;
                case 'created_at':
                    $sql = "ALTER TABLE info_tributaria ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER fecha_autorizacion";
                    break;
            }
            
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Campo '$column' agregado a info_tributaria</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Campo '$column' ya existe en info_tributaria</p>";
        }
    }

    // 2. Crear tabla info_factura si no existe
    $sql = "CREATE TABLE IF NOT EXISTS info_factura (
        id INT AUTO_INCREMENT PRIMARY KEY,
        info_tributaria_id INT NOT NULL,
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
        FOREIGN KEY (info_tributaria_id) REFERENCES info_tributaria(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla info_factura verificada/creada</p>";

    // 3. Crear tabla detalle_factura_sri si no existe
    $sql = "CREATE TABLE IF NOT EXISTS detalle_factura_sri (
        id INT AUTO_INCREMENT PRIMARY KEY,
        info_factura_id INT NOT NULL,
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
        FOREIGN KEY (info_factura_id) REFERENCES info_factura(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla detalle_factura_sri verificada/creada</p>";

    // 4. Crear tabla info_adicional_factura si no existe
    $sql = "CREATE TABLE IF NOT EXISTS info_adicional_factura (
        id INT AUTO_INCREMENT PRIMARY KEY,
        info_factura_id INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        valor TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (info_factura_id) REFERENCES info_factura(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla info_adicional_factura verificada/creada</p>";

    // 5. Verificar y agregar campos faltantes en info_factura
    $columns = ['estatus', 'retencion', 'valor_pagado', 'observacion', 'created_at'];
    
    foreach ($columns as $column) {
        $sql = "SHOW COLUMNS FROM info_factura LIKE '$column'";
        $stmt = $pdo->query($sql);
        $exists = $stmt->fetch();
        
        if (!$exists) {
            switch ($column) {
                case 'estatus':
                    $sql = "ALTER TABLE info_factura ADD COLUMN estatus VARCHAR(50) DEFAULT 'PENDIENTE' AFTER forma_pago";
                    break;
                case 'retencion':
                    $sql = "ALTER TABLE info_factura ADD COLUMN retencion DECIMAL(10,2) DEFAULT 0.00 AFTER estatus";
                    break;
                case 'valor_pagado':
                    $sql = "ALTER TABLE info_factura ADD COLUMN valor_pagado DECIMAL(10,2) DEFAULT 0.00 AFTER retencion";
                    break;
                case 'observacion':
                    $sql = "ALTER TABLE info_factura ADD COLUMN observacion TEXT AFTER valor_pagado";
                    break;
                case 'created_at':
                    $sql = "ALTER TABLE info_factura ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER observacion";
                    break;
            }
            
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Campo '$column' agregado a info_factura</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Campo '$column' ya existe en info_factura</p>";
        }
    }

    // 6. Verificar datos existentes
    $sql = "SELECT COUNT(*) as total FROM info_tributaria";
    $stmt = $pdo->query($sql);
    $totalTributaria = $stmt->fetch()['total'];

    $sql = "SELECT COUNT(*) as total FROM info_factura";
    $stmt = $pdo->query($sql);
    $totalFactura = $stmt->fetch()['total'];

    echo "<h3>📊 Estado de las Tablas:</h3>";
    echo "<p>📋 info_tributaria: $totalTributaria registros</p>";
    echo "<p>📋 info_factura: $totalFactura registros</p>";

    if ($totalTributaria == 0 || $totalFactura == 0) {
        echo "<p style='color: orange;'>⚠️ Las tablas están vacías. Para probar la funcionalidad:</p>";
        echo "<ol>";
        echo "<li>Ejecuta <code>load_factura_sri.php</code> para cargar el XML de ejemplo</li>";
        echo "<li>O usa la función de subir facturas desde facturacion.html</li>";
        echo "</ol>";
    } else {
        // Mostrar algunos datos de ejemplo
        $sql = "SELECT 
            it.id,
            it.secuencial,
            it.clave_acceso,
            inf_factura.fecha_emision,
            inf_factura.razon_social_comprador as cliente,
            inf_factura.importe_total,
            inf_factura.estatus
        FROM info_tributaria it
        JOIN info_factura inf_factura ON it.id = inf_factura.info_tributaria_id
        ORDER BY inf_factura.fecha_emision DESC
        LIMIT 3";

        $stmt = $pdo->query($sql);
        $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($facturas) > 0) {
            echo "<h3>📄 Datos de Ejemplo:</h3>";
            echo "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>";
            echo "<tr style='background: #007bff; color: white;'>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>ID</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Secuencial</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Clave Acceso</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Fecha</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Cliente</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Total</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Estatus</th>";
            echo "</tr>";

            foreach ($facturas as $factura) {
                echo "<tr>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['id'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['secuencial'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['clave_acceso'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['fecha_emision'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['cliente'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['importe_total'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['estatus'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    echo "<h3>🎉 ¡Setup completado exitosamente!</h3>";

    // Enlaces útiles
    echo "<hr>";
    echo "<h3>🔗 Enlaces Útiles:</h3>";
    echo "<p><a href='facturacion.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📊 Ir a Facturación</a></p>";
    echo "<p><a href='test_api_simple.html' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🧪 Test API</a></p>";
    echo "<p><a href='dashboard.html' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📈 Dashboard</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Setup completado - Sistema de Control GloboCity</em></p>";
?> 