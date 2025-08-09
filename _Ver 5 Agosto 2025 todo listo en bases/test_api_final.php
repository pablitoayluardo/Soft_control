<?php
// Script final para probar la API de facturas
echo "<h2>🔍 Test Final de API de Facturas</h2>";

// Incluir la API directamente
ob_start();
include 'api/get_facturas_simple.php';
$api_response = ob_get_clean();

echo "<h3>Respuesta de la API:</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars($api_response);
echo "</pre>";

// Decodificar JSON para verificar
$data = json_decode($api_response, true);

if ($data) {
    echo "<h3>Análisis:</h3>";
    echo "<ul>";
    echo "<li><strong>Success:</strong> " . ($data['success'] ? '✅ true' : '❌ false') . "</li>";
    if (isset($data['data'])) {
        echo "<li><strong>Número de facturas:</strong> " . count($data['data']) . "</li>";
    }
    if (isset($data['debug'])) {
        echo "<li><strong>Debug info:</strong> " . json_encode($data['debug']) . "</li>";
    }
    echo "</ul>";
    
    if (isset($data['data']) && count($data['data']) > 0) {
        echo "<h3>✅ Facturas encontradas:</h3>";
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estab</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Pto Emi</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Secuencial</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Cliente</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Total</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estatus</th>";
        echo "</tr>";
        
        foreach ($data['data'] as $factura) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['estab'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['pto_emi'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['secuencial'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['cliente'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['total'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $factura['estatus'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
        echo "<h4>🎉 ¡Éxito!</h4>";
        echo "<p>La API está funcionando correctamente. Las facturas se están mostrando en la lista.</p>";
        echo "<p><strong>Próximo paso:</strong> Ve a <a href='facturacion.html'>facturacion.html</a> para ver las facturas en el frontend.</p>";
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>⚠️ No se encontraron facturas en la respuesta</p>";
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
        echo "<h4>💡 Información</h4>";
        echo "<p>No hay facturas registradas en la base de datos. Esto es normal si aún no se han subido facturas.</p>";
        echo "<p><strong>Para probar:</strong> Ejecuta <a href='fix_join_data.php'>fix_join_data.php</a> para crear datos de prueba.</p>";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>❌ Error al decodificar JSON</p>";
}

echo "<hr>";
echo "<p><a href='facturacion.html'>📊 Ir a Facturación</a></p>";
echo "<p><a href='api/get_facturas_simple.php' target='_blank'>🔍 Ver API directamente</a></p>";
echo "<p><a href='verificar_estructura_hosting.php'>🔍 Verificar estructura de tablas</a></p>";
?> 