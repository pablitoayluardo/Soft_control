<?php
/**
 * Script para corregir pagos parciales en el hosting
 * Subir este archivo al hosting y ejecutarlo desde el navegador
 */

// Configuración directa para el hosting
define('DB_HOST', 'localhost');
define('DB_NAME', 'globocit_soft_control');
define('DB_USER', 'globocit_globocit');
define('DB_PASS', 'Correo2026+@');
define('DB_CHARSET', 'utf8mb4');

echo "<h1>🔍 CORRECCIÓN DE PAGOS PARCIALES</h1>";
echo "<hr>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // 1. Verificar facturas que deberían estar PAGADAS
    echo "<h2>🔍 FACTURAS QUE DEBERÍAN ESTAR PAGADAS:</h2>";
    
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
            COALESCE(SUM(p.monto), 0) as total_pagado_real
        FROM info_factura f
        JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
        LEFT JOIN pagos p ON f.id_info_factura = p.id_info_factura
        WHERE f.estatus IN ('PENDIENTE', 'REGISTRADO')
        GROUP BY f.id_info_factura
        HAVING total_pagado_real >= f.importe_total
        ORDER BY f.importe_total ASC
    ");
    
    $facturasCompletadas = $stmt->fetchAll();
    
    if (empty($facturasCompletadas)) {
        echo "<p style='color: green;'>✅ No hay facturas que necesiten corrección de estatus</p>";
    } else {
        echo "<p style='color: red;'>🚨 FACTURAS QUE NECESITAN CORRECCIÓN:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Factura</th><th>Estatus Actual</th><th>Total</th><th>Pagado Real</th><th>Saldo</th>";
        echo "</tr>";
        
        foreach ($facturasCompletadas as $factura) {
            echo "<tr>";
            echo "<td>{$factura['estab']}-{$factura['pto_emi']}-{$factura['secuencial']}</td>";
            echo "<td>{$factura['estatus']}</td>";
            echo "<td>\${$factura['importe_total']}</td>";
            echo "<td>\${$factura['total_pagado_real']}</td>";
            echo "<td>\${$factura['saldo_pendiente']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 2. Corregir estatus de facturas completadas
        echo "<h2>🔧 CORRIGIENDO ESTATUS DE FACTURAS COMPLETADAS:</h2>";
        
        $pdo->beginTransaction();
        
        try {
            $corregidas = 0;
            foreach ($facturasCompletadas as $factura) {
                $stmt = $pdo->prepare("
                    UPDATE info_factura 
                    SET estatus = 'PAGADA', valor_pagado = ?
                    WHERE id_info_factura = ?
                ");
                
                $stmt->execute([$factura['total_pagado_real'], $factura['id_info_factura']]);
                
                if ($stmt->rowCount() > 0) {
                    echo "<p style='color: green;'>✅ Corregida: {$factura['estab']}-{$factura['pto_emi']}-{$factura['secuencial']}</p>";
                    echo "<p>   Estatus: {$factura['estatus']} → PAGADA</p>";
                    echo "<p>   Valor pagado: \${$factura['valor_pagado']} → \${$factura['total_pagado_real']}</p>";
                    $corregidas++;
                }
            }
            
            $pdo->commit();
            echo "<p style='color: green; font-weight: bold;'>✅ Se corrigieron $corregidas facturas</p>";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<p style='color: red;'>❌ Error al corregir: " . $e->getMessage() . "</p>";
        }
    }
    
    // 3. Verificar estado final
    echo "<h2>📊 ESTADO FINAL DEL SISTEMA:</h2>";
    
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
    
    // 4. Verificar consistencia
    echo "<h2>🔍 VERIFICACIÓN DE CONSISTENCIA:</h2>";
    
    $stmt = $pdo->query("
        SELECT 
            f.id_info_factura,
            f.estatus,
            f.importe_total,
            f.valor_pagado,
            COALESCE(SUM(p.monto), 0) as total_pagado_real,
            ABS(f.valor_pagado - COALESCE(SUM(p.monto), 0)) as diferencia
        FROM info_factura f
        LEFT JOIN pagos p ON f.id_info_factura = p.id_info_factura
        GROUP BY f.id_info_factura
        HAVING diferencia > 0.01
        ORDER BY diferencia DESC
        LIMIT 5
    ");
    
    $inconsistencias = $stmt->fetchAll();
    
    if (empty($inconsistencias)) {
        echo "<p style='color: green;'>✅ No hay inconsistencias detectadas</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ INCONSISTENCIAS DETECTADAS:</p>";
        echo "<ul>";
        foreach ($inconsistencias as $inc) {
            echo "<li>ID: {$inc['id_info_factura']} - Diferencia: \${$inc['diferencia']}</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<ol>";
echo "<li>Sube este archivo al hosting</li>";
echo "<li>Accede a él desde el navegador: https://www.globocity.com.ec/soft_control/corregir_pagos_hosting.php</li>";
echo "<li>El script verificará y corregirá automáticamente los estatus de las facturas</li>";
echo "<li>Una vez completado, elimina este archivo del hosting por seguridad</li>";
echo "</ol>";
?>
