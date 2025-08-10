<?php
/**
 * Script para probar la API del listado de facturas
 * Sube este archivo a tu hosting y ejecútalo
 */

echo "<h2>🧪 Prueba de API del Listado de Facturas</h2>";

// Incluir configuración del hosting
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        echo "<p style='color: red;'>❌ Error de conexión a la base de datos</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";
    
    // Simular la consulta que hace get_facturas_simple.php
    echo "<h3>📊 Consulta directa a la tabla 'facturas':</h3>";
    
    $sql = "SELECT 
        id,
        numero_factura,
        numero_autorizacion,
        fecha_emision,
        cliente,
        ruc,
        direccion,
        subtotal,
        iva,
        total,
        moneda,
        ambiente,
        tipo_emision,
        secuencial,
        fecha_registro
    FROM facturas
    ORDER BY fecha_emision DESC, fecha_registro DESC
    LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Total de facturas encontradas: <strong>" . count($facturas) . "</strong></p>";
    
    if (count($facturas) > 0) {
        echo "<h4>📄 Facturas encontradas:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Número Factura</th><th>Cliente</th><th>Total</th><th>Fecha Emisión</th><th>Fecha Registro</th>";
        echo "</tr>";
        
        foreach ($facturas as $factura) {
            echo "<tr>";
            echo "<td>" . $factura['id'] . "</td>";
            echo "<td>" . $factura['numero_factura'] . "</td>";
            echo "<td>" . $factura['cliente'] . "</td>";
            echo "<td>" . $factura['total'] . "</td>";
            echo "<td>" . $factura['fecha_emision'] . "</td>";
            echo "<td>" . $factura['fecha_registro'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Formatear datos como lo hace la API
        echo "<h4>📄 Datos formateados como la API:</h4>";
        $formattedFacturas = [];
        foreach ($facturas as $factura) {
            $formattedFacturas[] = [
                'id' => $factura['id'],
                'fecha' => $factura['fecha_emision'] ? date('d/m/Y', strtotime($factura['fecha_emision'])) : 'N/A',
                'secuencia' => $factura['secuencial'] ?: $factura['numero_factura'],
                'numero_factura' => $factura['numero_factura'] ?: $factura['numero_autorizacion'],
                'cliente' => $factura['cliente'] ?: 'N/A',
                'direccion' => $factura['direccion'] ?: 'N/A',
                'total_fac' => number_format($factura['total'] ?: 0, 2),
                'estatus' => 'REGISTRADO',
                'retencion' => '0.00',
                'valor_pagado' => number_format($factura['total'] ?: 0, 2),
                'observacion' => 'Factura registrada desde XML',
                'ruc' => $factura['ruc'] ?: 'N/A'
            ];
        }
        
        echo "<pre>" . json_encode($formattedFacturas, JSON_PRETTY_PRINT) . "</pre>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ No se encontraron facturas en la tabla 'facturas'</p>";
    }
    
    // Probar la API real
    echo "<h3>🌐 Probando la API real:</h3>";
    
    // Simular la llamada a get_facturas_simple.php
    $apiUrl = 'api/get_facturas_simple.php';
    if (file_exists($apiUrl)) {
        echo "<p>✅ El archivo API existe</p>";
        
        // Capturar la salida de la API
        ob_start();
        include $apiUrl;
        $apiOutput = ob_get_clean();
        
        echo "<h4>Respuesta de la API:</h4>";
        echo "<pre>" . htmlspecialchars($apiOutput) . "</pre>";
        
        // Intentar decodificar JSON
        $jsonData = json_decode($apiOutput, true);
        if ($jsonData) {
            echo "<h4>Datos decodificados:</h4>";
            echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ La API no devolvió JSON válido</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ El archivo API no existe: $apiUrl</p>";
    }
    
    echo "<h3>✅ Prueba completada</h3>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 