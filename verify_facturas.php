<?php
/**
 * Script para verificar facturas en las tablas
 */

echo "🔍 Verificando facturas en las tablas...\n\n";

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Conexión exitosa a la base de datos\n\n";
    
    // Verificar tablas existentes
    echo "📋 Verificando tablas existentes:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "- $tableName\n";
    }
    
    echo "\n";
    
    // Verificar tabla facturas
    echo "📊 Verificando tabla 'facturas':\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM facturas");
    $count = $stmt->fetch();
    echo "Total de registros en 'facturas': " . $count['total'] . "\n";
    
    if ($count['total'] > 0) {
        echo "\n📄 Últimas 5 facturas en tabla 'facturas':\n";
        $stmt = $pdo->query("SELECT id, numero_factura, cliente, total, fecha_emision, fecha_registro FROM facturas ORDER BY fecha_registro DESC LIMIT 5");
        $facturas = $stmt->fetchAll();
        
        foreach ($facturas as $factura) {
            echo "- ID: " . $factura['id'] . 
                 " | Factura: " . $factura['numero_factura'] . 
                 " | Cliente: " . $factura['cliente'] . 
                 " | Total: " . $factura['total'] . 
                 " | Fecha: " . $factura['fecha_emision'] . 
                 " | Registro: " . $factura['fecha_registro'] . "\n";
        }
    }
    
    echo "\n";
    
    // Verificar tabla info_tributaria
    echo "📊 Verificando tabla 'info_tributaria':\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_tributaria");
    $count = $stmt->fetch();
    echo "Total de registros en 'info_tributaria': " . $count['total'] . "\n";
    
    if ($count['total'] > 0) {
        echo "\n📄 Últimas 5 facturas en tabla 'info_tributaria':\n";
        $stmt = $pdo->query("SELECT id, secuencial, clave_acceso, ruc FROM info_tributaria ORDER BY id DESC LIMIT 5");
        $tributarias = $stmt->fetchAll();
        
        foreach ($tributarias as $tributaria) {
            echo "- ID: " . $tributaria['id'] . 
                 " | Secuencial: " . $tributaria['secuencial'] . 
                 " | Clave: " . $tributaria['clave_acceso'] . 
                 " | RUC: " . $tributaria['ruc'] . "\n";
        }
    }
    
    echo "\n";
    
    // Verificar tabla info_factura
    echo "📊 Verificando tabla 'info_factura':\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_factura");
    $count = $stmt->fetch();
    echo "Total de registros en 'info_factura': " . $count['total'] . "\n";
    
    if ($count['total'] > 0) {
        echo "\n📄 Últimas 5 facturas en tabla 'info_factura':\n";
        $stmt = $pdo->query("SELECT info_tributaria_id, fecha_emision, razon_social_comprador, importe_total FROM info_factura ORDER BY fecha_emision DESC LIMIT 5");
        $facturas = $stmt->fetchAll();
        
        foreach ($facturas as $factura) {
            echo "- Tributaria ID: " . $factura['info_tributaria_id'] . 
                 " | Fecha: " . $factura['fecha_emision'] . 
                 " | Cliente: " . $factura['razon_social_comprador'] . 
                 " | Total: " . $factura['importe_total'] . "\n";
        }
    }
    
    echo "\n";
    
    // Verificar tabla factura_detalles
    echo "📊 Verificando tabla 'factura_detalles':\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM factura_detalles");
    $count = $stmt->fetch();
    echo "Total de registros en 'factura_detalles': " . $count['total'] . "\n";
    
    if ($count['total'] > 0) {
        echo "\n📄 Últimos 5 detalles en tabla 'factura_detalles':\n";
        $stmt = $pdo->query("SELECT factura_id, codigo_principal, descripcion, cantidad, precio_unitario FROM factura_detalles ORDER BY id DESC LIMIT 5");
        $detalles = $stmt->fetchAll();
        
        foreach ($detalles as $detalle) {
            echo "- Factura ID: " . $detalle['factura_id'] . 
                 " | Código: " . $detalle['codigo_principal'] . 
                 " | Descripción: " . $detalle['descripcion'] . 
                 " | Cantidad: " . $detalle['cantidad'] . 
                 " | Precio: " . $detalle['precio_unitario'] . "\n";
        }
    }
    
    echo "\n✅ Verificación completada.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 