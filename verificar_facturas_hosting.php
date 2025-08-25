<?php
// =====================================================
// VERIFICACIÓN DE FACTURAS EN HOSTING
// =====================================================

// Configuración de base de datos para hosting
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';
$charset = 'utf8mb4';

echo "<h2>🔍 Verificación de Facturas en Hosting</h2>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

try {
    // 1. Conexión a la base de datos
    echo "<h3>1. 🔌 Conexión a la base de datos</h3>";
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";
    
    // 2. Verificar tablas
    echo "<h3>2. 📋 Verificación de tablas</h3>";
    
    $tables = ['info_tributaria', 'info_factura', 'detalle_factura_sri', 'info_adicional_factura'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $pdo->query($sql);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Contar registros
            $sql = "SELECT COUNT(*) as total FROM $table";
            $stmt = $pdo->query($sql);
            $count = $stmt->fetch()['total'];
            
            $tableStatus[$table] = $count;
            echo "<p style='color: green;'>✅ Tabla $table existe - $count registros</p>";
        } else {
            $tableStatus[$table] = 0;
            echo "<p style='color: red;'>❌ Tabla $table NO existe</p>";
        }
    }
    
    // 3. Verificar datos específicos
    echo "<h3>3. 📊 Verificación de datos</h3>";
    
    $totalFacturas = $tableStatus['info_factura'];
    $totalTributaria = $tableStatus['info_tributaria'];
    
    if ($totalFacturas == 0) {
        echo "<p style='color: orange;'>⚠️ No hay facturas registradas en info_factura</p>";
        echo "<p><strong>Solución:</strong> <a href='create_test_data.php'>Crear datos de prueba</a></p>";
    } else {
        echo "<p style='color: green;'>✅ Hay $totalFacturas facturas registradas</p>";
        
        // Mostrar algunas facturas
        $sql = "SELECT id, razon_social_comprador, importe_total, estatus, info_tributaria_id FROM info_factura LIMIT 3";
        $stmt = $pdo->query($sql);
        $facturas = $stmt->fetchAll();
        
        echo "<h4>📋 Últimas facturas:</h4>";
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Cliente</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Total</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estatus</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>info_tributaria_id</th>";
        echo "</tr>";
        
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['id'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['razon_social_comprador'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['importe_total'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['estatus'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['info_tributaria_id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Test de JOIN
    echo "<h3>4. 🔗 Test de JOIN (info_factura + info_tributaria)</h3>";
    
    if ($totalFacturas > 0 && $totalTributaria > 0) {
        $sql = "SELECT 
            it.estab,
            it.pto_emi,
            it.secuencial,
            f.razon_social_comprador as cliente,
            f.importe_total as total,
            f.estatus
        FROM info_factura f 
        JOIN info_tributaria it ON f.info_tributaria_id = it.id
        ORDER BY f.created_at DESC
        LIMIT 5";
        
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll();
        
        if (count($resultados) > 0) {
            echo "<p style='color: green;'>✅ JOIN exitoso - Se encontraron " . count($resultados) . " registros</p>";
            
            echo "<h4>📋 Datos del JOIN:</h4>";
            echo "<table style='width: 100%; border-collapse: collapse;'>";
            echo "<tr style='background: #f8f9fa;'>";
            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estab</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Pto Emi</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Secuencial</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Cliente</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Total</th>";
            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estatus</th>";
            echo "</tr>";
            
            foreach ($resultados as $row) {
                echo "<tr>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estab'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['pto_emi'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['secuencial'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['cliente'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['total'] . "</td>";
                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estatus'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>❌ JOIN no devuelve resultados</p>";
            echo "<p><strong>Problema:</strong> Los datos no coinciden entre las tablas</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No se puede hacer JOIN porque faltan datos</p>";
    }
    
    // 5. Test de la API
    echo "<h3>5. 🌐 Test de la API get_facturas_simple.php</h3>";
    
    // Incluir directamente el archivo de la API
    ob_start();
    include 'api/get_facturas_simple.php';
    $apiResponse = ob_get_clean();
    
    if ($apiResponse) {
        echo "<p style='color: green;'>✅ API ejecutada correctamente</p>";
        
        // Decodificar JSON
        $data = json_decode($apiResponse, true);
        if ($data) {
            echo "<h4>📋 Respuesta de la API:</h4>";
            echo "<ul>";
            echo "<li><strong>Success:</strong> " . ($data['success'] ? 'true' : 'false') . "</li>";
            if (isset($data['data'])) {
                echo "<li><strong>Datos encontrados:</strong> " . count($data['data']) . "</li>";
            }
            if (isset($data['debug'])) {
                echo "<li><strong>Debug:</strong> " . json_encode($data['debug']) . "</li>";
            }
            echo "</ul>";
            
            // Mostrar datos si existen
            if (isset($data['data']) && count($data['data']) > 0) {
                echo "<h4>📋 Datos de la API:</h4>";
                echo "<table style='width: 100%; border-collapse: collapse;'>";
                echo "<tr style='background: #f8f9fa;'>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estab</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Pto Emi</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Secuencial</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Cliente</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Total</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estatus</th>";
                echo "</tr>";
                
                foreach ($data['data'] as $row) {
                    echo "<tr>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estab'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['pto_emi'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['secuencial'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['cliente'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['total'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estatus'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Error al ejecutar la API</p>";
    }
    
    // 6. Resumen y recomendaciones
    echo "<h3>6. 🎯 Resumen y Recomendaciones</h3>";
    
    if ($totalFacturas == 0) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
        echo "<h4>⚠️ No hay facturas registradas</h4>";
        echo "<p><strong>Solución:</strong> <a href='create_test_data.php'>Crear datos de prueba</a></p>";
        echo "</div>";
    } elseif (count($resultados) == 0) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ Problema en el JOIN</h4>";
        echo "<p>Hay datos en las tablas pero no coinciden en el JOIN</p>";
        echo "<p><strong>Solución:</strong> Verificar info_tributaria_id en info_factura</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ Todo parece estar bien</h4>";
        echo "<p>Los datos están correctos y la API funciona</p>";
        echo "<p><strong>Próximo paso:</strong> <a href='facturacion.html'>Ir a Facturación</a></p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔧 Enlaces útiles:</h3>";
echo "<ul>";
echo "<li><a href='facturacion.html'>📊 Ir a Facturación</a></li>";
echo "<li><a href='create_test_data.php'>🧪 Crear datos de prueba</a></li>";
echo "<li><a href='test_connection_simple.php'>🔍 Test de conexión simple</a></li>";
echo "<li><a href='debug_facturas.php'>🔍 Debug completo</a></li>";
echo "</ul>";
?> 