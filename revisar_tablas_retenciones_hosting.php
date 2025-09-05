<?php
/**
 * Script simple para revisar las tablas de retenciones en el hosting
 * Ejecutar directamente en el navegador: https://www.globocity.com.ec/soft_control/revisar_tablas_retenciones_hosting.php
 */

require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Revisión Tablas Retenciones</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .info { color: #17a2b8; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f8f9fa; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; background: #f8f9fa; }
    h1 { color: #343a40; }
    h2 { color: #495057; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🔍 Revisión de Tablas de Retenciones - Hosting</h1>";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("❌ Error: No se pudo conectar a la base de datos");
    }
    
    echo "<div class='success'>✅ Conexión establecida correctamente</div>";
    echo "<div class='info'>📊 Base de datos: " . DB_NAME . " | Host: " . DB_HOST . "</div><br>";
    
    // Verificar las tablas específicas mencionadas
    $tablasEsperadas = [
        'ComprobantesRetencion',
        'Contribuyentes', 
        'DetalleRetenciones',
        'DocumentosSustento'
    ];
    
    echo "<div class='section'>";
    echo "<h2>🎯 Verificación de Tablas</h2>";
    
    $tablasExistentes = [];
    
    foreach ($tablasEsperadas as $tabla) {
        echo "<h3>📊 Tabla: $tabla</h3>";
        
        // Verificar si la tabla existe
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "<div class='success'>✅ Tabla '$tabla' existe</div>";
            $tablasExistentes[] = $tabla;
            
            // Mostrar estructura de la tabla
            $stmt = $pdo->query("DESCRIBE `$tabla`");
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th><th>Extra</th></tr>";
            foreach ($columnas as $columna) {
                echo "<tr>";
                echo "<td><strong>{$columna['Field']}</strong></td>";
                echo "<td>{$columna['Type']}</td>";
                echo "<td>{$columna['Null']}</td>";
                echo "<td>{$columna['Key']}</td>";
                echo "<td>{$columna['Default']}</td>";
                echo "<td>{$columna['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='info'>📈 Total de registros: {$count['total']}</div>";
            
        } else {
            echo "<div class='error'>❌ Tabla '$tabla' NO existe</div>";
        }
        echo "<br>";
    }
    echo "</div>";
    
    // Análisis de compatibilidad con nuestro XML
    echo "<div class='section'>";
    echo "<h2>🔍 Análisis de Compatibilidad con XML</h2>";
    
    // Datos que extraemos del XML
    $datosXML = [
        'autorizacion' => [
            'estado' => 'AUTORIZADO',
            'numeroAutorizacion' => '2708202507099133185900120010270002592891234567810',
            'fechaAutorizacion' => '2025-08-27T05:35:08-05:00',
            'ambiente' => 'PRODUCCIÓN'
        ],
        'emisor' => [
            'razonSocial' => 'ATIMASA S.A.',
            'ruc' => '0991331859001',
            'estab' => '001',
            'ptoEmi' => '027',
            'secuencial' => '000259289'
        ],
        'sujetoRetenido' => [
            'razonSocial' => 'AYLUARDO GARCIA JOSELYN NICKOLL',
            'identificacion' => '1721642443001',
            'periodoFiscal' => '08/2025'
        ],
        'documentoSustento' => [
            'numero' => '002100000001916',
            'fechaEmision' => '26/08/2025',
            'totalSinImpuestos' => '57.48',
            'importeTotal' => '66.1'
        ],
        'retenciones' => [
            [
                'codigo' => '1',
                'codigoRetencion' => '312',
                'baseImponible' => '57.48',
                'porcentajeRetener' => '1.7500',
                'valorRetenido' => '1.01'
            ],
            [
                'codigo' => '2',
                'codigoRetencion' => '1',
                'baseImponible' => '8.62',
                'porcentajeRetener' => '30.0000',
                'valorRetenido' => '2.59'
            ]
        ]
    ];
    
    echo "<h3>📋 Datos que extraemos del XML:</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars(print_r($datosXML, true));
    echo "</pre>";
    
    echo "<h3>🎯 Mapeo sugerido a las tablas:</h3>";
    echo "<ul>";
    echo "<li><strong>ComprobantesRetencion:</strong> estado, numeroAutorizacion, fechaAutorizacion, ambiente</li>";
    echo "<li><strong>Contribuyentes:</strong> razonSocial, ruc (tanto emisor como receptor)</li>";
    echo "<li><strong>DocumentosSustento:</strong> numero, fechaEmision, totalSinImpuestos, importeTotal</li>";
    echo "<li><strong>DetalleRetenciones:</strong> codigoRetencion, baseImponible, porcentajeRetener, valorRetenido</li>";
    echo "</ul>";
    echo "</div>";
    
    // Recomendaciones
    echo "<div class='section'>";
    echo "<h2>💡 Recomendaciones</h2>";
    
    if (count($tablasExistentes) == 4) {
        echo "<div class='success'>✅ Todas las tablas necesarias están creadas</div>";
        echo "<div class='info'>📝 Próximo paso: Verificar que las columnas coincidan con los datos del XML</div>";
        echo "<div class='info'>🔧 Si las columnas no coinciden, necesitaremos ajustar el código de inserción</div>";
    } else {
        echo "<div class='warning'>⚠️ Faltan algunas tablas. Tablas encontradas: " . implode(', ', $tablasExistentes) . "</div>";
    }
    
    echo "<h3>🔧 Acciones sugeridas:</h3>";
    echo "<ol>";
    echo "<li>Verificar que las columnas de las tablas coincidan con los datos del XML</li>";
    echo "<li>Si hay diferencias, ajustar el código de inserción en <code>api/procesar_retencion_limpio.php</code></li>";
    echo "<li>Probar la inserción con datos de prueba</li>";
    echo "<li>Verificar las relaciones entre tablas (claves foráneas)</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "<br><div class='info'>🔍 Revisión completada. Analiza los resultados antes de proceder.</div>";
echo "</div></body></html>";
?>
