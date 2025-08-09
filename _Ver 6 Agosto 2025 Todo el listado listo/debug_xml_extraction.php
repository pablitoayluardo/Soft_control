<?php
// =====================================================
// DEBUG XML EXTRACTION
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔍 Debug XML Extraction</h2>";

// Función para extraer información del XML (copiada del archivo original)
function extraerInformacionFactura($xml) {
    $info = [];
    
    // Información básica de la factura
    $info['estab'] = (string)($xml->estab ?? 'N/A');
    $info['ptoEmi'] = (string)($xml->ptoEmi ?? 'N/A');
    $info['secuencial'] = (string)($xml->secuencial ?? 'N/A');
    
    // Manejar fecha de emisión
    $fechaEmision = (string)($xml->fechaEmision ?? '');
    $info['fecha_emision'] = !empty($fechaEmision) ? convertirFecha($fechaEmision) : date('Y-m-d');
    
    // Información del comprador
    $info['razon_social_comprador'] = (string)($xml->razonSocialComprador ?? 'N/A');
    $info['identificacion_comprador'] = (string)($xml->identificacionComprador ?? 'N/A');
    $info['direccion_comprador'] = (string)($xml->direccionComprador ?? 'N/A');
    $info['tipo_identificacion_comprador'] = (string)($xml->tipoIdentificacionComprador ?? '04');
    
    // Información de la factura
    $info['importe_total'] = (float)($xml->importeTotal ?? 0);
    $info['total_sin_impuestos'] = (float)($xml->totalSinImpuestos ?? $info['importe_total']);
    $info['total_descuento'] = (float)($xml->totalDescuento ?? 0);
    $info['moneda'] = (string)($xml->moneda ?? 'USD');
    $info['forma_pago'] = (string)($xml->formaPago ?? '01');
    
    // Información tributaria
    $info['ambiente'] = (string)($xml->ambiente ?? $xml->infoTributaria->ambiente ?? '2');
    $info['tipo_emision'] = (string)($xml->tipoEmision ?? $xml->infoTributaria->tipoEmision ?? '1');
    $info['razon_social'] = (string)($xml->razonSocial ?? $xml->infoTributaria->razonSocial ?? 'N/A');
    $info['nombre_comercial'] = (string)($xml->nombreComercial ?? $xml->infoTributaria->nombreComercial ?? 'N/A');
    $info['ruc_emisor'] = (string)($xml->ruc ?? $xml->infoTributaria->ruc ?? 'N/A');
    $info['clave_acceso'] = (string)($xml->claveAcceso ?? $xml->infoTributaria->claveAcceso ?? 'N/A');
    $info['cod_doc'] = (string)($xml->codDoc ?? $xml->infoTributaria->codDoc ?? '01');
    $info['dir_matriz'] = (string)($xml->dirMatriz ?? $xml->infoTributaria->dirMatriz ?? 'N/A');
    $info['dir_establecimiento'] = (string)($xml->dirEstablecimiento ?? $xml->infoFactura->dirEstablecimiento ?? 'N/A');
    $info['obligado_contabilidad'] = (string)($xml->obligadoContabilidad ?? $xml->infoFactura->obligadoContabilidad ?? 'NO');
    
    // Fecha de autorización
    $fechaAutorizacion = (string)($xml->fechaAutorizacion ?? $info['fecha_emision']);
    $info['fecha_autorizacion'] = !empty($fechaAutorizacion) ? convertirFecha($fechaAutorizacion) : null;
    
    return $info;
}

function convertirFecha($fecha) {
    if (empty($fecha)) {
        return date('Y-m-d');
    }
    
    $fecha = trim($fecha);
    
    $formatos = [
        'Y-m-d',           // 2024-01-15
        'd/m/Y',           // 15/01/2024
        'Y-m-d\TH:i:s',    // 2024-01-15T10:30:00
        'Y-m-d\TH:i:s.v',  // 2024-01-15T10:30:00.000
        'Y-m-d\TH:i:s.vP', // 2024-01-15T10:30:00.000-05:00
        'd-m-Y',           // 15-01-2024
        'm/d/Y',           // 01/15/2024
        'Y-m-d H:i:s',     // 2024-01-15 10:30:00
    ];
    
    foreach ($formatos as $formato) {
        $fechaObj = DateTime::createFromFormat($formato, $fecha);
        if ($fechaObj !== false) {
            return $fechaObj->format('Y-m-d');
        }
    }
    
    try {
        $fechaObj = new DateTime($fecha);
        return $fechaObj->format('Y-m-d');
    } catch (Exception $e) {
        return date('Y-m-d');
    }
}

function extraerDetallesFactura($xml) {
    $detalles = [];
    
    // Buscar elementos de detalle
    $detallesFactura = $xml->detallesFactura->detalleFactura ?? $xml->detalle ?? [];
    
    if (is_array($detallesFactura)) {
        foreach ($detallesFactura as $detalle) {
            $det = [];
            $det['codigo_principal'] = (string)($detalle->codigoPrincipal ?? 'N/A');
            $det['descripcion'] = (string)($detalle->descripcion ?? 'N/A');
            $det['cantidad'] = (float)($detalle->cantidad ?? 0);
            $det['precio_unitario'] = (float)($detalle->precioUnitario ?? 0);
            $det['descuento'] = (float)($detalle->descuento ?? 0);
            $det['precio_total_sin_impuesto'] = (float)($detalle->precioTotalSinImpuesto ?? 0);
            $det['codigo_impuesto'] = (string)($detalle->codigoImpuesto ?? '2');
            $det['codigo_porcentaje'] = (string)($detalle->codigoPorcentaje ?? '4');
            $det['tarifa'] = (float)($detalle->tarifa ?? 15.00);
            $det['base_imponible'] = (float)($detalle->baseImponible ?? $det['precio_total_sin_impuesto']);
            $det['valor_impuesto'] = (float)($detalle->valorImpuesto ?? ($det['precio_total_sin_impuesto'] * 0.15));
            $det['informacion_adicional'] = (string)($detalle->informacionAdicional ?? '');
            $detalles[] = $det;
        }
    }
    
    return $detalles;
}

function extraerInfoAdicional($xml) {
    $infoAdicional = [];
    
    $infoAdicionalElement = $xml->infoAdicional ?? $xml->informacionAdicional ?? null;
    
    if ($infoAdicionalElement) {
        $campoAdicional = $infoAdicionalElement->campoAdicional ?? [];
        
        if (is_array($campoAdicional)) {
            foreach ($campoAdicional as $campo) {
                $nombre = (string)($campo['nombre'] ?? 'campo_' . count($infoAdicional));
                $valor = (string)($campo ?? '');
                $infoAdicional[] = ['nombre' => $nombre, 'valor' => $valor];
            }
        }
    }
    
    return $infoAdicional;
}

function extraerPagos($xml) {
    $pagos = [];
    $pagosElement = $xml->pagos ?? $xml->pago ?? null;

    if ($pagosElement) {
        $pago = $pagosElement->pago ?? [];

        if (is_array($pago)) {
            foreach ($pago as $p) {
                $pagoInfo = [];
                $pagoInfo['tipo_pago'] = (string)($p->tipoPago ?? 'N/A');
                $pagoInfo['forma_pago'] = (string)($p->formaPago ?? 'N/A');
                $pagoInfo['fecha_pago'] = (string)($p->fechaPago ?? 'N/A');
                $pagoInfo['valor_pagado'] = (float)($p->valorPagado ?? 0);
                $pagoInfo['moneda'] = (string)($p->moneda ?? 'N/A');
                $pagoInfo['tipo_cambio'] = (float)($p->tipoCambio ?? 1);
                $pagoInfo['observacion'] = (string)($p->observacion ?? '');
                $pagos[] = $pagoInfo;
            }
        }
    }
    return $pagos;
}

function extraerTotalImpuestos($xml) {
    $totalImpuestos = [];
    $totalImpuestosElement = $xml->totalImpuestos ?? $xml->totalImpuestosSRI ?? null;

    if ($totalImpuestosElement) {
        $totalImpuesto = $totalImpuestosElement->totalImpuesto ?? [];

        if (is_array($totalImpuesto)) {
            foreach ($totalImpuesto as $impuesto) {
                $impuestoInfo = [];
                $impuestoInfo['codigo'] = (string)($impuesto->codigo ?? 'N/A');
                $impuestoInfo['codigo_porcentaje'] = (string)($impuesto->codigoPorcentaje ?? 'N/A');
                $impuestoInfo['base_imponible'] = (float)($impuesto->baseImponible ?? 0);
                $impuestoInfo['valor'] = (float)($impuesto->valor ?? 0);
                $totalImpuestos[] = $impuestoInfo;
            }
        }
    }
    return $totalImpuestos;
}

function extraerImpuestosDetalle($xml) {
    $impuestosDetalle = [];
    $detalles = $xml->detallesFactura->detalleFactura ?? $xml->detalle ?? [];

    if (is_array($detalles)) {
        foreach ($detalles as $detalle) {
            $impuestoDetalle = [];
            $impuestoDetalle['codigo_principal'] = (string)($detalle->codigoPrincipal ?? 'N/A');
            $impuestoDetalle['codigo_impuesto'] = (string)($detalle->codigoImpuesto ?? 'N/A');
            $impuestoDetalle['codigo_porcentaje'] = (string)($detalle->codigoPorcentaje ?? 'N/A');
            $impuestoDetalle['base_imponible'] = (float)($detalle->baseImponible ?? 0);
            $impuestoDetalle['valor'] = (float)($detalle->valorImpuesto ?? 0);
            $impuestosDetalle[] = $impuestoDetalle;
        }
    }
    return $impuestosDetalle;
}

// Verificar si se subió un archivo
if (isset($_FILES['archivo_xml']) && $_FILES['archivo_xml']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['archivo_xml'];
    $xmlContent = file_get_contents($archivo['tmp_name']);
    
    if ($xmlContent) {
        $xml = simplexml_load_string($xmlContent);
        
        if ($xml) {
            echo "<p style='color: green;'>✅ XML cargado correctamente</p>";
            
            // Extraer información
            $facturaInfo = extraerInformacionFactura($xml);
            $detallesFactura = extraerDetallesFactura($xml);
            $infoAdicional = extraerInfoAdicional($xml);
            $pagos = extraerPagos($xml);
            $totalImpuestos = extraerTotalImpuestos($xml);
            $impuestosDetalle = extraerImpuestosDetalle($xml);
            
            echo "<h3>📋 Información extraída:</h3>";
            echo "<pre>" . print_r($facturaInfo, true) . "</pre>";
            
            echo "<h3>📄 Detalles extraídos (" . count($detallesFactura) . " items):</h3>";
            echo "<pre>" . print_r($detallesFactura, true) . "</pre>";
            
            echo "<h3>📝 Información adicional (" . count($infoAdicional) . " items):</h3>";
            echo "<pre>" . print_r($infoAdicional, true) . "</pre>";
            
            echo "<h3>💰 Pagos extraídos (" . count($pagos) . " items):</h3>";
            echo "<pre>" . print_r($pagos, true) . "</pre>";
            
            echo "<h3>🏛️ Impuestos totales extraídos (" . count($totalImpuestos) . " items):</h3>";
            echo "<pre>" . print_r($totalImpuestos, true) . "</pre>";
            
            echo "<h3>📊 Impuestos por detalle extraídos (" . count($impuestosDetalle) . " items):</h3>";
            echo "<pre>" . print_r($impuestosDetalle, true) . "</pre>";
            
            // Intentar insertar en la base de datos
            try {
                $pdo = getDBConnection();
                
                if ($pdo) {
                    echo "<h3>🗄️ Intentando insertar en la base de datos:</h3>";
                    
                    $pdo->beginTransaction();
                    
                    // 1. Insertar en info_tributaria
                    $stmt = $pdo->prepare("
                        INSERT INTO info_tributaria (
                            ambiente, tipo_emision, razon_social, nombre_comercial, ruc,
                            clave_acceso, cod_doc, estab, pto_emi, secuencial, dir_matriz,
                            fecha_autorizacion
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $resultado = $stmt->execute([
                        $facturaInfo['ambiente'] ?: '2',
                        $facturaInfo['tipo_emision'] ?: '1',
                        $facturaInfo['razon_social'] ?: 'N/A',
                        $facturaInfo['nombre_comercial'] ?: 'N/A',
                        $facturaInfo['ruc_emisor'] ?: 'N/A',
                        $facturaInfo['clave_acceso'] ?: 'N/A',
                        $facturaInfo['cod_doc'] ?: '01',
                        $facturaInfo['estab'] ?: '001',
                        $facturaInfo['ptoEmi'] ?: '001',
                        $facturaInfo['secuencial'] ?: '000000001',
                        $facturaInfo['dir_matriz'] ?: 'N/A',
                        $facturaInfo['fecha_autorizacion'] ?: null
                    ]);
                    
                    if ($resultado) {
                        $infoTributariaId = $pdo->lastInsertId();
                        echo "<p style='color: green;'>✅ Info_tributaria insertada (ID: $infoTributariaId)</p>";
                        
                        // 2. Insertar en info_factura
                        $stmt = $pdo->prepare("
                            INSERT INTO info_factura (
                                id_info_tributaria, fecha_emision, dir_establecimiento,
                                obligado_contabilidad, tipo_identificacion_comprador,
                                razon_social_comprador, identificacion_comprador,
                                direccion_comprador, total_sin_impuestos, total_descuento,
                                importe_total, moneda, forma_pago, estatus, retencion,
                                valor_pagado, observacion
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        
                        $resultado = $stmt->execute([
                            $infoTributariaId,
                            $facturaInfo['fecha_emision'],
                            $facturaInfo['dir_establecimiento'],
                            $facturaInfo['obligado_contabilidad'],
                            $facturaInfo['tipo_identificacion_comprador'],
                            $facturaInfo['razon_social_comprador'],
                            $facturaInfo['identificacion_comprador'],
                            $facturaInfo['direccion_comprador'],
                            $facturaInfo['total_sin_impuestos'],
                            $facturaInfo['total_descuento'],
                            $facturaInfo['importe_total'],
                            $facturaInfo['moneda'],
                            $facturaInfo['forma_pago'],
                            'REGISTRADA',
                            0.00,
                            $facturaInfo['importe_total'],
                            'Factura registrada desde XML'
                        ]);
                        
                        if ($resultado) {
                            $infoFacturaId = $pdo->lastInsertId();
                            echo "<p style='color: green;'>✅ Info_factura insertada (ID: $infoFacturaId)</p>";
                            
                            // 3. Insertar detalles
                            if (!empty($detallesFactura)) {
                                $stmt = $pdo->prepare("
                                    INSERT INTO detalle_factura_sri (
                                        id_info_factura, codigo_principal, descripcion,
                                        cantidad, precio_unitario, descuento,
                                        precio_total_sin_impuesto, codigo_impuesto,
                                        codigo_porcentaje, tarifa, base_imponible,
                                        valor_impuesto, informacion_adicional
                                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                
                                $detallesInsertados = 0;
                                foreach ($detallesFactura as $detalle) {
                                    $resultado = $stmt->execute([
                                        $infoFacturaId,
                                        $detalle['codigo_principal'],
                                        $detalle['descripcion'],
                                        $detalle['cantidad'],
                                        $detalle['precio_unitario'],
                                        $detalle['descuento'],
                                        $detalle['precio_total_sin_impuesto'],
                                        $detalle['codigo_impuesto'],
                                        $detalle['codigo_porcentaje'],
                                        $detalle['tarifa'],
                                        $detalle['base_imponible'],
                                        $detalle['valor_impuesto'],
                                        $detalle['informacion_adicional']
                                    ]);
                                    
                                    if ($resultado) {
                                        $detallesInsertados++;
                                    }
                                }
                                
                                echo "<p style='color: green;'>✅ $detallesInsertados detalles insertados</p>";
                            }
                            
                            // 4. Insertar información adicional
                            if (!empty($infoAdicional)) {
                                $stmt = $pdo->prepare("
                                    INSERT INTO info_adicional_factura (
                                        id_info_factura, nombre, valor
                                    ) VALUES (?, ?, ?)
                                ");
                                
                                $adicionalesInsertados = 0;
                                foreach ($infoAdicional as $adicional) {
                                    $resultado = $stmt->execute([
                                        $infoFacturaId,
                                        $adicional['nombre'],
                                        $adicional['valor']
                                    ]);
                                    
                                    if ($resultado) {
                                        $adicionalesInsertados++;
                                    }
                                }
                                
                                echo "<p style='color: green;'>✅ $adicionalesInsertados campos adicionales insertados</p>";
                            }
                            
                            // 5. Insertar pagos
                            if (!empty($pagos)) {
                                $stmt = $pdo->prepare("
                                    INSERT INTO pagos (
                                        id_info_factura, formaPago, total
                                    ) VALUES (?, ?, ?)
                                ");
                                
                                $pagosInsertados = 0;
                                foreach ($pagos as $pago) {
                                    $resultado = $stmt->execute([
                                        $infoFacturaId,
                                        $pago['forma_pago'],
                                        $pago['valor_pagado']
                                    ]);
                                    
                                    if ($resultado) {
                                        $pagosInsertados++;
                                    }
                                }
                                
                                echo "<p style='color: green;'>✅ $pagosInsertados pagos insertados</p>";
                            }
                            
                            // 6. Insertar impuestos totales
                            if (!empty($totalImpuestos)) {
                                $stmt = $pdo->prepare("
                                    INSERT INTO total_con_impuestos (
                                        id_info_factura, codigo, codigoPorcentaje, baseImponible, valor
                                    ) VALUES (?, ?, ?, ?, ?)
                                ");
                                
                                $impuestosInsertados = 0;
                                foreach ($totalImpuestos as $impuesto) {
                                    $resultado = $stmt->execute([
                                        $infoFacturaId,
                                        $impuesto['codigo'],
                                        $impuesto['codigo_porcentaje'],
                                        $impuesto['base_imponible'],
                                        $impuesto['valor']
                                    ]);
                                    
                                    if ($resultado) {
                                        $impuestosInsertados++;
                                    }
                                }
                                
                                echo "<p style='color: green;'>✅ $impuestosInsertados impuestos totales insertados</p>";
                            }
                            
                            // 7. Insertar impuestos por detalle
                            if (!empty($impuestosDetalle)) {
                                $stmt = $pdo->prepare("
                                    INSERT INTO impuestos_detalle (
                                        id_detalle, codigo, codigoPorcentaje, tarifa, baseImponible, valor
                                    ) VALUES (?, ?, ?, ?, ?, ?)
                                ");
                                
                                // Obtener los IDs de los detalles insertados
                                $detallesIds = [];
                                if (!empty($detallesFactura)) {
                                    $sql = "SELECT id_detalle FROM detalle_factura_sri WHERE id_info_factura = ? ORDER BY id_detalle ASC";
                                    $stmtIds = $pdo->prepare($sql);
                                    $stmtIds->execute([$infoFacturaId]);
                                    $detallesIds = $stmtIds->fetchAll(PDO::FETCH_COLUMN);
                                }
                                
                                $impuestosDetalleInsertados = 0;
                                foreach ($impuestosDetalle as $index => $impuesto) {
                                    if (isset($detallesIds[$index])) {
                                        $resultado = $stmt->execute([
                                            $detallesIds[$index],
                                            $impuesto['codigo_impuesto'],
                                            $impuesto['codigo_porcentaje'],
                                            15.00, // tarifa por defecto
                                            $impuesto['base_imponible'],
                                            $impuesto['valor']
                                        ]);
                                        
                                        if ($resultado) {
                                            $impuestosDetalleInsertados++;
                                        }
                                    }
                                }
                                
                                echo "<p style='color: green;'>✅ $impuestosDetalleInsertados impuestos por detalle insertados</p>";
                            }
                            
                            $pdo->commit();
                            echo "<p style='color: green;'>✅ <strong>Transacción completada exitosamente</strong></p>";
                            
                        } else {
                            throw new Exception('Error al insertar info_factura');
                        }
                    } else {
                        throw new Exception('Error al insertar info_tributaria');
                    }
                } else {
                    echo "<p style='color: red;'>❌ Error de conexión a la base de datos</p>";
                }
            } catch (Exception $e) {
                if (isset($pdo)) {
                    $pdo->rollback();
                }
                echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error al parsear el XML</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se pudo leer el contenido del archivo</p>";
    }
} else {
    // Formulario para subir archivo
    echo "<form method='post' enctype='multipart/form-data'>";
    echo "<h3>📁 Subir archivo XML para debug:</h3>";
    echo "<input type='file' name='archivo_xml' accept='.xml' required><br><br>";
    echo "<input type='submit' value='Analizar XML'>";
    echo "</form>";
}
?> 