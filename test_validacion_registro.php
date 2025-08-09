<?php
// =====================================================
// TEST DE VALIDACIÓN DE REGISTRO DE FACTURAS
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🧪 Test de Validación de Registro de Facturas</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
    
    // =====================================================
    // VERIFICAR FACTURAS CON ESTATUS REGISTRADO
    // =====================================================
    
    echo "<h3>🔍 Verificando facturas con estatus REGISTRADO:</h3>";
    
    $sql = "SELECT 
        f.id_info_factura,
        f.estatus,
        f.retencion,
        f.valor_pagado,
        f.importe_total,
        f.razon_social_comprador,
        it.secuencial
    FROM info_factura f 
    JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
    WHERE f.estatus = 'REGISTRADO'
    ORDER BY f.id_info_factura DESC
    LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($facturas) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>SECUENCIAL</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>CLIENTE</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ESTATUS</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>RETENCIÓN</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>VALOR PAGADO</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>IMPORTE TOTAL</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>VALIDACIÓN</th>";
        echo "</tr>";
        
        $errores = 0;
        $correctas = 0;
        
        foreach ($facturas as $factura) {
            $retencion = floatval($factura['retencion']);
            $valorPagado = floatval($factura['valor_pagado']);
            $esValida = ($retencion == 0 && $valorPagado == 0);
            
            if ($esValida) {
                $correctas++;
                $colorValidacion = '#28a745';
                $textoValidacion = '✅ CORRECTO';
            } else {
                $errores++;
                $colorValidacion = '#dc3545';
                $textoValidacion = '❌ ERROR';
            }
            
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['id_info_factura'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($factura['secuencial'] ?: 'N/A') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($factura['razon_social_comprador'] ?: 'N/A') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: #28a745; font-weight: bold;'>" . $factura['estatus'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: " . ($retencion == 0 ? '#28a745' : '#dc3545') . ";'>$" . number_format($retencion, 2) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: " . ($valorPagado == 0 ? '#28a745' : '#dc3545') . ";'>$" . number_format($valorPagado, 2) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>$" . number_format($factura['importe_total'] ?: 0, 2) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: $colorValidacion; font-weight: bold;'>$textoValidacion</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h4>📊 Resumen de Validación:</h4>";
        echo "<p><strong>Total de facturas REGISTRADO:</strong> " . count($facturas) . "</p>";
        echo "<p style='color: #28a745;'><strong>✅ Correctas:</strong> $correctas</p>";
        echo "<p style='color: " . ($errores > 0 ? '#dc3545' : '#28a745') . ";'><strong>" . ($errores > 0 ? '❌' : '✅') . " Errores:</strong> $errores</p>";
        
        if ($errores > 0) {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
            echo "<h4 style='color: #721c24; margin-top: 0;'>⚠️ Problemas Detectados:</h4>";
            echo "<p style='color: #721c24;'>Se encontraron $errores facturas con estatus REGISTRADO que no tienen 0 en retención o valor pagado.</p>";
            echo "<p style='color: #721c24;'>Esto puede indicar que fueron registradas antes de implementar la validación.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
            echo "<h4 style='color: #155724; margin-top: 0;'>✅ Validación Exitosa:</h4>";
            echo "<p style='color: #155724;'>Todas las facturas con estatus REGISTRADO tienen correctamente 0 en retención y valor pagado.</p>";
            echo "</div>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ No hay facturas con estatus REGISTRADO en la base de datos.</p>";
    }
    
    // =====================================================
    // VERIFICAR TODOS LOS ESTATUS
    // =====================================================
    
    echo "<h3>📋 Resumen de todos los estatus:</h3>";
    
    $sql = "SELECT 
        f.estatus,
        COUNT(*) as total,
        SUM(CASE WHEN f.retencion = 0 AND f.valor_pagado = 0 THEN 1 ELSE 0 END) as con_ceros,
        SUM(CASE WHEN f.retencion != 0 OR f.valor_pagado != 0 THEN 1 ELSE 0 END) as sin_ceros
    FROM info_factura f 
    GROUP BY f.estatus
    ORDER BY f.estatus";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $estatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($estatus) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ESTATUS</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>TOTAL</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>CON CEROS</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>SIN CEROS</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>PORCENTAJE</th>";
        echo "</tr>";
        
        foreach ($estatus as $status) {
            $porcentaje = $status['total'] > 0 ? round(($status['con_ceros'] / $status['total']) * 100, 1) : 0;
            $colorPorcentaje = $porcentaje == 100 ? '#28a745' : ($porcentaje >= 80 ? '#ffc107' : '#dc3545');
            
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>" . ($status['estatus'] ?: 'N/A') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $status['total'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: #28a745;'>" . $status['con_ceros'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: #dc3545;'>" . $status['sin_ceros'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px; color: $colorPorcentaje; font-weight: bold;'>" . $porcentaje . "%</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // =====================================================
    // INSTRUCCIONES PARA CORREGIR
    // =====================================================
    
    echo "<h3>🔧 Instrucciones para Corregir:</h3>";
    echo "<div style='background: #e2e3e5; border: 1px solid #d6d8db; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
    echo "<h4 style='margin-top: 0;'>Si hay facturas con errores:</h4>";
    echo "<ol>";
    echo "<li>Ejecutar el siguiente SQL para corregir las facturas REGISTRADO:</li>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    echo "UPDATE info_factura \n";
    echo "SET retencion = 0.00, valor_pagado = 0.00 \n";
    echo "WHERE estatus = 'REGISTRADO' \n";
    echo "AND (retencion != 0.00 OR valor_pagado != 0.00);";
    echo "</pre>";
    echo "<li>Verificar que todas las facturas REGISTRADO tengan 0 en retención y valor pagado</li>";
    echo "<li>Revisar el proceso de registro de XML para asegurar que se aplique la validación</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Test completado - Sistema de Control GloboCity</em></p>";
?>
