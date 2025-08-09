<?php
// Archivo de prueba para verificar validación de duplicados
require_once 'config.php';

echo "<h2>Prueba de Validación de Duplicados</h2>";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        echo "<p style='color: red;'>Error de conexión a la base de datos</p>";
        exit;
    }
    
    // Verificar estructura de la tabla
    echo "<h3>Estructura de la tabla facturas:</h3>";
    $stmt = $pdo->query("DESCRIBE facturas");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th></tr>";
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>{$columna['Field']}</td>";
        echo "<td>{$columna['Type']}</td>";
        echo "<td>{$columna['Null']}</td>";
        echo "<td>{$columna['Key']}</td>";
        echo "<td>{$columna['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar facturas existentes
    echo "<h3>Facturas registradas actualmente:</h3>";
    $stmt = $pdo->query("SELECT numero_autorizacion, cliente, total, fecha_registro FROM facturas ORDER BY fecha_registro DESC LIMIT 10");
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($facturas)) {
        echo "<p style='color: orange;'>No hay facturas registradas en el sistema.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Número Autorización</th><th>Cliente</th><th>Total</th><th>Fecha Registro</th></tr>";
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td>{$factura['numero_autorizacion']}</td>";
            echo "<td>{$factura['cliente']}</td>";
            echo "<td>\${$factura['total']}</td>";
            echo "<td>{$factura['fecha_registro']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Probar consulta de duplicados
    echo "<h3>Prueba de consulta de duplicados:</h3>";
    $numeroPrueba = "1234567890";
    
    $stmt = $pdo->prepare("SELECT id, numero_autorizacion, cliente, total FROM facturas WHERE numero_autorizacion = ?");
    $stmt->execute([$numeroPrueba]);
    $facturaExistente = $stmt->fetch();
    
    if ($facturaExistente) {
        echo "<p style='color: red;'>❌ Ya existe una factura con número de autorización: {$facturaExistente['numero_autorizacion']}</p>";
        echo "<p>Cliente: {$facturaExistente['cliente']}</p>";
        echo "<p>Total: \${$facturaExistente['total']}</p>";
    } else {
        echo "<p style='color: green;'>✅ No existe factura con número de autorización: {$numeroPrueba}</p>";
    }
    
    echo "<h3>Índices de la tabla:</h3>";
    $stmt = $pdo->query("SHOW INDEX FROM facturas");
    $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($indices)) {
        echo "<p style='color: orange;'>No hay índices definidos en la tabla.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Índice</th><th>Columna</th><th>Tipo</th></tr>";
        foreach ($indices as $indice) {
            echo "<tr>";
            echo "<td>{$indice['Key_name']}</td>";
            echo "<td>{$indice['Column_name']}</td>";
            echo "<td>{$indice['Index_type']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 10px 0; }
    th, td { padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style> 