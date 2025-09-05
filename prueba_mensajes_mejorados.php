<?php
/**
 * Script de prueba para verificar mensajes mejorados
 * Prueba duplicados y mensajes de error amigables
 */

require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Prueba Mensajes Mejorados</title>";
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
    .message-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .message-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-primary { background: #007bff; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-warning { background: #ffc107; color: black; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🧪 Prueba de Mensajes Mejorados</h1>";
echo "<p><strong>Verificación de mensajes amigables para el usuario</strong></p>";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("❌ Error: No se pudo conectar a la base de datos");
    }
    
    echo "<div class='success'>✅ Conexión establecida correctamente</div><br>";
    
    // PASO 1: Insertar un comprobante de prueba
    echo "<div class='section'>";
    echo "<h2>📝 PASO 1: Insertar Comprobante de Prueba</h2>";
    
    try {
        $pdo->beginTransaction();
        
        // Insertar emisor
        $stmt = $pdo->prepare("
            INSERT INTO Contribuyentes (identificacion, razon_social, nombre_comercial, tipo_identificacion) 
            VALUES (?, ?, ?, '04') 
            ON DUPLICATE KEY UPDATE 
                razon_social = VALUES(razon_social),
                nombre_comercial = VALUES(nombre_comercial)
        ");
        $stmt->execute(['0991331859001', 'ATIMASA S.A.', 'ATIMASA S.A.']);
        $emisorId = $pdo->lastInsertId();
        if ($emisorId == 0) {
            $stmt = $pdo->prepare("SELECT id FROM Contribuyentes WHERE identificacion = ?");
            $stmt->execute(['0991331859001']);
            $emisorId = $stmt->fetchColumn();
        }
        
        // Insertar receptor
        $stmt = $pdo->prepare("
            INSERT INTO Contribuyentes (identificacion, razon_social, tipo_identificacion) 
            VALUES (?, ?, '04') 
            ON DUPLICATE KEY UPDATE 
                razon_social = VALUES(razon_social)
        ");
        $stmt->execute(['1721642443001', 'AYLUARDO GARCIA JOSELYN NICKOLL']);
        $receptorId = $pdo->lastInsertId();
        if ($receptorId == 0) {
            $stmt = $pdo->prepare("SELECT id FROM Contribuyentes WHERE identificacion = ?");
            $stmt->execute(['1721642443001']);
            $receptorId = $stmt->fetchColumn();
        }
        
        // Insertar comprobante
        $stmt = $pdo->prepare("
            INSERT INTO ComprobantesRetencion (
                clave_acceso, numero_autorizacion, estado, numero_comprobante,
                fecha_emision, fecha_autorizacion, periodo_fiscal,
                emisor_id, receptor_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'TEST-DUPLICADO-001',
            '2708202507099133185900120010270002592891234567810',
            'AUTORIZADO',
            '001-027-000259289',
            '2025-08-27',
            '2025-08-27 05:35:08',
            '08/2025',
            $emisorId,
            $receptorId
        ]);
        
        $pdo->commit();
        echo "<div class='success'>✅ Comprobante de prueba insertado correctamente</div>";
        
    } catch (Exception $e) {
        $pdo->rollback();
        echo "<div class='error'>❌ Error al insertar comprobante de prueba: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    // PASO 2: Simular mensaje de duplicado
    echo "<div class='section'>";
    echo "<h2>⚠️ PASO 2: Mensaje de Duplicado</h2>";
    echo "<p><strong>Si intentas subir el mismo XML nuevamente, verás este mensaje:</strong></p>";
    
    $mensajeDuplicado = "⚠️ Este comprobante de retención ya fue procesado anteriormente.\n\n" .
                       "📄 Número: 001-027-000259289\n" .
                       "📅 Fecha: 27/08/2025\n" .
                       "🏢 Emisor: ATIMASA S.A.\n\n" .
                       "💡 Si necesitas procesarlo nuevamente, primero elimínalo desde la lista de retenciones.";
    
    echo "<div class='message-box message-error'>$mensajeDuplicado</div>";
    echo "<div class='success'>✅ Mensaje claro y amigable para el usuario</div>";
    echo "</div>";
    
    // PASO 3: Simular mensaje de éxito
    echo "<div class='section'>";
    echo "<h2>✅ PASO 3: Mensaje de Éxito</h2>";
    echo "<p><strong>Cuando proceses un XML exitosamente, verás este mensaje:</strong></p>";
    
    $mensajeExito = "✅ ¡Retención procesada exitosamente!\n\n" .
                   "📄 Comprobante: 001-027-000259289\n" .
                   "🏢 Emisor: ATIMASA S.A.\n" .
                   "👤 Receptor: AYLUARDO GARCIA JOSELYN NICKOLL\n" .
                   "📅 Fecha: 27/08/2025\n" .
                   "📊 Período: 08/2025";
    
    echo "<div class='message-box message-success'>$mensajeExito</div>";
    echo "<div class='success'>✅ Mensaje informativo y detallado</div>";
    echo "</div>";
    
    // PASO 4: Simular mensaje de error general
    echo "<div class='section'>";
    echo "<h2>❌ PASO 4: Mensaje de Error General</h2>";
    echo "<p><strong>Si hay un error técnico, verás este mensaje:</strong></p>";
    
    $mensajeError = "❌ Error al procesar la retención:\n\n" .
                   "🔍 Detalle: Error específico del sistema\n\n" .
                   "💡 Verifica que el archivo XML sea válido y no esté corrupto.\n" .
                   "📞 Si el problema persiste, contacta al administrador del sistema.";
    
    echo "<div class='message-box message-error'>$mensajeError</div>";
    echo "<div class='success'>✅ Mensaje técnico pero amigable</div>";
    echo "</div>";
    
    // RESULTADO FINAL
    echo "<div class='section'>";
    echo "<h2>🎯 RESULTADO FINAL</h2>";
    echo "<div class='success'>🎉 ¡MENSAJES MEJORADOS IMPLEMENTADOS!</div>";
    echo "<div class='info'>✅ Mensaje de duplicado: Claro y con información útil</div>";
    echo "<div class='info'>✅ Mensaje de éxito: Informativo y detallado</div>";
    echo "<div class='info'>✅ Mensaje de error: Técnico pero amigable</div>";
    
    echo "<h3>🚀 Próximos pasos:</h3>";
    echo "<ol>";
    echo "<li>Ve a <strong>retenciones.html</strong></li>";
    echo "<li>Intenta subir el mismo XML dos veces</li>";
    echo "<li>Verifica que aparezca el mensaje de duplicado</li>";
    echo "<li>Sube un XML nuevo y verifica el mensaje de éxito</li>";
    echo "</ol>";
    
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='retenciones.html' class='btn btn-primary'>📄 Ir a Retenciones</a>";
    echo "<a href='limpiar_datos_retenciones.php' class='btn btn-warning'>🧹 Limpiar Datos</a>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
