<?php
/**
 * Script para corregir pagos parciales y cambiar estatus a PAGADA
 * Este script debe ejecutarse en el hosting
 */

// Configuración directa para el hosting
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';
$charset = 'utf8mb4';

echo "<h2>🔍 CORRECCIÓN DE PAGOS PARCIALES</h2>";
echo "<hr>";

try {
    // Conexión a la base de datos
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p>✅ Conexión exitosa</p>";
    
    // 1. Verificar facturas con pagos parciales
    echo "<h3>📊 FACTURAS CON PAGOS PARCIALES:</h3>";
    
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
            COALESCE(SUM(p.monto), 0) as total_pagado_real
        FROM info_factura f
        JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
        LEFT JOIN pagos p ON f.id_info_factura = p.id_info_factura
        WHERE f.estatus IN ('PENDIENTE', 'REGISTRADO')
        GROUP BY f.id_info_factura
        HAVING total_pagado_real > 0
        ORDER BY saldo_pendiente ASC
    ");
    
    $facturasParciales = $stmt->fetchAll();
    
    if (empty($facturasParciales)) {
        echo "<p>❌ No hay facturas con pagos parciales</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Factura</th><th>Estatus</th><th>Total</th><th>Pagado</th><th>Saldo</th><th>Pagos Real</th><th>Acción</th>";
        echo "</tr>";
        
        foreach ($facturasParciales as $factura) {
            $debeEstarPagada = $factura['saldo_pendiente'] <= 0;
            $inconsistencia = abs($factura['valor_pagado'] - $factura['total_pagado_real']) > 0.01;
            
            $rowColor = $debeEstarPagada ? '#ffebee' : ($inconsistencia ? '#fff3e0' : 'white');
            
            echo "<tr style='background-color: $rowColor;'>";
            echo "<td>{$factura['estab']}-{$factura['pto_emi']}-{$factura['secuencial']}</td>";
            echo "<td>{$factura['estatus']}</td>";
            echo "<td>\${$factura['importe_total']}</td>";
            echo "<td>\${$factura['valor_pagado']}</td>";
            echo "<td>\${$factura['saldo_pendiente']}</td>";
            echo "<td>\${$factura['total_pagado_real']}</td>";
            
            if ($debeEstarPagada) {
                echo "<td style='color: red; font-weight: bold;'>DEBE ESTAR PAGADA</td>";
            } elseif ($inconsistencia) {
                echo "<td style='color: orange;'>INCONSISTENCIA</td>";
            } else {
                echo "<td>OK</td>";
            }
            
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 2. Verificar facturas que deberían estar PAGADAS
    echo "<h3>🔍 FACTURAS QUE DEBERÍAN ESTAR PAGADAS:</h3>";
    
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
        echo "<p>✅ No hay facturas que necesiten corrección de estatus</p>";
    } else {
        echo "<p>🚨 <strong>FACTURAS QUE NECESITAN CORRECCIÓN:</strong></p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #ffebee;'>";
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
        
        // 3. Botón para corregir
        echo "<br>";
        echo "<form method='post'>";
        echo "<input type='submit' name='corregir' value='🔧 CORREGIR ESTATUS DE FACTURAS COMPLETADAS' style='background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;'>";
        echo "</form>";
    }
    
    // 4. Procesar corrección si se solicita
    if (isset($_POST['corregir']) && !empty($facturasCompletadas)) {
        echo "<h3>🔧 CORRIGIENDO ESTATUS DE FACTURAS COMPLETADAS:</h3>";
        
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
                    echo "<p>✅ Corregida: {$factura['estab']}-{$factura['pto_emi']}-{$factura['secuencial']}</p>";
                    echo "<p>   Estatus: {$factura['estatus']} → PAGADA</p>";
                    echo "<p>   Valor pagado: \${$factura['valor_pagado']} → \${$factura['total_pagado_real']}</p>";
                    $corregidas++;
                }
            }
            
            $pdo->commit();
            echo "<p><strong>✅ Se corrigieron $corregidas facturas</strong></p>";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<p style='color: red;'>❌ Error al corregir: " . $e->getMessage() . "</p>";
        }
    }
    
    // 5. Verificar estado final
    echo "<h3>📊 ESTADO FINAL DEL SISTEMA:</h3>";
    
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
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
