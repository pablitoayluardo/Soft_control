<?php
/**
 * Script para verificar pagos parciales y generar reporte
 */

// Iniciar buffer de salida
ob_start();

echo "🔍 VERIFICACIÓN DE PAGOS PARCIALES\n";
echo "==================================\n\n";

// Incluir configuración
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Conexión exitosa\n\n";
    
    // 1. Verificar facturas con pagos parciales
    echo "📊 FACTURAS CON PAGOS PARCIALES:\n";
    echo "--------------------------------\n";
    
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
        echo "❌ No hay facturas con pagos parciales\n\n";
    } else {
        foreach ($facturasParciales as $factura) {
            echo "📄 Factura: {$factura['estab']}-{$factura['pto_emi']}-{$factura['secuencial']}\n";
            echo "   Estatus: {$factura['estatus']}\n";
            echo "   Total: \${$factura['importe_total']}\n";
            echo "   Pagado: \${$factura['valor_pagado']}\n";
            echo "   Saldo: \${$factura['saldo_pendiente']}\n";
            echo "   Pagos registrados: {$factura['total_pagos']}\n";
            echo "   Total pagado real: \${$factura['total_pagado_real']}\n";
            
            // Verificar si hay inconsistencia
            if (abs($factura['valor_pagado'] - $factura['total_pagado_real']) > 0.01) {
                echo "   ⚠️  INCONSISTENCIA: valor_pagado ≠ total_pagado_real\n";
            }
            
            // Verificar si debería estar PAGADA
            if ($factura['saldo_pendiente'] <= 0) {
                echo "   🚨 DEBERÍA ESTAR PAGADA (saldo <= 0)\n";
            }
            
            echo "\n";
        }
    }
    
    // 2. Verificar facturas que deberían estar PAGADAS
    echo "🔍 FACTURAS QUE DEBERÍAN ESTAR PAGADAS:\n";
    echo "---------------------------------------\n";
    
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
        echo "✅ No hay facturas que necesiten corrección de estatus\n\n";
    } else {
        echo "🚨 FACTURAS QUE NECESITAN CORRECCIÓN:\n\n";
        foreach ($facturasCompletadas as $factura) {
            echo "📄 Factura: {$factura['estab']}-{$factura['pto_emi']}-{$factura['secuencial']}\n";
            echo "   Estatus actual: {$factura['estatus']}\n";
            echo "   Total: \${$factura['importe_total']}\n";
            echo "   Pagado real: \${$factura['total_pagado_real']}\n";
            echo "   Saldo: \${$factura['saldo_pendiente']}\n";
            echo "   Estatus correcto: PAGADA\n\n";
        }
    }
    
    // 3. Corregir estatus de facturas completadas
    if (!empty($facturasCompletadas)) {
        echo "🔧 CORRIGIENDO ESTATUS DE FACTURAS COMPLETADAS:\n";
        echo "-----------------------------------------------\n";
        
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
                    echo "✅ Corregida: {$factura['estab']}-{$factura['pto_emi']}-{$factura['secuencial']}\n";
                    echo "   Estatus: {$factura['estatus']} → PAGADA\n";
                    echo "   Valor pagado: \${$factura['valor_pagado']} → \${$factura['total_pagado_real']}\n\n";
                    $corregidas++;
                }
            }
            
            $pdo->commit();
            echo "✅ Se corrigieron $corregidas facturas\n\n";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "❌ Error al corregir: " . $e->getMessage() . "\n\n";
        }
    }
    
    // 4. Verificar estado final
    echo "📊 ESTADO FINAL DEL SISTEMA:\n";
    echo "----------------------------\n";
    
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
    
    foreach ($estados as $estado) {
        echo "📈 {$estado['estatus']}: {$estado['cantidad']} facturas\n";
        echo "   Total: \${$estado['total_facturas']}\n";
        echo "   Pagado: \${$estado['total_pagado']}\n\n";
    }
    
    // 5. Verificar consistencia
    echo "🔍 VERIFICACIÓN DE CONSISTENCIA:\n";
    echo "--------------------------------\n";
    
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
        echo "✅ No hay inconsistencias detectadas\n";
    } else {
        echo "⚠️  INCONSISTENCIAS DETECTADAS:\n";
        foreach ($inconsistencias as $inc) {
            echo "   ID: {$inc['id_info_factura']} - Diferencia: \${$inc['diferencia']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Obtener el contenido del buffer
$output = ob_get_contents();

// Limpiar el buffer
ob_end_clean();

// Escribir el contenido a un archivo
file_put_contents('reporte_pagos_parciales.txt', $output);

echo "✅ Reporte generado en: reporte_pagos_parciales.txt\n";
?>
