<?php
// =====================================================
// PRUEBA WEB DE ANULACIÓN - VERIFICAR APIS
// =====================================================

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>🔍 Prueba Web de Anulación</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".warning { color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background-color: #f2f2f2; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔍 Prueba Web de Anulación</h1>";

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
    
    echo "<div class='success'>✅ Conexión exitosa</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    exit;
}

// Verificar facturas existentes
echo "<h2>2. Verificando facturas existentes...</h2>";
try {
    $sql = "SELECT COUNT(*) as total FROM info_factura";
    $stmt = $pdo->query($sql);
    $totalFacturas = $stmt->fetch()['total'];
    
    echo "<div class='info'>Total de facturas: <strong>$totalFacturas</strong></div>";
    
    if ($totalFacturas > 0) {
        // Mostrar algunas facturas - intentar múltiples combinaciones de JOIN
        $facturas = [];
        $joinAttempts = [
            "f.id_info_tributaria = it.id_info_tributaria",
            "f.info_tributaria_id = it.id", 
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
                LIMIT 5";
                
                $stmt = $pdo->query($sql);
                $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($facturas)) {
                    echo "<div class='success'>✅ JOIN exitoso usando: $joinCondition</div>";
                    break;
                }
            } catch (Exception $e) {
                echo "<div class='warning'>⚠️ JOIN falló con: $joinCondition - " . $e->getMessage() . "</div>";
                continue;
            }
        }
        
        if (!empty($facturas)) {
            echo "<h3>Ejemplos de facturas:</h3>";
            echo "<table>";
            echo "<tr><th>Estab</th><th>Pto Emi</th><th>Secuencial</th><th>Estatus</th><th>Cliente</th><th>Acción</th></tr>";
            
            foreach ($facturas as $factura) {
                echo "<tr>";
                echo "<td>" . $factura['estab'] . "</td>";
                echo "<td>" . $factura['pto_emi'] . "</td>";
                echo "<td>" . $factura['secuencial'] . "</td>";
                echo "<td>" . $factura['estatus'] . "</td>";
                echo "<td>" . $factura['cliente'] . "</td>";
                echo "<td>";
                if ($factura['estatus'] === 'REGISTRADO') {
                    echo "<button class='btn' onclick='probarBusqueda(\"" . $factura['estab'] . "\", \"" . $factura['pto_emi'] . "\", \"" . $factura['secuencial'] . "\")'>Probar Búsqueda</button>";
                } else {
                    echo "<span style='color: gray;'>No anulable</span>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='error'>❌ No se pudo hacer JOIN entre las tablas</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ No hay facturas para probar</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "<h2>3. Prueba de APIs</h2>";
echo "<div id='api-results'></div>";

echo "<h2>4. Instrucciones</h2>";
echo "<div class='info'>";
echo "<h3>Para probar la anulación:</h3>";
echo "<ol>";
echo "<li>Abre <code>facturacion.html</code> en tu navegador</li>";
echo "<li>Haz clic en <strong>Anular Factura</strong></li>";
echo "<li>Ingresa los datos de una factura existente</li>";
echo "<li>Haz clic en <strong>Buscar Factura</strong></li>";
echo "<li>Si la factura tiene estatus 'REGISTRADO', podrás anularla</li>";
echo "</ol>";
echo "</div>";

echo "</div>";

echo "<script>";
echo "function probarBusqueda(estab, ptoEmi, secuencial) {";
echo "    const resultsDiv = document.getElementById('api-results');";
echo "    resultsDiv.innerHTML = '<div class=\"info\">🔍 Probando búsqueda...</div>';";
echo "    ";
echo "    fetch(`api/buscar_factura.php?estab=${estab}&pto_emi=${ptoEmi}&secuencial=${secuencial}`)";
echo "        .then(response => response.json())";
echo "        .then(data => {";
echo "            if (data.success) {";
echo "                resultsDiv.innerHTML = '<div class=\"success\">✅ API de búsqueda funciona</div><pre>' + JSON.stringify(data, null, 2) + '</pre>';";
echo "            } else {";
echo "                resultsDiv.innerHTML = '<div class=\"error\">❌ Error en API: ' + data.message + '</div>';";
echo "            }";
echo "        })";
echo "        .catch(error => {";
echo "            resultsDiv.innerHTML = '<div class=\"error\">❌ Error de conexión: ' + error.message + '</div>';";
echo "        });";
echo "}";
echo "</script>";

echo "</body>";
echo "</html>";
?> 