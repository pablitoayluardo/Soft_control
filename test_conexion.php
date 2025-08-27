<?php
echo "🔍 PROBANDO CONEXIÓN A BASE DE DATOS\n";
echo "=====================================\n";

require_once 'config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    echo "Conectando a: " . DB_HOST . "/" . DB_NAME . "\n";
    
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
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
