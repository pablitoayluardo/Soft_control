<?php
echo "🔍 PROBANDO CONEXIÓN SIMPLE\n";
echo "===========================\n";

// Verificar si config.php existe
if (!file_exists('config.php')) {
    echo "❌ config.php no existe\n";
    exit;
}

echo "✅ config.php existe\n";

// Incluir configuración
require_once 'config.php';

echo "✅ config.php cargado\n";

// Verificar si las constantes están definidas
if (!defined('DB_HOST')) {
    echo "❌ DB_HOST no está definido\n";
    exit;
}

if (!defined('DB_NAME')) {
    echo "❌ DB_NAME no está definido\n";
    exit;
}

if (!defined('DB_USER')) {
    echo "❌ DB_USER no está definido\n";
    exit;
}

if (!defined('DB_PASS')) {
    echo "❌ DB_PASS no está definido\n";
    exit;
}

echo "✅ Todas las constantes están definidas\n";
echo "Host: " . DB_HOST . "\n";
echo "DB: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    echo "Conectando a: $dsn\n";
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Conexión exitosa\n";
    
    // Verificar tablas
    $tablas = ['info_factura', 'pagos', 'logs_actividad'];
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla $tabla existe\n";
        } else {
            echo "❌ Tabla $tabla NO existe\n";
        }
    }
    
    // Contar facturas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_factura");
    $total = $stmt->fetchColumn();
    echo "📊 Total facturas: $total\n";
    
    // Contar pagos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pagos");
    $total = $stmt->fetchColumn();
    echo "💰 Total pagos: $total\n";
    
    // Verificar estatus de facturas
    $stmt = $pdo->query("SELECT estatus, COUNT(*) as cantidad FROM info_factura GROUP BY estatus");
    $estados = $stmt->fetchAll();
    
    echo "📈 Estados de facturas:\n";
    foreach ($estados as $estado) {
        echo "   {$estado['estatus']}: {$estado['cantidad']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
