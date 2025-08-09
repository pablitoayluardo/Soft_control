<?php
// Script para crear la tabla de detalles de facturas
require_once 'config.php';

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        echo "Error de conexión a la base de datos\n";
        exit;
    }
    
    // Crear tabla de detalles de facturas
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
    
    $resultado = $pdo->exec($sql);
    
    if ($resultado !== false) {
        echo "✅ Tabla factura_detalles creada exitosamente\n";
        
        // Verificar la estructura de la tabla
        $stmt = $pdo->query("DESCRIBE factura_detalles");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n📋 Estructura de la tabla factura_detalles:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-20s %-15s %-8s %-8s %-8s\n", "Campo", "Tipo", "Nulo", "Llave", "Default");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($columnas as $columna) {
            printf("%-20s %-15s %-8s %-8s %-8s\n", 
                $columna['Field'], 
                $columna['Type'], 
                $columna['Null'], 
                $columna['Key'], 
                $columna['Default'] ?? 'NULL'
            );
        }
        
        // Verificar índices
        $stmt = $pdo->query("SHOW INDEX FROM factura_detalles");
        $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n🔍 Índices de la tabla factura_detalles:\n";
        echo str_repeat("-", 50) . "\n";
        printf("%-20s %-20s %-10s\n", "Índice", "Columna", "Tipo");
        echo str_repeat("-", 50) . "\n";
        
        foreach ($indices as $indice) {
            printf("%-20s %-20s %-10s\n", 
                $indice['Key_name'], 
                $indice['Column_name'], 
                $indice['Index_type']
            );
        }
        
    } else {
        echo "❌ Error al crear la tabla factura_detalles\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 