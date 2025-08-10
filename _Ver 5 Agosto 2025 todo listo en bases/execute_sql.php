<?php
// Script para ejecutar SQL directamente
require_once 'config.php';

echo "🔧 Ejecutando script SQL para crear tabla factura_detalles...\n\n";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        echo "❌ Error de conexión a la base de datos\n";
        exit;
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
    $resultado = $pdo->exec($sql);
    
    if ($resultado !== false) {
        echo "✅ Tabla factura_detalles creada exitosamente\n\n";
        
        // Verificar la estructura de la tabla
        echo "📊 Verificando estructura de la tabla:\n";
        echo str_repeat("-", 80) . "\n";
        
        $stmt = $pdo->query("DESCRIBE factura_detalles");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        
        echo "\n🔍 Verificando índices:\n";
        echo str_repeat("-", 50) . "\n";
        
        $stmt = $pdo->query("SHOW INDEX FROM factura_detalles");
        $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        printf("%-20s %-20s %-10s\n", "Índice", "Columna", "Tipo");
        echo str_repeat("-", 50) . "\n";
        
        foreach ($indices as $indice) {
            printf("%-20s %-20s %-10s\n", 
                $indice['Key_name'], 
                $indice['Column_name'], 
                $indice['Index_type']
            );
        }
        
        echo "\n✅ Verificación completada. La tabla está lista para usar.\n";
        
    } else {
        echo "❌ Error al crear la tabla factura_detalles\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 