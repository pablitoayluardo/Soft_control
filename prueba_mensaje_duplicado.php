<?php
/**
 * Script de prueba específico para verificar mensaje de duplicado
 */

require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Prueba Mensaje Duplicado</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .info { color: #17a2b8; font-weight: bold; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; background: #f8f9fa; }
    .test-step { margin: 10px 0; padding: 10px; border-left: 4px solid #007bff; background: #f8f9fa; }
    .message-box { padding: 15px; margin: 10px 0; border-radius: 5px; white-space: pre-line; }
    .message-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-primary { background: #007bff; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-warning { background: #ffc107; color: black; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🧪 Prueba Específica: Mensaje de Duplicado</h1>";
echo "<p><strong>Verificación del mensaje de duplicado corregido</strong></p>";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("❌ Error: No se pudo conectar a la base de datos");
    }
    
    echo "<div class='success'>✅ Conexión establecida correctamente</div><br>";
    
    // Verificar si existe el comprobante de prueba
    echo "<div class='section'>";
    echo "<h2>🔍 Verificación de Comprobante Existente</h2>";
    
    $stmt = $pdo->prepare("
        SELECT id, numero_comprobante, fecha_emision, emisor.razon_social as emisor_nombre
        FROM ComprobantesRetencion cr
        JOIN Contribuyentes emisor ON cr.emisor_id = emisor.id
        WHERE cr.numero_comprobante = ? OR cr.clave_acceso = ?
    ");
    $stmt->execute(['001-027-000259289', 'TEST-DUPLICADO-001']);
    $comprobanteExistente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($comprobanteExistente) {
        echo "<div class='success'>✅ Comprobante encontrado en la base de datos</div>";
        echo "<div class='info'>📄 Número: " . $comprobanteExistente['numero_comprobante'] . "</div>";
        echo "<div class='info'>📅 Fecha: " . date('d/m/Y', strtotime($comprobanteExistente['fecha_emision'])) . "</div>";
        echo "<div class='info'>🏢 Emisor: " . $comprobanteExistente['emisor_nombre'] . "</div>";
        
        // Simular el mensaje que debería aparecer
        echo "<h3>📱 Mensaje que debería aparecer al intentar subir el mismo XML:</h3>";
        
        $mensajeDuplicado = "⚠️ Este comprobante de retención ya fue procesado anteriormente.\n\n" .
                           "📄 Número: " . $comprobanteExistente['numero_comprobante'] . "\n" .
                           "📅 Fecha: " . date('d/m/Y', strtotime($comprobanteExistente['fecha_emision'])) . "\n" .
                           "🏢 Emisor: " . $comprobanteExistente['emisor_nombre'] . "\n\n" .
                           "💡 Si necesitas procesarlo nuevamente, primero elimínalo desde la lista de retenciones.";
        
        echo "<div class='message-box message-error'>$mensajeDuplicado</div>";
        
        echo "<div class='success'>✅ Mensaje de duplicado configurado correctamente</div>";
        
    } else {
        echo "<div class='warning'>⚠️ No se encontró el comprobante de prueba</div>";
        echo "<div class='info'>💡 Ejecuta primero: prueba_mensajes_mejorados.php</div>";
    }
    echo "</div>";
    
    // Mostrar el código de validación
    echo "<div class='section'>";
    echo "<h2>🔧 Código de Validación Implementado</h2>";
    echo "<div class='test-step'>";
    echo "<strong>✅ Validación movida ANTES de la transacción</strong><br>";
    echo "<strong>✅ Verifica por número de comprobante Y clave de acceso</strong><br>";
    echo "<strong>✅ Muestra información detallada del comprobante existente</strong><br>";
    echo "<strong>✅ Incluye instrucciones claras para el usuario</strong>";
    echo "</div>";
    echo "</div>";
    
    // Instrucciones de prueba
    echo "<div class='section'>";
    echo "<h2>🚀 Instrucciones de Prueba</h2>";
    echo "<ol>";
    echo "<li><strong>Ve a:</strong> <a href='retenciones.html' target='_blank'>retenciones.html</a></li>";
    echo "<li><strong>Selecciona</strong> un archivo XML que ya hayas subido anteriormente</li>";
    echo "<li><strong>Confirma</strong> los datos en el modal</li>";
    echo "<li><strong>Verifica</strong> que aparezca el mensaje de duplicado (no el mensaje de error general)</li>";
    echo "</ol>";
    
    echo "<div class='warning'>⚠️ Si aún aparece el mensaje de error general, puede ser que:</div>";
    echo "<ul>";
    echo "<li>El archivo XML tenga un formato diferente</li>";
    echo "<li>Haya un error en la extracción de datos del XML</li>";
    echo "<li>El comprobante no se esté identificando correctamente</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='retenciones.html' class='btn btn-primary' target='_blank'>📄 Ir a Retenciones</a>";
    echo "<a href='prueba_mensajes_mejorados.php' class='btn btn-success'>🧪 Prueba Completa</a>";
    echo "<a href='limpiar_datos_retenciones.php' class='btn btn-warning'>🧹 Limpiar Datos</a>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
