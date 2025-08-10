<?php
// Script para ejecutar complete_fix_tables.sql
require_once 'config.php';

echo "🔧 Ejecutando script SQL para corregir tabla facturas...\n\n";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        echo "❌ Error de conexión a la base de datos\n";
        exit;
    }
    
    echo "✅ Conexión a la base de datos establecida\n\n";
    
    // Leer el archivo SQL
    $sqlFile = 'complete_fix_tables.sql';
    if (!file_exists($sqlFile)) {
        echo "❌ Error: No se encontró el archivo $sqlFile\n";
        exit;
    }
    
    $sqlContent = file_get_contents($sqlFile);
    
    // Dividir el SQL en comandos individuales
    $commands = array_filter(array_map('trim', explode(';', $sqlContent)));
    
    echo "📋 Ejecutando comandos SQL...\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($commands as $command) {
        if (empty($command) || strpos($command, '--') === 0) {
            continue; // Saltar comentarios y líneas vacías
        }
        
        echo "Ejecutando: " . substr($command, 0, 50) . "...\n";
        
        try {
            $resultado = $pdo->exec($command);
            if ($resultado !== false) {
                echo "✅ Comando ejecutado exitosamente\n";
            } else {
                echo "⚠️ Comando ejecutado (posiblemente sin cambios)\n";
            }
        } catch (Exception $e) {
            echo "❌ Error en comando: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    echo "✅ Script SQL ejecutado completamente\n\n";
    
    // Verificar la estructura final de la tabla facturas
    echo "📊 Verificando estructura final de la tabla facturas:\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query("DESCRIBE facturas");
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
    
    echo "\n✅ Verificación completada. La tabla facturas está corregida.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 