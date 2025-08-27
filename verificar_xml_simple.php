<?php
// =====================================================
// VERIFICACIÓN SIMPLE DE ARCHIVOS XML
// =====================================================

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Verificación Simple de Archivos XML</h1>";

$archivosXML = [
    'api/upload_factura_individual_clean.php',
    'api/upload_factura_individual.php',
    'debug_xml_extraction.php'
];

foreach ($archivosXML as $archivo) {
    echo "<h2>📁 Verificando: $archivo</h2>";
    
    if (file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        $lineas = explode("\n", $contenido);
        
        $insertEncontrado = false;
        $insertComentado = false;
        
        foreach ($lineas as $numero => $linea) {
            if (strpos($linea, 'INSERT INTO pagos') !== false) {
                $insertEncontrado = true;
                echo "<p><strong>Línea $numero:</strong> " . htmlspecialchars(trim($linea)) . "</p>";
                
                // Verificar línea anterior
                if ($numero > 0) {
                    $lineaAnterior = trim($lineas[$numero-1]);
                    echo "<p><strong>Línea anterior:</strong> " . htmlspecialchars($lineaAnterior) . "</p>";
                    
                    if (strpos($lineaAnterior, '//') === 0 || strpos($lineaAnterior, '/*') !== false) {
                        $insertComentado = true;
                        echo "<p style='color: green;'>✅ Comentado por línea anterior</p>";
                    }
                }
                
                // Verificar línea actual
                if (strpos(trim($linea), '//') === 0) {
                    $insertComentado = true;
                    echo "<p style='color: green;'>✅ Comentado en línea actual</p>";
                }
                
                // Verificar bloque comentado
                $enBloque = false;
                $bloqueAbierto = false;
                for ($i = $numero; $i >= 0; $i--) {
                    if (strpos($lineas[$i], '*/') !== false) {
                        $bloqueAbierto = true;
                    }
                    if (strpos($lineas[$i], '/*') !== false) {
                        if ($bloqueAbierto) {
                            $enBloque = true;
                            $insertComentado = true;
                            echo "<p style='color: green;'>✅ Dentro de bloque comentado /* ... */</p>";
                        }
                        break;
                    }
                }
            }
        }
        
        if ($insertEncontrado) {
            if ($insertComentado) {
                echo "<p style='color: green; font-weight: bold;'>✅ SEGURO: INSERT INTO pagos está comentado</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>🚨 CRÍTICO: INSERT INTO pagos NO está comentado</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ No se encontró INSERT INTO pagos</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Archivo no existe</p>";
    }
    
    echo "<hr>";
}

echo "<h2>🎯 Resumen</h2>";
echo "<p>Si todos los archivos muestran 'SEGURO' o 'No se encontró', entonces el sistema está correctamente separado.</p>";
?>
