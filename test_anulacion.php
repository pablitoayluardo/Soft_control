<?php
// =====================================================
// ARCHIVO DE PRUEBA PARA VERIFICAR FUNCIONAMIENTO DE ANULACIÓN
// =====================================================

echo "<h1>🔍 Prueba de Funcionalidad de Anulación</h1>";

// Probar conexión a la base de datos
echo "<h2>1. Verificando conexión a la base de datos...</h2>";
try {
    require_once 'config.php';
    
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Configuración de base de datos incompleta');
    }
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar si las tablas existen
echo "<h2>2. Verificando estructura de tablas...</h2>";
try {
    $sql = "SHOW TABLES LIKE 'info_factura'";
    $stmt = $pdo->query($sql);
    $infoFacturaExists = $stmt->fetch();
    
    if (!$infoFacturaExists) {
        echo "<p style='color: red;'>❌ La tabla info_factura no existe</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabla info_factura existe</p>";
    }
    
    $sql = "SHOW TABLES LIKE 'info_tributaria'";
    $stmt = $pdo->query($sql);
    $infoTributariaExists = $stmt->fetch();
    
    if (!$infoTributariaExists) {
        echo "<p style='color: red;'>❌ La tabla info_tributaria no existe</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabla info_tributaria existe</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error verificando tablas: " . $e->getMessage() . "</p>";
}

// Verificar estructura de columnas
echo "<h2>3. Verificando estructura de columnas...</h2>";
try {
    $sql = "DESCRIBE info_tributaria";
    $stmt = $pdo->query($sql);
    $tributaria_columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    
    echo "<p><strong>Columnas de info_tributaria:</strong></p>";
    echo "<ul>";
    foreach ($tributaria_columns as $column) {
        echo "<li>$column</li>";
    }
    echo "</ul>";
    
    $sql = "DESCRIBE info_factura";
    $stmt = $pdo->query($sql);
    $factura_columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    
    echo "<p><strong>Columnas de info_factura:</strong></p>";
    echo "<ul>";
    foreach ($factura_columns as $column) {
        echo "<li>$column</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error verificando columnas: " . $e->getMessage() . "</p>";
}

// Verificar si hay facturas registradas
echo "<h2>4. Verificando facturas registradas...</h2>";
try {
    $sql = "SELECT COUNT(*) as total FROM info_factura";
    $stmt = $pdo->query($sql);
    $totalFacturas = $stmt->fetch()['total'];
    
    echo "<p>Total de facturas registradas: <strong>$totalFacturas</strong></p>";
    
    if ($totalFacturas > 0) {
        // Mostrar algunas facturas de ejemplo
        $sql = "SELECT 
            it.estab,
            it.pto_emi,
            it.secuencial,
            f.estatus,
            f.razon_social_comprador as cliente
        FROM info_factura f 
        JOIN info_tributaria it ON f.info_tributaria_id = it.id
        LIMIT 5";
        
        $stmt = $pdo->query($sql);
        $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Ejemplos de facturas:</strong></p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Estab</th><th>Pto Emi</th><th>Secuencial</th><th>Estatus</th><th>Cliente</th></tr>";
        
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td>" . $factura['estab'] . "</td>";
            echo "<td>" . $factura['pto_emi'] . "</td>";
            echo "<td>" . $factura['secuencial'] . "</td>";
            echo "<td>" . $factura['estatus'] . "</td>";
            echo "<td>" . $factura['cliente'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error verificando facturas: " . $e->getMessage() . "</p>";
}

// Probar API de búsqueda
echo "<h2>5. Probando API de búsqueda...</h2>";
if ($totalFacturas > 0) {
    try {
        // Tomar la primera factura como ejemplo
        $sql = "SELECT 
            it.estab,
            it.pto_emi,
            it.secuencial
        FROM info_factura f 
        JOIN info_tributaria it ON f.info_tributaria_id = it.id
        LIMIT 1";
        
        $stmt = $pdo->query($sql);
        $facturaEjemplo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($facturaEjemplo) {
            $estab = $facturaEjemplo['estab'];
            $ptoEmi = $facturaEjemplo['pto_emi'];
            $secuencial = $facturaEjemplo['secuencial'];
            
            echo "<p>Probando búsqueda con: Estab=$estab, PtoEmi=$ptoEmi, Secuencial=$secuencial</p>";
            
            // Simular la llamada a la API
            $url = "api/buscar_factura.php?estab=" . urlencode($estab) . "&pto_emi=" . urlencode($ptoEmi) . "&secuencial=" . urlencode($secuencial);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => 'Content-Type: application/json'
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                
                if ($data && isset($data['success'])) {
                    if ($data['success']) {
                        echo "<p style='color: green;'>✅ API de búsqueda funciona correctamente</p>";
                        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
                    } else {
                        echo "<p style='color: red;'>❌ Error en API de búsqueda: " . $data['message'] . "</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ Respuesta inválida de la API</p>";
                    echo "<pre>$response</pre>";
                }
            } else {
                echo "<p style='color: red;'>❌ No se pudo conectar a la API</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error probando API: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No hay facturas para probar la API</p>";
}

echo "<h2>6. Instrucciones de uso</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h3>Para probar la anulación:</h3>";
echo "<ol>";
echo "<li>Abre <code>facturacion.html</code> en tu navegador</li>";
echo "<li>Haz clic en el botón <strong>Anular Factura</strong></li>";
echo "<li>Ingresa los datos de una factura existente (estab, pto_emi, secuencial)</li>";
echo "<li>Haz clic en <strong>Buscar Factura</strong></li>";
echo "<li>Si la factura tiene estatus 'REGISTRADO', podrás anularla</li>";
echo "</ol>";
echo "</div>";

?> 