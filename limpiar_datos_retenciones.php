<?php
/**
 * Script para limpiar datos de retenciones
 * Mantiene las tablas catálogo (TiposImpuesto, CodigosRetencion) intactas
 */

require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Limpiar Datos de Retenciones</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .info { color: #17a2b8; font-weight: bold; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; background: #f8f9fa; }
    .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; }
    .btn-danger { background: #dc3545; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-info { background: #17a2b8; color: white; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🧹 Limpiar Datos de Retenciones</h1>";
echo "<p><strong>Este script limpiará solo los datos de retenciones, manteniendo las tablas catálogo intactas.</strong></p>";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("❌ Error: No se pudo conectar a la base de datos");
    }
    
    echo "<div class='success'>✅ Conexión establecida correctamente</div>";
    echo "<div class='info'>📊 Base de datos: " . DB_NAME . "</div><br>";
    
    // Verificar estado actual
    echo "<div class='section'>";
    echo "<h2>📊 Estado Actual de las Tablas</h2>";
    
    $tablasRetenciones = ['ComprobantesRetencion', 'Contribuyentes', 'DetalleRetenciones', 'DocumentosSustento'];
    $tablasCatalogo = ['TiposImpuesto', 'CodigosRetencion'];
    
    echo "<h3>📋 Tablas de Retenciones:</h3>";
    foreach ($tablasRetenciones as $tabla) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<div class='info'>📊 $tabla: {$count['total']} registros</div>";
    }
    
    echo "<h3>📚 Tablas Catálogo (se mantendrán):</h3>";
    foreach ($tablasCatalogo as $tabla) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<div class='success'>📚 $tabla: {$count['total']} registros (se mantienen)</div>";
    }
    echo "</div>";
    
    // Mostrar botón de confirmación
    echo "<div class='section'>";
    echo "<h2>⚠️ Confirmación Requerida</h2>";
    echo "<p><strong>Esta acción eliminará TODOS los datos de retenciones:</strong></p>";
    echo "<ul>";
    echo "<li>❌ ComprobantesRetencion</li>";
    echo "<li>❌ Contribuyentes</li>";
    echo "<li>❌ DetalleRetenciones</li>";
    echo "<li>❌ DocumentosSustento</li>";
    echo "</ul>";
    echo "<p><strong>Se mantendrán intactas:</strong></p>";
    echo "<ul>";
    echo "<li>✅ TiposImpuesto</li>";
    echo "<li>✅ CodigosRetencion</li>";
    echo "</ul>";
    
    if (isset($_POST['confirmar']) && $_POST['confirmar'] === 'SI') {
        // Proceder con la limpieza
        echo "<div class='warning'>🔄 Iniciando limpieza...</div>";
        
        try {
            $pdo->beginTransaction();
            
            // Limpiar en orden correcto (respetando foreign keys)
            $ordenLimpieza = [
                'DetalleRetenciones',
                'DocumentosSustento', 
                'ComprobantesRetencion',
                'Contribuyentes'
            ];
            
            foreach ($ordenLimpieza as $tabla) {
                $stmt = $pdo->prepare("DELETE FROM `$tabla`");
                $stmt->execute();
                $filasEliminadas = $stmt->rowCount();
                echo "<div class='success'>✅ $tabla: $filasEliminadas registros eliminados</div>";
            }
            
            // Resetear auto_increment
            foreach ($ordenLimpieza as $tabla) {
                $stmt = $pdo->prepare("ALTER TABLE `$tabla` AUTO_INCREMENT = 1");
                $stmt->execute();
                echo "<div class='info'>🔄 $tabla: Auto_increment reseteado</div>";
            }
            
            $pdo->commit();
            
            echo "<div class='success'>🎉 ¡Limpieza completada exitosamente!</div>";
            echo "<div class='info'>📊 Todas las tablas de retenciones están ahora vacías</div>";
            echo "<div class='success'>✅ Las tablas catálogo se mantuvieron intactas</div>";
            
            // Verificar estado final
            echo "<h3>📊 Estado Final:</h3>";
            foreach ($tablasRetenciones as $tabla) {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<div class='success'>✅ $tabla: {$count['total']} registros</div>";
            }
            
            echo "<h3>🚀 Próximos pasos:</h3>";
            echo "<ol>";
            echo "<li>Ve a <strong>retenciones.html</strong></li>";
            echo "<li>Selecciona tu archivo XML de retención</li>";
            echo "<li>Confirma los datos en el modal</li>";
            echo "<li>Verifica que se guarde correctamente</li>";
            echo "</ol>";
            
        } catch (Exception $e) {
            $pdo->rollback();
            echo "<div class='error'>❌ Error durante la limpieza: " . $e->getMessage() . "</div>";
        }
        
    } else {
        // Mostrar formulario de confirmación
        echo "<form method='POST'>";
        echo "<p><strong>¿Estás seguro de que quieres continuar?</strong></p>";
        echo "<input type='hidden' name='confirmar' value='SI'>";
        echo "<button type='submit' class='btn btn-danger'>🗑️ SÍ, LIMPIAR DATOS</button>";
        echo "</form>";
        
        echo "<p><strong>O puedes:</strong></p>";
        echo "<a href='retenciones.html' class='btn btn-info'>📄 Ir a Retenciones</a>";
        echo "<a href='prueba_final_sistema.php' class='btn btn-success'>🧪 Ejecutar Prueba</a>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
