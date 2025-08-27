<?php
/**
 * Script para probar pagos exactos en el hosting
 * Subir este archivo al hosting y ejecutarlo desde el navegador
 */

// Configuración directa para el hosting
define('DB_HOST', 'localhost');
define('DB_NAME', 'globocit_soft_control');
define('DB_USER', 'globocit_globocit');
define('DB_PASS', 'Correo2026+@');
define('DB_CHARSET', 'utf8mb4');

echo "<h1>🧪 PRUEBA DE PAGOS EXACTOS</h1>";
echo "<hr>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // Buscar una factura con saldo exacto para probar
    echo "<h2>🔍 BUSCANDO FACTURA PARA PRUEBA:</h2>";
    
    $stmt = $pdo->query("
        SELECT 
            f.id_info_factura,
            f.estatus,
            f.importe_total,
            f.valor_pagado,
            (f.importe_total - f.valor_pagado) as saldo_pendiente,
            it.estab,
            it.pto_emi,
            it.secuencial,
            it.id_info_tributaria
        FROM info_factura f
        JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
        WHERE f.estatus IN ('PENDIENTE', 'REGISTRADO')
        AND (f.importe_total - f.valor_pagado) > 0
        ORDER BY saldo_pendiente ASC
        LIMIT 1
    ");
    
    $factura = $stmt->fetch();
    
    if (!$factura) {
        echo "<p style='color: orange;'>⚠️ No hay facturas pendientes para probar</p>";
    } else {
        echo "<p><strong>Factura encontrada:</strong></p>";
        echo "<ul>";
        echo "<li>Factura: {$factura['estab']}-{$factura['pto_emi']}-{$factura['secuencial']}</li>";
        echo "<li>Estatus: {$factura['estatus']}</li>";
        echo "<li>Total: \${$factura['importe_total']}</li>";
        echo "<li>Pagado: \${$factura['valor_pagado']}</li>";
        echo "<li>Saldo: \${$factura['saldo_pendiente']}</li>";
        echo "</ul>";
        
        // Simular la validación del pago
        $monto = floatval($factura['saldo_pendiente']);
        $saldoActual = floatval($factura['saldo_pendiente']);
        
        echo "<h2>🧪 SIMULANDO VALIDACIÓN:</h2>";
        echo "<p>Monto a pagar: \$$monto</p>";
        echo "<p>Saldo actual: \$$saldoActual</p>";
        
        // Probar la nueva validación
        if ($monto > ($saldoActual + 0.01)) {
            echo "<p style='color: red;'>❌ ERROR: El monto excede el saldo</p>";
        } else {
            echo "<p style='color: green;'>✅ VALIDACIÓN PASADA: El pago es válido</p>";
            
            // Simular el cálculo del nuevo saldo
            $nuevoSaldo = $saldoActual - $monto;
            $nuevoValorPagado = floatval($factura['valor_pagado']) + $monto;
            $nuevoEstatus = ($nuevoSaldo <= 0.01) ? 'PAGADA' : 'PENDIENTE';
            
            echo "<h2>📊 SIMULACIÓN DE RESULTADO:</h2>";
            echo "<ul>";
            echo "<li>Nuevo saldo: \$$nuevoSaldo</li>";
            echo "<li>Nuevo valor pagado: \$$nuevoValorPagado</li>";
            echo "<li>Nuevo estatus: $nuevoEstatus</li>";
            echo "</ul>";
            
            if ($nuevoEstatus == 'PAGADA') {
                echo "<p style='color: green; font-weight: bold;'>🎉 La factura se marcaría como PAGADA</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ La factura se mantendría como PENDIENTE</p>";
            }
        }
    }
    
    // Mostrar todas las facturas pendientes
    echo "<h2>📋 TODAS LAS FACTURAS PENDIENTES:</h2>";
    
    $stmt = $pdo->query("
        SELECT 
            f.id_info_factura,
            f.estatus,
            f.importe_total,
            f.valor_pagado,
            (f.importe_total - f.valor_pagado) as saldo_pendiente,
            it.estab,
            it.pto_emi,
            it.secuencial
        FROM info_factura f
        JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
        WHERE f.estatus IN ('PENDIENTE', 'REGISTRADO')
        AND (f.importe_total - f.valor_pagado) > 0
        ORDER BY saldo_pendiente ASC
    ");
    
    $facturasPendientes = $stmt->fetchAll();
    
    if (empty($facturasPendientes)) {
        echo "<p style='color: green;'>✅ No hay facturas pendientes</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Factura</th><th>Estatus</th><th>Total</th><th>Pagado</th><th>Saldo</th>";
        echo "</tr>";
        
        foreach ($facturasPendientes as $f) {
            echo "<tr>";
            echo "<td>{$f['estab']}-{$f['pto_emi']}-{$f['secuencial']}</td>";
            echo "<td>{$f['estatus']}</td>";
            echo "<td>\${$f['importe_total']}</td>";
            echo "<td>\${$f['valor_pagado']}</td>";
            echo "<td>\${$f['saldo_pendiente']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<ol>";
echo "<li>Sube este archivo al hosting</li>";
echo "<li>Accede a él desde el navegador: https://www.globocity.com.ec/soft_control/test_pago_exacto.php</li>";
echo "<li>Revisa si la validación funciona correctamente</li>";
echo "<li>Una vez completado, elimina este archivo del hosting por seguridad</li>";
echo "</ol>";
?>
