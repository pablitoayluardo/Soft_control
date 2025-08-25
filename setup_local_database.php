<?php
// =====================================================
// SETUP DE BASE DE DATOS LOCAL
// =====================================================

echo "<h2>🔧 Setup de Base de Datos Local</h2>";

try {
    // Configuración local para XAMPP
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $charset = 'utf8mb4';

    // Conectar sin especificar base de datos
    $dsn = "mysql:host=$host;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    echo "<p style='color: green;'>✅ <strong>Conexión exitosa a MySQL</strong></p>";

    // Crear base de datos si no existe
    $dbname = 'soft_control';
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Base de datos '$dbname' verificada/creada</p>";

    // Conectar a la base de datos específica
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    echo "<p style='color: green;'>✅ <strong>Conectado a base de datos '$dbname'</strong></p>";

    // Crear tabla info_tributaria
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
    echo "<p style='color: green;'>✅ Tabla info_tributaria verificada/creada</p>";

    // Verificar y agregar columnas faltantes
    $requiredColumns = ['numero_autorizacion', 'fecha_autorizacion'];
    
    foreach ($requiredColumns as $column) {
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
            }
            
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Columna '$column' agregada a info_tributaria</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Columna '$column' ya existe en info_tributaria</p>";
        }
    }

    // Crear tabla info_factura
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla info_factura verificada/creada</p>";

    // Crear tabla detalle_factura_sri
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla detalle_factura_sri verificada/creada</p>";

    // Mostrar estructura final de info_tributaria
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

    echo "<p style='color: green;'>✅ <strong>Setup completado exitosamente</strong></p>";
    echo "<p>🎯 Ahora puedes registrar facturas sin errores</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>💡 Asegúrate de que:</p>";
    echo "<ul>";
    echo "<li>XAMPP esté ejecutándose</li>";
    echo "<li>MySQL esté activo</li>";
    echo "<li>El usuario 'root' tenga permisos</li>";
    echo "</ul>";
}
?> 