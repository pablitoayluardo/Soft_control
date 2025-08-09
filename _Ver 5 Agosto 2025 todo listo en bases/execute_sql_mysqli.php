<?php
// Script para ejecutar SQL usando mysqli
echo "🔧 Ejecutando script SQL para crear tabla factura_detalles...\n\n";

// Configuración de base de datos (copiada de config.php)
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';
$charset = 'utf8mb4';

try {
    // Crear conexión mysqli
    $mysqli = new mysqli($host, $username, $password, $dbname);
    
    // Verificar conexión
    if ($mysqli->connect_error) {
        throw new Exception("Error de conexión: " . $mysqli->connect_error);
    }
    
    // Establecer charset
    $mysqli->set_charset($charset);
    
    echo "✅ Conexión a la base de datos establecida\n\n";
    
    // SQL para crear la tabla de detalles
    $sql = "
    CREATE TABLE IF NOT EXISTS factura_detalles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        factura_id INT NOT NULL,
        codigo_principal VARCHAR(50) NOT NULL,
        descripcion TEXT NOT NULL,
        cantidad DECIMAL(10,2) NOT NULL DEFAULT 0,
        precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
        descuento DECIMAL(10,2) NOT NULL DEFAULT 0,
        precio_total_sin_impuesto DECIMAL(10,2) NOT NULL DEFAULT 0,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE CASCADE,
        INDEX idx_factura_id (factura_id),
        INDEX idx_codigo_principal (codigo_principal)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    echo "📋 Creando tabla factura_detalles...\n";
    $resultado = $mysqli->query($sql);
    
    if ($resultado) {
        echo "✅ Tabla factura_detalles creada exitosamente\n\n";
        
        // Verificar la estructura de la tabla
        echo "📊 Verificando estructura de la tabla:\n";
        echo str_repeat("-", 80) . "\n";
        
        $result = $mysqli->query("DESCRIBE factura_detalles");
        if ($result) {
            printf("%-20s %-15s %-8s %-8s %-8s\n", "Campo", "Tipo", "Nulo", "Llave", "Default");
            echo str_repeat("-", 80) . "\n";
            
            while ($row = $result->fetch_assoc()) {
                printf("%-20s %-15s %-8s %-8s %-8s\n", 
                    $row['Field'], 
                    $row['Type'], 
                    $row['Null'], 
                    $row['Key'], 
                    $row['Default'] ?? 'NULL'
                );
            }
        }
        
        echo "\n🔍 Verificando índices:\n";
        echo str_repeat("-", 50) . "\n";
        
        $result = $mysqli->query("SHOW INDEX FROM factura_detalles");
        if ($result) {
            printf("%-20s %-20s %-10s\n", "Índice", "Columna", "Tipo");
            echo str_repeat("-", 50) . "\n";
            
            while ($row = $result->fetch_assoc()) {
                printf("%-20s %-20s %-10s\n", 
                    $row['Key_name'], 
                    $row['Column_name'], 
                    $row['Index_type']
                );
            }
        }
        
        echo "\n✅ Verificación completada. La tabla está lista para usar.\n";
        
        // Verificar si la tabla facturas existe
        echo "\n🔍 Verificando tabla facturas:\n";
        $result = $mysqli->query("SHOW TABLES LIKE 'facturas'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Tabla facturas existe\n";
            
            // Contar registros en facturas
            $result = $mysqli->query("SELECT COUNT(*) as total FROM facturas");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "📊 Total de facturas registradas: " . $row['total'] . "\n";
            }
        } else {
            echo "⚠️ Tabla facturas no existe. Debes crearla primero.\n";
        }
        
    } else {
        echo "❌ Error al crear la tabla factura_detalles: " . $mysqli->error . "\n";
    }
    
    // Cerrar conexión
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 