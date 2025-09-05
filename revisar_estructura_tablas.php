<?php
/**
 * Script para revisar la estructura de las tablas de retenciones
 * SOLO LECTURA - NO MODIFICA NADA
 */

require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Revisión Estructura Tablas</title>";
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
echo "<h1>🔍 Revisión de Estructura de Tablas de Retenciones</h1>";
echo "<p><strong>⚠️ SOLO LECTURA - NO SE MODIFICA NADA</strong></p>";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("❌ Error: No se pudo conectar a la base de datos");
    }
    
    echo "<div class='success'>✅ Conexión establecida correctamente</div>";
    echo "<div class='info'>📊 Base de datos: " . DB_NAME . " | Host: " . DB_HOST . "</div><br>";
    
    // Lista de tablas a revisar
    $tablas = [
        'ComprobantesRetencion',
        'Contribuyentes', 
        'DetalleRetenciones',
        'DocumentosSustento'
    ];
    
    echo "<div class='section'>";
    echo "<h2>📋 Estructura de las Tablas</h2>";
    
    foreach ($tablas as $tabla) {
        echo "<h3>📊 Tabla: <strong>$tabla</strong></h3>";
        
        // Verificar si la tabla existe
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "<div class='success'>✅ Tabla '$tabla' existe</div>";
            
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
    
    // Verificar relaciones (claves foráneas)
    echo "<div class='section'>";
    echo "<h2>🔗 Relaciones entre Tablas (Claves Foráneas)</h2>";
    
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            REFERENCED_TABLE_SCHEMA = '" . DB_NAME . "' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
            AND TABLE_NAME IN ('ComprobantesRetencion', 'Contribuyentes', 'DetalleRetenciones', 'DocumentosSustento')
    ");
    
    $relaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($relaciones)) {
        echo "<div class='warning'>⚠️ No se encontraron claves foráneas entre las tablas</div>";
        echo "<div class='info'>💡 Esto podría indicar que las relaciones no están definidas formalmente</div>";
    } else {
        echo "<table>";
        echo "<tr><th>Tabla</th><th>Columna</th><th>Referencia</th><th>Tabla Referenciada</th><th>Columna Referenciada</th></tr>";
        foreach ($relaciones as $relacion) {
            echo "<tr>";
            echo "<td>{$relacion['TABLE_NAME']}</td>";
            echo "<td>{$relacion['COLUMN_NAME']}</td>";
            echo "<td>{$relacion['CONSTRAINT_NAME']}</td>";
            echo "<td>{$relacion['REFERENCED_TABLE_NAME']}</td>";
            echo "<td>{$relacion['REFERENCED_COLUMN_NAME']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // Análisis de compatibilidad con XML
    echo "<div class='section'>";
    echo "<h2>🔍 Análisis de Compatibilidad con XML</h2>";
    
    echo "<h3>📋 Datos que extraemos del XML de retención:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Autorización:</h4>";
    echo "<ul><li>estado: 'AUTORIZADO'</li><li>numeroAutorizacion: '2708202507099133185900120010270002592891234567810'</li><li>fechaAutorizacion: '2025-08-27T05:35:08-05:00'</li><li>ambiente: 'PRODUCCIÓN'</li></ul>";
    
    echo "<h4>Emisor:</h4>";
    echo "<ul><li>ruc: '0991331859001'</li><li>razonSocial: 'ATIMASA S.A.'</li><li>estab: '001'</li><li>ptoEmi: '027'</li><li>secuencial: '000259289'</li></ul>";
    
    echo "<h4>Sujeto Retenido:</h4>";
    echo "<ul><li>ruc: '1721642443001'</li><li>razonSocial: 'AYLUARDO GARCIA JOSELYN NICKOLL'</li><li>periodoFiscal: '08/2025'</li></ul>";
    
    echo "<h4>Documento Sustentante:</h4>";
    echo "<ul><li>numero: '002100000001916'</li><li>fechaEmision: '26/08/2025'</li><li>totalSinImpuestos: '57.48'</li><li>importeTotal: '66.1'</li></ul>";
    
    echo "<h4>Retenciones:</h4>";
    echo "<ul><li>Retención 1: codigoRetencion='312', baseImponible='57.48', porcentajeRetener='1.7500', valorRetenido='1.01'</li><li>Retención 2: codigoRetencion='1', baseImponible='8.62', porcentajeRetener='30.0000', valorRetenido='2.59'</li></ul>";
    echo "</div>";
    
    echo "<h3>🎯 Mapeo sugerido a las tablas:</h3>";
    echo "<ul>";
    echo "<li><strong>ComprobantesRetencion:</strong> numeroAutorizacion, fechaAutorizacion, estado, ambiente</li>";
    echo "<li><strong>Contribuyentes:</strong> ruc, razonSocial (tanto emisor como receptor)</li>";
    echo "<li><strong>DocumentosSustento:</strong> numero, fechaEmision, totalSinImpuestos, importeTotal</li>";
    echo "<li><strong>DetalleRetenciones:</strong> codigoRetencion, baseImponible, porcentajeRetener, valorRetenido</li>";
    echo "</ul>";
    echo "</div>";
    
    // Recomendaciones
    echo "<div class='section'>";
    echo "<h2>💡 Recomendaciones</h2>";
    
    echo "<h3>✅ Verificaciones necesarias:</h3>";
    echo "<ol>";
    echo "<li><strong>Campos de ID:</strong> Verificar que cada tabla tenga un campo ID auto-incremental</li>";
    echo "<li><strong>Tipos de datos:</strong> Verificar que los tipos coincidan con los datos del XML</li>";
    echo "<li><strong>Longitud de campos:</strong> Verificar que VARCHAR tenga suficiente longitud</li>";
    echo "<li><strong>Campos requeridos:</strong> Verificar que los campos importantes no permitan NULL</li>";
    echo "<li><strong>Relaciones:</strong> Verificar que las claves foráneas estén correctamente definidas</li>";
    echo "</ol>";
    
    echo "<h3>🔧 Próximos pasos:</h3>";
    echo "<ol>";
    echo "<li>Revisar la estructura mostrada arriba</li>";
    echo "<li>Identificar campos faltantes o incorrectos</li>";
    echo "<li>Ajustar el código de inserción si es necesario</li>";
    echo "<li>Probar con datos de ejemplo</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "<br><div class='info'>🔍 Revisión completada. Analiza los resultados antes de proceder.</div>";
echo "</div></body></html>";
?>
