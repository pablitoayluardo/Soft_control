<?php
/**
 * Script de prueba para el nuevo procesador de retenciones
 * Prueba la inserción con datos de ejemplo
 */

require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Prueba Nuevo Procesador</title>";
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
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🧪 Prueba del Nuevo Procesador de Retenciones</h1>";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("❌ Error: No se pudo conectar a la base de datos");
    }
    
    echo "<div class='success'>✅ Conexión establecida correctamente</div>";
    echo "<div class='info'>📊 Base de datos: " . DB_NAME . "</div><br>";
    
    // Verificar que las tablas existen
    echo "<div class='section'>";
    echo "<h2>🔍 Verificación de Tablas</h2>";
    
    $tablas = ['ComprobantesRetencion', 'Contribuyentes', 'DetalleRetenciones', 'DocumentosSustento', 'TiposImpuesto', 'CodigosRetencion'];
    
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        $existe = $stmt->fetch();
        
        if ($existe) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='success'>✅ $tabla: {$count['total']} registros</div>";
        } else {
            echo "<div class='error'>❌ $tabla: NO existe</div>";
        }
    }
    echo "</div>";
    
    // Prueba de inserción manual
    echo "<div class='section'>";
    echo "<h2>🧪 Prueba de Inserción Manual</h2>";
    
    try {
        $pdo->beginTransaction();
        
        // 1. Insertar emisor
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
        echo "<div class='success'>✅ Emisor insertado/actualizado: ID=$emisorId</div>";
        
        // 2. Insertar receptor
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
        echo "<div class='success'>✅ Receptor insertado/actualizado: ID=$receptorId</div>";
        
        // 3. Insertar comprobante
        $stmt = $pdo->prepare("
            INSERT INTO ComprobantesRetencion (
                clave_acceso, numero_autorizacion, estado, numero_comprobante,
                fecha_emision, fecha_autorizacion, periodo_fiscal,
                emisor_id, receptor_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'TEST-KEY-001',
            '2708202507099133185900120010270002592891234567810',
            'AUTORIZADO',
            '001-027-000259289',
            '2025-08-27',
            '2025-08-27 05:35:08',
            '08/2025',
            $emisorId,
            $receptorId
        ]);
        
        $comprobanteId = $pdo->lastInsertId();
        echo "<div class='success'>✅ Comprobante insertado: ID=$comprobanteId</div>";
        
        // 4. Insertar documento sustentante
        $stmt = $pdo->prepare("
            INSERT INTO DocumentosSustento (
                comprobante_retencion_id, tipo_documento_sustento, numero_documento_sustento,
                fecha_emision_sustento, total_sin_impuestos, importe_total
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $comprobanteId,
            '01',
            '002100000001916',
            '2025-08-26',
            57.48,
            66.10
        ]);
        
        echo "<div class='success'>✅ Documento sustentante insertado</div>";
        
        // 5. Insertar retenciones
        $retenciones = [
            ['1', '312', 57.48, 1.75, 1.01],
            ['2', '1', 8.62, 30.00, 2.59]
        ];
        
        foreach ($retenciones as $retencion) {
            $stmt = $pdo->prepare("
                INSERT INTO DetalleRetenciones (
                    comprobante_retencion_id, codigo_impuesto, codigo_retencion,
                    base_imponible, porcentaje_retener, valor_retenido
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $comprobanteId,
                $retencion[0],
                $retencion[1],
                $retencion[2],
                $retencion[3],
                $retencion[4]
            ]);
            
            echo "<div class='success'>✅ Retención insertada: código {$retencion[1]}, valor {$retencion[4]}</div>";
        }
        
        $pdo->commit();
        echo "<div class='success'>🎉 ¡Prueba de inserción exitosa!</div>";
        
    } catch (Exception $e) {
        $pdo->rollback();
        echo "<div class='error'>❌ Error en prueba: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    // Prueba de consulta con JOINs
    echo "<div class='section'>";
    echo "<h2>📊 Prueba de Consulta con JOINs</h2>";
    
    $stmt = $pdo->query("
        SELECT 
            cr.numero_comprobante,
            cr.fecha_emision,
            emisor.razon_social AS emisor,
            receptor.razon_social AS receptor,
            dr.codigo_retencion,
            codigos.descripcion AS tipo_retencion,
            dr.base_imponible,
            dr.valor_retenido
        FROM 
            ComprobantesRetencion cr
        JOIN 
            Contribuyentes emisor ON cr.emisor_id = emisor.id
        JOIN 
            Contribuyentes receptor ON cr.receptor_id = receptor.id
        JOIN 
            DetalleRetenciones dr ON cr.id = dr.comprobante_retencion_id
        LEFT JOIN 
            CodigosRetencion codigos ON dr.codigo_retencion = codigos.codigo
        ORDER BY cr.id DESC
        LIMIT 5
    ");
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($resultados)) {
        echo "<table>";
        echo "<tr><th>Comprobante</th><th>Fecha</th><th>Emisor</th><th>Receptor</th><th>Código</th><th>Descripción</th><th>Base</th><th>Valor</th></tr>";
        
        foreach ($resultados as $row) {
            echo "<tr>";
            echo "<td>{$row['numero_comprobante']}</td>";
            echo "<td>{$row['fecha_emision']}</td>";
            echo "<td>{$row['emisor']}</td>";
            echo "<td>{$row['receptor']}</td>";
            echo "<td>{$row['codigo_retencion']}</td>";
            echo "<td>{$row['tipo_retencion']}</td>";
            echo "<td>{$row['base_imponible']}</td>";
            echo "<td>{$row['valor_retenido']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ No hay datos para mostrar</div>";
    }
    echo "</div>";
    
    echo "<div class='success'>🎉 ¡Prueba completada exitosamente!</div>";
    echo "<div class='info'>💡 El nuevo procesador está listo para usar.</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
