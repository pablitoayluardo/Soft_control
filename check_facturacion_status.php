<?php
// =====================================================
// VERIFICAR ESTADO DE TABLAS DE FACTURACIÓN
// =====================================================

// Configuración directa de base de datos
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';
$charset = 'utf8mb4';

echo "<h2>🔍 Verificación de Estado de Facturación</h2>";

try {
    // Conexión directa a la base de datos
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
    
    // Verificar todas las tablas del sistema
    $tables = [
        'usuarios',
        'productos', 
        'clientes',
        'facturas',
        'detalle_facturas',
        'pagos',
        'gastos',
        'movimientos_inventario',
        'configuraciones',
        'actividad_log',
        'info_tributaria',
        'info_factura',
        'detalle_factura_sri',
        'info_adicional_factura'
    ];
    
    echo "<h3>📋 Estado de Todas las Tablas:</h3>";
    echo "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Tabla</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Existe</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Registros</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Estado</th>";
    echo "</tr>";
    
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $pdo->query($sql);
        $exists = $stmt->fetch();
        
        if ($exists) {
            $sql = "SELECT COUNT(*) as total FROM $table";
            $stmt = $pdo->query($sql);
            $count = $stmt->fetch()['total'];
            
            $status = $count > 0 ? '✅ Con datos' : '⚠️ Vacía';
            $statusColor = $count > 0 ? 'green' : 'orange';
            
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>$table</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: green;'>✅ Sí</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>$count</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: $statusColor;'>$status</td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>$table</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: red;'>❌ No</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>-</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: red;'>❌ No existe</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    // Verificar estructura de info_factura
    echo "<h3>📋 Estructura de info_factura:</h3>";
    $sql = "DESCRIBE info_factura";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll();
    
    if (count($columns) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Campo</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Tipo</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Nulo</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Llave</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Por defecto</th>";
        echo "</tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Field'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Type'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Null'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Key'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $column['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ La tabla info_factura no existe</p>";
    }
    
    // Verificar datos de facturas
    echo "<h3>📄 Datos de Facturas:</h3>";
    $sql = "SELECT 
        it.id,
        it.secuencial,
        it.clave_acceso,
        inf_factura.fecha_emision,
        inf_factura.razon_social_comprador as cliente,
        inf_factura.importe_total,
        inf_factura.estatus
    FROM info_tributaria it
    JOIN info_factura inf_factura ON it.id = inf_factura.info_tributaria_id
    ORDER BY inf_factura.fecha_emision DESC
    LIMIT 5";
    
    $stmt = $pdo->query($sql);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($facturas) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Secuencial</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Clave Acceso</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Fecha</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Cliente</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Total</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Estatus</th>";
        echo "</tr>";
        
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['id'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['secuencial'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['clave_acceso'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['fecha_emision'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['cliente'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['importe_total'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['estatus'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay facturas en la base de datos</p>";
    }
    
    // Recomendaciones
    echo "<h3>💡 Recomendaciones:</h3>";
    echo "<ul>";
    echo "<li>Si las tablas de facturación no existen, ejecuta <code>setup_facturacion_complete.php</code></li>";
    echo "<li>Si las tablas están vacías, ejecuta <code>load_factura_sri.php</code> para cargar datos de ejemplo</li>";
    echo "<li>Si hay problemas con la API, usa <code>api/get_facturas_simple.php</code> que no depende de config.php</li>";
    echo "</ul>";
    
    // Enlaces útiles
    echo "<hr>";
    echo "<h3>🔗 Enlaces Útiles:</h3>";
    echo "<p><a href='setup_facturacion_complete.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🔧 Setup Completo</a></p>";
    echo "<p><a href='test_api_simple.html' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🧪 Test API</a></p>";
    echo "<p><a href='facturacion.html' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📊 Facturación</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Verificación completada - Sistema de Control GloboCity</em></p>";
?> 