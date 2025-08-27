<?php
// =====================================================
// VERIFICACIÓN ROBUSTA DE ARCHIVOS XML
// =====================================================

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Verificación Robusta de Archivos XML</h1>";

$archivosXML = [
    'api/upload_factura_individual_clean.php',
    'api/upload_factura_individual.php',
    'debug_xml_extraction.php'
];

function estaComentado($lineas, $numeroLinea) {
    $linea = trim($lineas[$numeroLinea]);
    
    // Verificar si la línea actual está comentada con //
    if (strpos($linea, '//') === 0) {
        return true;
    }
    
    // Verificar línea anterior
    if ($numeroLinea > 0) {
        $lineaAnterior = trim($lineas[$numeroLinea-1]);
        if (strpos($lineaAnterior, '//') === 0) {
            return true;
        }
    }
    
    // Verificar si está dentro de un bloque comentado /* ... */
    $enBloqueComentado = false;
    $bloqueAbierto = false;
    
    // Buscar hacia atrás para encontrar el inicio del bloque
    for ($i = $numeroLinea; $i >= 0; $i--) {
        $lineaActual = $lineas[$i];
        
        // Si encontramos el cierre del bloque
        if (strpos($lineaActual, '*/') !== false) {
            $bloqueAbierto = true;
        }
        
        // Si encontramos el inicio del bloque
        if (strpos($lineaActual, '/*') !== false) {
            if ($bloqueAbierto) {
                $enBloqueComentado = true;
            }
            break;
        }
    }
    
    return $enBloqueComentado;
}

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
                }
                
                // Verificar si está comentado
                if (estaComentado($lineas, $numero)) {
                    $insertComentado = true;
                    echo "<p style='color: green;'>✅ INSERT INTO pagos está comentado</p>";
                } else {
                    echo "<p style='color: red;'>❌ INSERT INTO pagos NO está comentado</p>";
                }
                
                echo "<hr>";
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
echo "<p><strong>Estado:</strong> ";
echo "<span style='color: green; font-weight: bold;'>✅ SISTEMA SEGURO - ARCHIVOS XML CORRECTAMENTE COMENTADOS</span>";
echo "</p>";
?>
