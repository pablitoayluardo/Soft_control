<?php
// =====================================================
// TEST SIMPLE DE RETENCIONES - HOSTING
// =====================================================

// Configuración de la base de datos
require_once 'config.php';

try {
    // Inicializar conexión a BD
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<h2>🔍 Verificación Simple de Tablas de Retenciones</h2>\n";
    
    // Verificar que las tablas existen usando consulta directa
    $tablas = ['rete_cabe', 'rete_deta'];
    
    foreach ($tablas as $tabla) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
            $existe = $stmt->fetch();
            
            if ($existe) {
                echo "<p style='color: green;'>✅ Tabla <strong>$tabla</strong> existe</p>\n";
                
                // Contar registros
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
                $resultado = $stmt->fetch();
                echo "<p><strong>$tabla:</strong> {$resultado['total']} registros</p>\n";
                
                // Mostrar estructura básica
                $stmt = $pdo->query("DESCRIBE $tabla");
                $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<p><strong>Campos de $tabla:</strong></p>\n";
                echo "<ul>\n";
                foreach ($columnas as $columna) {
                    echo "<li>{$columna['Field']} ({$columna['Type']})</li>\n";
                }
                echo "</ul>\n";
            } else {
                echo "<p style='color: red;'>❌ Tabla <strong>$tabla</strong> NO existe</p>\n";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error verificando tabla <strong>$tabla</strong>: " . $e->getMessage() . "</p>\n";
        }
    }
    
    // Verificar que no existe rete_pagos
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'rete_pagos'");
        $existeRetePagos = $stmt->fetch();
        
        if (!$existeRetePagos) {
            echo "<p style='color: green;'>✅ Tabla <strong>rete_pagos</strong> NO existe (correcto)</p>\n";
        } else {
            echo "<p style='color: orange;'>⚠️ Tabla <strong>rete_pagos</strong> existe (no debería)</p>\n";
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ No se pudo verificar rete_pagos: " . $e->getMessage() . "</p>\n";
    }
    
    // Probar consulta simple de JOIN
    echo "<h3>🧪 Prueba de consulta JOIN:</h3>\n";
    
    try {
        $query = "
            SELECT 
                rc.id,
                rc.numero_autorizacion,
                rc.ambiente,
                rc.razon_social_emisor,
                rc.razon_social_retenido,
                COUNT(rd.id) as total_detalles
            FROM rete_cabe rc
            LEFT JOIN rete_deta rd ON rc.id = rd.id_rete_cabe
            GROUP BY rc.id
            LIMIT 5
        ";
        
        $stmt = $pdo->query($query);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($resultados)) {
            echo "<p style='color: orange;'>ℹ️ No hay registros en las tablas de retenciones</p>\n";
        } else {
            echo "<p style='color: green;'>✅ Consulta JOIN ejecutada correctamente</p>\n";
            echo "<p>Se encontraron " . count($resultados) . " registros</p>\n";
            
            // Mostrar algunos datos de ejemplo
            echo "<p><strong>Datos de ejemplo:</strong></p>\n";
            echo "<ul>\n";
            foreach (array_slice($resultados, 0, 3) as $row) {
                echo "<li>ID: {$row['id']} - Autorización: {$row['numero_autorizacion']} - Ambiente: {$row['ambiente']}</li>\n";
            }
            echo "</ul>\n";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en consulta JOIN: " . $e->getMessage() . "</p>\n";
    }
    
    // Probar inserción de datos de prueba
    echo "<h3>🧪 Prueba de inserción:</h3>\n";
    
    try {
        // Verificar si ya existe un registro de prueba
        $stmt = $pdo->prepare("SELECT id FROM rete_cabe WHERE numero_autorizacion = 'TEST-001'");
        $stmt->execute();
        $existeTest = $stmt->fetch();
        
        if (!$existeTest) {
            // Insertar registro de prueba en rete_cabe
            $stmt = $pdo->prepare("
                INSERT INTO rete_cabe (
                    numero_autorizacion, fecha_autorizacion, ambiente, tipo_emision,
                    ruc_emisor, razon_social_emisor, nombre_comercial_emisor, clave_acceso,
                    establecimiento, punto_emision, secuencial, fecha_emision,
                    periodo_fiscal, razon_social_retenido, identificacion_retenido,
                    tipo_identificacion, cod_doc
                ) VALUES (
                    'TEST-001', '2024-01-15', '1', 1,
                    '1234567890001', 'EMPRESA DE PRUEBA', 'EMPRESA DE PRUEBA', 'TEST-KEY-001',
                    '001', '001', '000000001', '2024-01-15',
                    '01/2024', 'SUJETO DE PRUEBA', '9876543210001',
                    '04', '07'
                )
            ");
            $stmt->execute();
            $idTest = $pdo->lastInsertId();
            
            echo "<p style='color: green;'>✅ Registro de prueba insertado en rete_cabe (ID: $idTest)</p>\n";
            
            // Insertar detalle de prueba
            $stmt = $pdo->prepare("
                INSERT INTO rete_deta (
                    id_rete_cabe, cod_sustento, cod_doc_sustento, num_doc_sustento,
                    fecha_emision_doc_sustento, codigo_retencion, base_imponible,
                    porcentaje_retener, valor_retenido
                ) VALUES (
                    ?, '01', '01', '002100000001916',
                    '2024-01-15', '1', 1000.00, 12.00, 120.00
                )
            ");
            $stmt->execute([$idTest]);
            
            echo "<p style='color: green;'>✅ Detalle de prueba insertado en rete_deta</p>\n";
        } else {
            echo "<p style='color: orange;'>ℹ️ Registro de prueba ya existe</p>\n";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en inserción de prueba: " . $e->getMessage() . "</p>\n";
    }
    
    echo "<h3>🎉 Verificación completada</h3>\n";
    echo "<p>El sistema está listo para procesar retenciones.</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de base de datos: " . $e->getMessage() . "</p>\n";
}
?>
