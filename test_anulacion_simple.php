<?php
// =====================================================
// PRUEBA SIMPLE DE ANULACIÓN - VERIFICAR APIS
// =====================================================

echo "<h1>🔍 Prueba Simple de Anulación</h1>";

// Probar conexión a la base de datos
echo "<h2>1. Verificando conexión...</h2>";
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
    
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar facturas existentes
echo "<h2>2. Verificando facturas existentes...</h2>";
try {
    $sql = "SELECT COUNT(*) as total FROM info_factura";
    $stmt = $pdo->query($sql);
    $totalFacturas = $stmt->fetch()['total'];
    
    echo "<p>Total de facturas: <strong>$totalFacturas</strong></p>";
    
    if ($totalFacturas > 0) {
        // Mostrar algunas facturas - intentar múltiples combinaciones de JOIN
        $facturas = [];
        $joinAttempts = [
            "f.info_tributaria_id = it.id",
            "f.id_info_tributaria = it.id_info_tributaria", 
            "f.info_tributaria_id = it.id_info_tributaria"
        ];
        
        foreach ($joinAttempts as $joinCondition) {
            try {
                $sql = "SELECT 
                    it.estab,
                    it.pto_emi,
                    it.secuencial,
                    f.estatus,
                    f.razon_social_comprador as cliente
                FROM info_factura f 
                JOIN info_tributaria it ON $joinCondition
                LIMIT 3";
                
                $stmt = $pdo->query($sql);
                $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($facturas)) {
                    echo "<p style='color: green;'>✅ JOIN exitoso usando: $joinCondition</p>";
                    break;
                }
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠️ JOIN falló con: $joinCondition - " . $e->getMessage() . "</p>";
                continue;
            }
        }
        
        if (!empty($facturas)) {
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
            
            // Probar API de búsqueda
            if (count($facturas) > 0) {
                $facturaEjemplo = $facturas[0];
                echo "<h2>3. Probando API de búsqueda...</h2>";
                
                $estab = $facturaEjemplo['estab'];
                $ptoEmi = $facturaEjemplo['pto_emi'];
                $secuencial = $facturaEjemplo['secuencial'];
                
                echo "<p>Probando con: Estab=$estab, PtoEmi=$ptoEmi, Secuencial=$secuencial</p>";
                
                // Simular llamada a la API
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
                            echo "<p style='color: green;'>✅ API de búsqueda funciona</p>";
                            echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
                        } else {
                            echo "<p style='color: red;'>❌ Error en API: " . $data['message'] . "</p>";
                        }
                    } else {
                        echo "<p style='color: red;'>❌ Respuesta inválida</p>";
                        echo "<pre>$response</pre>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ No se pudo conectar a la API</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>❌ No se pudo hacer JOIN entre las tablas</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No hay facturas para probar</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Instrucciones</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h3>Para probar la anulación:</h3>";
echo "<ol>";
echo "<li>Abre <code>facturacion.html</code> en tu navegador</li>";
echo "<li>Haz clic en <strong>Anular Factura</strong></li>";
echo "<li>Ingresa los datos de una factura existente</li>";
echo "<li>Haz clic en <strong>Buscar Factura</strong></li>";
echo "<li>Si la factura tiene estatus 'REGISTRADO', podrás anularla</li>";
echo "</ol>";
echo "</div>";

?> 