<?php
/**
 * Script para verificar el estatus de los pagos en el hosting
 * Subir este archivo al hosting y ejecutarlo desde el navegador
 */

// Configuración directa para el hosting
define('DB_HOST', 'localhost');
define('DB_NAME', 'globocit_soft_control');
define('DB_USER', 'globocit_globocit');
define('DB_PASS', 'Correo2026+@');
define('DB_CHARSET', 'utf8mb4');

echo "<h1>🔍 VERIFICACIÓN DE ESTATUS DE PAGOS</h1>";
echo "<hr>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // 1. Verificar todas las facturas con sus pagos
    echo "<h2>📊 TODAS LAS FACTURAS CON PAGOS:</h2>";
    
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
            COUNT(p.id_pago) as total_pagos,
            COALESCE(SUM(p.monto), 0) as total_pagado_real,
            CASE 
                WHEN COALESCE(SUM(p.monto), 0) >= f.importe_total THEN 'COMPLETADA'
                WHEN COALESCE(SUM(p.monto), 0) > 0 THEN 'PARCIAL'
                ELSE 'SIN PAGOS'
            END as estado_real
        FROM info_factura f
        JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
        LEFT JOIN pagos p ON f.id_info_factura = p.id_info_factura
        GROUP BY f.id_info_factura
        ORDER BY f.estatus, f.importe_total DESC
    ");
    
    $facturas = $stmt->fetchAll();
    
    if (empty($facturas)) {
        echo "<p style='color: orange;'>⚠️ No hay facturas registradas</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Factura</th><th>Estatus</th><th>Total</th><th>Pagado</th><th>Saldo</th><th>Pagos</th><th>Pagado Real</th><th>Estado Real</th><th>Problema</th>";
        echo "</tr>";
        
        $problemas = 0;
        foreach ($facturas as $factura) {
            $tieneProblema = false;
            $problema = "";
            
            // Verificar inconsistencias
            if (abs($factura['valor_pagado'] - $factura['total_pagado_real']) > 0.01) {
                $tieneProblema = true;
                $problema .= "Inconsistencia en valor_pagado; ";
            }
            
            // Verificar estatus incorrecto
            if ($factura['estado_real'] == 'COMPLETADA' && $factura['estatus'] != 'PAGADA') {
                $tieneProblema = true;
                $problema .= "Debería estar PAGADA; ";
            }
            
            if ($factura['estado_real'] == 'PARCIAL' && $factura['estatus'] != 'PENDIENTE') {
                $tieneProblema = true;
                $problema .= "Debería estar PENDIENTE; ";
            }
            
            if ($tieneProblema) {
                $problemas++;
                echo "<tr style='background-color: #ffe6e6;'>";
            } else {
                echo "<tr>";
            }
            
            echo "<td>{$factura['estab']}-{$factura['pto_emi']}-{$factura['secuencial']}</td>";
            echo "<td>{$factura['estatus']}</td>";
            echo "<td>\${$factura['importe_total']}</td>";
            echo "<td>\${$factura['valor_pagado']}</td>";
            echo "<td>\${$factura['saldo_pendiente']}</td>";
            echo "<td>{$factura['total_pagos']}</td>";
            echo "<td>\${$factura['total_pagado_real']}</td>";
            echo "<td>{$factura['estado_real']}</td>";
            echo "<td style='color: red;'>" . ($tieneProblema ? $problema : "OK") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p><strong>Total facturas: " . count($facturas) . "</strong></p>";
        echo "<p><strong>Facturas con problemas: " . $problemas . "</strong></p>";
    }
    
    // 2. Resumen por estatus
    echo "<h2>📈 RESUMEN POR ESTATUS:</h2>";
    
    $stmt = $pdo->query("
        SELECT 
            estatus,
            COUNT(*) as cantidad,
            SUM(importe_total) as total_facturas,
            SUM(valor_pagado) as total_pagado
        FROM info_factura 
        GROUP BY estatus
        ORDER BY estatus
    ");
    
    $estados = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Estatus</th><th>Cantidad</th><th>Total Facturas</th><th>Total Pagado</th>";
    echo "</tr>";
    
    foreach ($estados as $estado) {
        echo "<tr>";
        echo "<td>{$estado['estatus']}</td>";
        echo "<td>{$estado['cantidad']}</td>";
        echo "<td>\${$estado['total_facturas']}</td>";
        echo "<td>\${$estado['total_pagado']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Facturas que necesitan corrección
    if ($problemas > 0) {
        echo "<h2>🔧 FACTURAS QUE NECESITAN CORRECCIÓN:</h2>";
        echo "<p style='color: red;'>🚨 Se encontraron $problemas facturas con problemas</p>";
        echo "<p>Ejecuta el script <strong>corregir_pagos_hosting.php</strong> para corregir automáticamente los estatus.</p>";
    } else {
        echo "<h2>✅ VERIFICACIÓN COMPLETADA:</h2>";
        echo "<p style='color: green;'>🎉 Todas las facturas están correctas</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<ol>";
echo "<li>Sube este archivo al hosting</li>";
echo "<li>Accede a él desde el navegador: https://www.globocity.com.ec/soft_control/verificar_estatus_pagos.php</li>";
echo "<li>Revisa los resultados en la tabla</li>";
echo "<li>Si hay problemas, ejecuta corregir_pagos_hosting.php</li>";
echo "<li>Una vez completado, elimina estos archivos del hosting por seguridad</li>";
echo "</ol>";
?>
