<?php
// =====================================================
// CARGA DE FACTURAS SRI - VERSIÓN MEJORADA
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🏢 Carga de Facturas SRI - Versión Mejorada</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
    
    // =====================================================
    // VERIFICAR SI LA FACTURA YA EXISTE
    // =====================================================
    
    $xmlFile = 'xml/fact_gc/3007202501172164244300120021000000018661440544518.xml';
    $xmlContent = file_get_contents($xmlFile);
    
    if (!$xmlContent) {
        throw new Exception('No se pudo leer el archivo XML');
    }
    
    $xml = simplexml_load_string($xmlContent);
    if (!$xml) {
        throw new Exception('Error al parsear el XML');
    }
    
    $claveAcceso = (string)$xml->infoTributaria->claveAcceso;
    
    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id FROM info_tributaria WHERE clave_acceso = ?");
    $stmt->execute([$claveAcceso]);
    $existe = $stmt->fetch();
    
    if ($existe) {
        echo "<p style='color: orange;'>⚠️ <strong>La factura ya existe en la base de datos</strong></p>";
        echo "<p><strong>Clave de Acceso:</strong> $claveAcceso</p>";
        
        // Mostrar información de la factura existente
        $sql = "SELECT 
            it.razon_social,
            it.nombre_comercial,
            it.ruc,
            it.secuencial,
            inf_factura.fecha_emision,
            inf_factura.razon_social_comprador,
            inf_factura.importe_total,
            inf_factura.moneda
        FROM info_tributaria it
        JOIN info_factura inf_factura ON it.id = inf_factura.info_tributaria_id
        WHERE it.clave_acceso = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$claveAcceso]);
        $factura = $stmt->fetch();
        
        if ($factura) {
            echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3>📋 Información de la Factura Existente:</h3>";
            echo "<p><strong>Emisor:</strong> " . $factura['razon_social'] . " (" . $factura['nombre_comercial'] . ")</p>";
            echo "<p><strong>RUC:</strong> " . $factura['ruc'] . "</p>";
            echo "<p><strong>Secuencial:</strong> " . $factura['secuencial'] . "</p>";
            echo "<p><strong>Fecha:</strong> " . $factura['fecha_emision'] . "</p>";
            echo "<p><strong>Comprador:</strong> " . $factura['razon_social_comprador'] . "</p>";
            echo "<p><strong>Total:</strong> $" . number_format($factura['importe_total'], 2) . " " . $factura['moneda'] . "</p>";
            echo "</div>";
        }
        
        // Mostrar estadísticas actuales
        echo "<h3>📊 Estadísticas Actuales:</h3>";
        
        $stats = [
            'info_tributaria' => 'SELECT COUNT(*) as total FROM info_tributaria',
            'info_factura' => 'SELECT COUNT(*) as total FROM info_factura',
            'detalle_factura_sri' => 'SELECT COUNT(*) as total FROM detalle_factura_sri',
            'info_adicional_factura' => 'SELECT COUNT(*) as total FROM info_adicional_factura'
        ];
        
        foreach ($stats as $table => $query) {
            $stmt = $pdo->query($query);
            $count = $stmt->fetch()['total'];
            echo "<p><strong>$table:</strong> $count registros</p>";
        }
        
        echo "<h3>🎯 Opciones:</h3>";
        echo "<p><a href='view_factura_sri.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 Ver Datos de Facturación</a></p>";
        echo "<p><a href='dashboard.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📈 Ir al Dashboard</a></p>";
        
    } else {
        // =====================================================
        // CARGAR NUEVA FACTURA
        // =====================================================
        
        echo "<h3>📄 Cargando nueva factura...</h3>";
        
        // Extraer información tributaria
        $infoTributaria = $xml->infoTributaria;
        
        $sql = "INSERT INTO info_tributaria (
            ambiente, tipo_emision, razon_social, nombre_comercial, ruc, 
            clave_acceso, cod_doc, estab, pto_emi, secuencial, dir_matriz
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            (string)$infoTributaria->ambiente,
            (string)$infoTributaria->tipoEmision,
            (string)$infoTributaria->razonSocial,
            (string)$infoTributaria->nombreComercial,
            (string)$infoTributaria->ruc,
            (string)$infoTributaria->claveAcceso,
            (string)$infoTributaria->codDoc,
            (string)$infoTributaria->estab,
            (string)$infoTributaria->ptoEmi,
            (string)$infoTributaria->secuencial,
            (string)$infoTributaria->dirMatriz
        ]);
        
        $infoTributariaId = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Información tributaria insertada (ID: $infoTributariaId)</p>";
        
        // Extraer información de factura
        $infoFactura = $xml->infoFactura;
        
        $sql = "INSERT INTO info_factura (
            info_tributaria_id, fecha_emision, dir_establecimiento, obligado_contabilidad,
            tipo_identificacion_comprador, razon_social_comprador, identificacion_comprador,
            direccion_comprador, total_sin_impuestos, total_descuento, importe_total,
            moneda, forma_pago
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $infoTributariaId,
            date('Y-m-d', strtotime((string)$infoFactura->fechaEmision)),
            (string)$infoFactura->dirEstablecimiento,
            (string)$infoFactura->obligadoContabilidad,
            (string)$infoFactura->tipoIdentificacionComprador,
            (string)$infoFactura->razonSocialComprador,
            (string)$infoFactura->identificacionComprador,
            (string)$infoFactura->direccionComprador,
            (float)$infoFactura->totalSinImpuestos,
            (float)$infoFactura->totalDescuento,
            (float)$infoFactura->importeTotal,
            (string)$infoFactura->moneda,
            (string)$infoFactura->pagos->pago->formaPago
        ]);
        
        $infoFacturaId = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Información de factura insertada (ID: $infoFacturaId)</p>";
        
        // Extraer detalles
        $detalles = $xml->detalles->detalle;
        $totalDetalles = 0;
        
        foreach ($detalles as $detalle) {
            $sql = "INSERT INTO detalle_factura_sri (
                info_factura_id, codigo_principal, descripcion, cantidad, precio_unitario,
                descuento, precio_total_sin_impuesto, codigo_impuesto, codigo_porcentaje,
                tarifa, base_imponible, valor_impuesto, informacion_adicional
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $informacionAdicional = '';
            if (isset($detalle->detallesAdicionales->detAdicional)) {
                $detAdicional = $detalle->detallesAdicionales->detAdicional;
                $informacionAdicional = (string)$detAdicional['valor'];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $infoFacturaId,
                (string)$detalle->codigoPrincipal,
                (string)$detalle->descripcion,
                (float)$detalle->cantidad,
                (float)$detalle->precioUnitario,
                (float)$detalle->descuento,
                (float)$detalle->precioTotalSinImpuesto,
                (string)$detalle->impuestos->impuesto->codigo,
                (string)$detalle->impuestos->impuesto->codigoPorcentaje,
                (float)$detalle->impuestos->impuesto->tarifa,
                (float)$detalle->impuestos->impuesto->baseImponible,
                (float)$detalle->impuestos->impuesto->valor,
                $informacionAdicional
            ]);
            
            $totalDetalles++;
        }
        
        echo "<p style='color: green;'>✅ $totalDetalles detalles insertados</p>";
        
        // Extraer información adicional
        if (isset($xml->infoAdicional->campoAdicional)) {
            foreach ($xml->infoAdicional->campoAdicional as $campo) {
                $sql = "INSERT INTO info_adicional_factura (info_factura_id, nombre, valor) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $infoFacturaId,
                    (string)$campo['nombre'],
                    (string)$campo
                ]);
            }
            echo "<p style='color: green;'>✅ Información adicional insertada</p>";
        }
        
        echo "<h3>🎉 ¡Factura cargada exitosamente!</h3>";
        echo "<p><a href='view_factura_sri.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 Ver Datos de Facturación</a></p>";
    }
    
    // =====================================================
    // MOSTRAR ENLACES ÚTILES
    // =====================================================
    
    echo "<hr>";
    echo "<h3>🔗 Enlaces Útiles:</h3>";
    echo "<p><a href='dashboard.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📊 Dashboard</a></p>";
    echo "<p><a href='index.html' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🔐 Login</a></p>";
    echo "<p><a href='check_tables.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🔍 Verificar Tablas</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
    
    echo "<h3>🔧 Solución de problemas:</h3>";
    echo "<ul>";
    echo "<li>Verificar que el archivo XML existe en xml/fact_gc/</li>";
    echo "<li>Confirmar que las tablas están creadas</li>";
    echo "<li>Verificar permisos de la base de datos</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><em>Carga de facturas SRI completada - Sistema de Control GloboCity</em></p>";
?> 