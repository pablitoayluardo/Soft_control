<?php
// Script para ejecutar SQL usando funciones básicas de MySQL
echo "🔧 Ejecutando script SQL para crear tabla factura_detalles...\n\n";

// Configuración de base de datos
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';

try {
    // Crear conexión usando funciones básicas
    $connection = mysql_connect($host, $username, $password);
    if (!$connection) {
        throw new Exception("Error de conexión: " . mysql_error());
    }
    
    // Seleccionar base de datos
    if (!mysql_select_db($dbname, $connection)) {
        throw new Exception("Error seleccionando base de datos: " . mysql_error());
    }
    
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
    $resultado = mysql_query($sql, $connection);
    
    if ($resultado) {
        echo "✅ Tabla factura_detalles creada exitosamente\n\n";
        
        // Verificar la estructura de la tabla
        echo "📊 Verificando estructura de la tabla:\n";
        echo str_repeat("-", 80) . "\n";
        
        $result = mysql_query("DESCRIBE factura_detalles", $connection);
        if ($result) {
            printf("%-20s %-15s %-8s %-8s %-8s\n", "Campo", "Tipo", "Nulo", "Llave", "Default");
            echo str_repeat("-", 80) . "\n";
            
            while ($row = mysql_fetch_assoc($result)) {
                printf("%-20s %-15s %-8s %-8s %-8s\n", 
                    $row['Field'], 
                    $row['Type'], 
                    $row['Null'], 
                    $row['Key'], 
                    $row['Default'] ?? 'NULL'
                );
            }
        }
        
        echo "\n✅ Verificación completada. La tabla está lista para usar.\n";
        
    } else {
        echo "❌ Error al crear la tabla factura_detalles: " . mysql_error($connection) . "\n";
    }
    
    // Cerrar conexión
    mysql_close($connection);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 