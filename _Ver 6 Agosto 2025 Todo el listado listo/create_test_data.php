<?php
// =====================================================
// CREAR DATOS DE PRUEBA PARA FACTURACIÓN
// =====================================================

// Configuración directa de base de datos
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';
$charset = 'utf8mb4';

echo "<h2>🧪 Creando Datos de Prueba</h2>";

try {
    // Conexión directa a la base de datos
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // Verificar si ya hay datos
    $sql = "SELECT COUNT(*) as total FROM info_factura";
    $stmt = $pdo->query($sql);
    $countFactura = $stmt->fetch()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM info_tributaria";
    $stmt = $pdo->query($sql);
    $countTributaria = $stmt->fetch()['total'];
    
    if ($countFactura > 0 || $countTributaria > 0) {
        echo "<p style='color: orange;'>⚠️ Ya hay datos en las tablas:</p>";
        echo "<ul>";
        echo "<li>info_factura: $countFactura registros</li>";
        echo "<li>info_tributaria: $countTributaria registros</li>";
        echo "</ul>";
        echo "<p>No se crearán datos de prueba.</p>";
    } else {
        echo "<p>📝 Creando datos de prueba...</p>";
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Insertar datos en info_tributaria
            $sql = "INSERT INTO info_tributaria (
                ambiente, tipo_emision, razon_social, nombre_comercial, 
                ruc_emisor, clave_acceso, cod_doc, estab, pto_emi, 
                secuencial, dir_matriz, dir_establecimiento, 
                obligado_contabilidad, tipo_identificacion_comprador,
                identificacion_comprador, razon_social_comprador,
                direccion_comprador, telefono_comprador, email_comprador,
                fecha_emision, total_sin_impuestos, total_descuento,
                total_impuesto, propina, importe_total, moneda,
                forma_pago, plazo, fecha_vencimiento, numero_autorizacion,
                fecha_autorizacion, created_at
            ) VALUES (
                'PRUEBAS', 'NORMAL', 'EMPRESA DE PRUEBA S.A.', 'EMPRESA DE PRUEBA',
                '1234567890001', '1234567890123456789012345678901234567890', '01', '001', '001',
                '000000001', 'DIRECCION MATRIZ', 'DIRECCION ESTABLECIMIENTO',
                'NO', 'RUC', '9876543210001', 'CLIENTE DE PRUEBA S.A.',
                'DIRECCION DEL CLIENTE', '0999999999', 'cliente@prueba.com',
                '2024-01-15', 100.00, 0.00, 12.00, 0.00, 112.00, 'DOLAR',
                'SIN UTILIZACION DEL SISTEMA FINANCIERO', '0', '2024-01-15', '1234567890',
                '2024-01-15 10:00:00', NOW()
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $infoTributariaId = $pdo->lastInsertId();
            
            echo "<p style='color: green;'>✅ Datos insertados en info_tributaria (ID: $infoTributariaId)</p>";
            
            // Insertar datos en info_factura
            $sql = "INSERT INTO info_factura (
                info_tributaria_id, fecha_emision, razon_social_comprador,
                identificacion_comprador, direccion_comprador, telefono_comprador,
                email_comprador, importe_total, estatus, retencion, valor_pagado,
                observacion, created_at
            ) VALUES (
                ?, '2024-01-15', 'CLIENTE DE PRUEBA S.A.',
                '9876543210001', 'DIRECCION DEL CLIENTE', '0999999999',
                'cliente@prueba.com', 112.00, 'REGISTRADA', 0.00, 112.00,
                'Factura de prueba creada automáticamente', NOW()
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$infoTributariaId]);
            $infoFacturaId = $pdo->lastInsertId();
            
            echo "<p style='color: green;'>✅ Datos insertados en info_factura (ID: $infoFacturaId)</p>";
            
            // Insertar datos en detalle_factura_sri
            $sql = "INSERT INTO detalle_factura_sri (
                info_factura_id, codigo_principal, codigo_auxiliar, descripcion,
                cantidad, precio_unitario, descuento, precio_total_sin_impuesto,
                codigo_impuesto, codigo_porcentaje, tarifa, base_imponible,
                valor_impuesto, created_at
            ) VALUES (
                ?, '001', 'PROD001', 'Producto de prueba',
                1, 100.00, 0.00, 100.00,
                '2', '0', 0.00, 100.00,
                12.00, NOW()
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$infoFacturaId]);
            
            echo "<p style='color: green;'>✅ Datos insertados en detalle_factura_sri</p>";
            
            // Commit de la transacción
            $pdo->commit();
            
            echo "<p style='color: green;'>✅ Datos de prueba creados exitosamente</p>";
            
            // Verificar los datos creados
            echo "<h3>📊 Verificación de datos creados:</h3>";
            
            $sql = "SELECT 
                it.estab,
                it.pto_emi,
                it.secuencial,
                f.razon_social_comprador as cliente,
                f.importe_total as total,
                f.estatus
            FROM info_factura f 
            JOIN info_tributaria it ON f.info_tributaria_id = it.id";
            
            $stmt = $pdo->query($sql);
            $resultados = $stmt->fetchAll();
            
            if (count($resultados) > 0) {
                echo "<table style='width: 100%; border-collapse: collapse;'>";
                echo "<tr style='background: #f8f9fa;'>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estab</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Pto Emi</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Secuencial</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Cliente</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Total</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estatus</th>";
                echo "</tr>";
                
                foreach ($resultados as $row) {
                    echo "<tr>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estab'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['pto_emi'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['secuencial'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['cliente'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['total'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estatus'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='facturacion.html'>📊 Ir a Facturación</a></p>";
echo "<p><a href='test_connection_simple.php'>🔍 Verificar Conexión</a></p>";
?> 