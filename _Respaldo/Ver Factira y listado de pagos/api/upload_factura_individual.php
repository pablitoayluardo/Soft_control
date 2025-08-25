<?php
// Asegurar que no hay salida antes de los headers
ob_start();

// Headers para JSON y CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir configuración de base de datos
require_once '../config.php';

// Función para devolver respuesta JSON
function returnJsonResponse($success, $message, $data = null) {
    // Limpiar cualquier salida previa
    if (ob_get_length()) {
        ob_clean();
    }
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Verificar que se recibió un archivo
    if (!isset($_FILES['archivo_xml']) || $_FILES['archivo_xml']['error'] !== UPLOAD_ERR_OK) {
        returnJsonResponse(false, 'No se recibió el archivo XML o hubo un error en la subida');
    }
    
    $archivo = $_FILES['archivo_xml'];
    $datosFactura = json_decode($_POST['datos_factura'], true);
    
    // Verificar que es un archivo XML
    if ($archivo['type'] !== 'text/xml' && $archivo['type'] !== 'application/xml') {
        // Verificar extensión como respaldo
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if ($extension !== 'xml') {
            returnJsonResponse(false, 'El archivo debe ser un XML válido');
        }
    }
    
    // Leer el contenido del archivo XML
    $xmlContent = file_get_contents($archivo['tmp_name']);
    if (!$xmlContent) {
        returnJsonResponse(false, 'No se pudo leer el contenido del archivo XML');
    }
    
    // Parsear el XML
    $xml = simplexml_load_string($xmlContent);
    if (!$xml) {
        returnJsonResponse(false, 'El archivo XML no es válido');
    }
    
    // Extraer información de la factura
    $facturaInfo = extraerInformacionFactura($xml);
    
    // Extraer detalles de la factura
    $detallesFactura = extraerDetallesFactura($xml);
    
    // Extraer información adicional
    $infoAdicional = extraerInfoAdicional($xml);
    
    // Extraer información de pagos
    $pagos = extraerPagos($xml);
    
    // Extraer información de impuestos totales
    $totalImpuestos = extraerTotalImpuestos($xml);
    
    // Extraer información de impuestos por detalle
    $impuestosDetalle = extraerImpuestosDetalle($xml);
    
    // Conectar a la base de datos (método directo que funciona en el hosting)
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Configuración de base de datos incompleta en el servidor.');
    }
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    if (!$pdo) {
        returnJsonResponse(false, 'Error de conexión a la base de datos');
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        // VALIDACIÓN 1: Verificar si ya existe una factura con la misma clave_acceso
        $stmt = $pdo->prepare("
            SELECT f.id_info_factura, f.razon_social_comprador, f.importe_total, it.clave_acceso
            FROM info_factura f
            JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
            WHERE it.clave_acceso = ?
        ");
        $stmt->execute([$facturaInfo['clave_acceso']]);
        $facturaExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($facturaExistente) {
            returnJsonResponse(false, "Esta factura ya está registrada en el sistema. Clave de acceso: {$facturaInfo['clave_acceso']}, Cliente: {$facturaExistente['razon_social_comprador']}, Total: \${$facturaExistente['importe_total']}");
        }
        
        // VALIDACIÓN 2: Verificar si existe una factura con el mismo secuencial y fecha
        $stmt = $pdo->prepare("
            SELECT f.id_info_factura, f.razon_social_comprador, f.importe_total, it.clave_acceso
            FROM info_factura f
            JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
            WHERE it.secuencial = ? AND f.fecha_emision = ?
        ");
        $stmt->execute([$facturaInfo['secuencial'], $facturaInfo['fecha_emision']]);
        $facturaExistenteSecuencial = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($facturaExistenteSecuencial) {
            returnJsonResponse(false, "Ya existe una factura con el mismo secuencial ({$facturaInfo['secuencial']}) y fecha ({$facturaInfo['fecha_emision']}). Clave de acceso: {$facturaExistenteSecuencial['clave_acceso']}, Cliente: {$facturaExistenteSecuencial['razon_social_comprador']}");
        }
        
        // PASO 1: Insertar en info_tributaria
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
        
        if (!$resultado) {
            returnJsonResponse(false, 'Error al insertar información tributaria');
        }
        
        $infoTributariaId = $pdo->lastInsertId();
        
        // PASO 2: Insertar en info_factura
        $stmt = $pdo->prepare("
            INSERT INTO info_factura (
                id_info_tributaria, fecha_emision, dir_establecimiento,
                obligado_contabilidad, tipo_identificacion_comprador,
                razon_social_comprador, identificacion_comprador,
                direccion_comprador, total_sin_impuestos, total_descuento,
                importe_total, moneda, forma_pago, estatus, 
                retencion, valor_pagado, observacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $resultado = $stmt->execute([
            $infoTributariaId,
            $facturaInfo['fecha_emision'] ?: date('Y-m-d'),
            $facturaInfo['dir_establecimiento'] ?: 'N/A',
            $facturaInfo['obligado_contabilidad'] ?: 'NO',
            $facturaInfo['tipo_identificacion_comprador'] ?: '04',
            $facturaInfo['razon_social_comprador'] ?: 'N/A',
            $facturaInfo['identificacion_comprador'] ?: 'N/A',
            $facturaInfo['direccion_comprador'] ?: 'N/A',
            $facturaInfo['total_sin_impuestos'] ?: 0,
            $facturaInfo['total_descuento'] ?: 0,
            $facturaInfo['importe_total'] ?: 0,
            $facturaInfo['moneda'] ?: 'USD',
            $facturaInfo['forma_pago'] ?: '01',
            'REGISTRADO',
            0.00, // Retención siempre 0 para facturas REGISTRADO
            0.00, // Valor pagado siempre 0 para facturas REGISTRADO
            'Factura registrada desde XML'
        ]);
        
        if (!$resultado) {
            returnJsonResponse(false, 'Error al insertar información de factura');
        }
        
        $infoFacturaId = $pdo->lastInsertId();
        
        // PASO 3: Insertar detalles de la factura
        if (!empty($detallesFactura)) {
            $stmt = $pdo->prepare("
                INSERT INTO detalle_factura_sri (
                    id_info_factura, codigo_principal, descripcion,
                    cantidad, precio_unitario, descuento,
                    precio_total_sin_impuesto, informacion_adicional
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($detallesFactura as $detalle) {
                $stmt->execute([
                    $infoFacturaId,
                    $detalle['codigo_principal'] ?? 'N/A',
                    $detalle['descripcion'] ?? 'N/A',
                    $detalle['cantidad'] ?? 0,
                    $detalle['precio_unitario'] ?? 0,
                    $detalle['descuento'] ?? 0,
                    $detalle['precio_total_sin_impuesto'] ?? 0,
                    $detalle['informacion_adicional'] ?? null
                ]);
            }
        }
        
        // PASO 4: Insertar información adicional
        if (!empty($infoAdicional)) {
            $stmt = $pdo->prepare("
                INSERT INTO info_adicional_factura (
                    id_info_factura, nombre, valor
                ) VALUES (?, ?, ?)
            ");
            
            foreach ($infoAdicional as $adicional) {
                $stmt->execute([
                    $infoFacturaId,
                    $adicional['nombre'],
                    $adicional['valor']
                ]);
            }
        }
        
        // PASO 5: Insertar pagos
        if (!empty($pagos)) {
            $stmt = $pdo->prepare("
                INSERT INTO pagos (
                    id_info_factura, formaPago, total
                ) VALUES (?, ?, ?)
            ");
            
            foreach ($pagos as $pago) {
                $stmt->execute([
                    $infoFacturaId,
                    $pago['formaPago'],
                    $pago['total']
                ]);
            }
        }
        
        // PASO 6 y 7 han sido eliminados porque su lógica es incorrecta o se maneja en otra parte.
        
        // Confirmar transacción
        $pdo->commit();
        
        // Guardar el archivo XML en el sistema de archivos
        $directorioDestino = '../xml/facturas_individuales/';
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }
        
        $nombreArchivo = $facturaInfo['clave_acceso'] . '.xml';
        $rutaCompleta = $directorioDestino . $nombreArchivo;
        
        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            error_log("No se pudo guardar el archivo XML: " . $rutaCompleta);
        }
        
        // Preparar respuesta exitosa
        $responseData = [
            'clave_acceso' => $facturaInfo['clave_acceso'],
            'secuencial' => $facturaInfo['secuencial'],
            'cliente' => $facturaInfo['razon_social_comprador'],
            'total' => $facturaInfo['importe_total'],
            'info_tributaria_id' => $infoTributariaId,
            'info_factura_id' => $infoFacturaId,
            'resumen' => [
                'detalles_insertados' => count($detallesFactura),
                'adicionales_insertados' => count($infoAdicional),
                'pagos_insertados' => count($pagos),
                'impuestos_totales_insertados' => count($totalImpuestos),
                'impuestos_detalle_insertados' => count($impuestosDetalle)
            ]
        ];
        
        // Registrar en el log
        logActivity("Factura individual registrada: " . $facturaInfo['clave_acceso'], 'INFO');
        
        returnJsonResponse(true, 'Factura registrada exitosamente', $responseData);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        error_log("Error en upload_factura_individual.php: " . $e->getMessage());
        returnJsonResponse(false, $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Error en upload_factura_individual.php: " . $e->getMessage());
    returnJsonResponse(false, $e->getMessage());
}

/**
 * Extraer información de la factura del XML
 */
function extraerInformacionFactura($xml) {
    $info = [];
    
    // Información básica de la factura
    $info['estab'] = (string)($xml->estab ?? $xml->infoTributaria->estab ?? 'N/A');
    $info['ptoEmi'] = (string)($xml->ptoEmi ?? $xml->infoTributaria->ptoEmi ?? 'N/A');
    $info['secuencial'] = (string)($xml->secuencial ?? $xml->infoTributaria->secuencial ?? 'N/A');
    
    // Manejar fecha de emisión
    $fechaEmision = (string)($xml->fechaEmision ?? $xml->infoFactura->fechaEmision ?? '');
    $info['fecha_emision'] = !empty($fechaEmision) ? convertirFecha($fechaEmision) : date('Y-m-d');
    
    // Información del comprador
    $info['razon_social_comprador'] = (string)($xml->razonSocialComprador ?? $xml->infoFactura->razonSocialComprador ?? 'N/A');
    $info['identificacion_comprador'] = (string)($xml->identificacionComprador ?? $xml->infoFactura->identificacionComprador ?? 'N/A');
    $info['direccion_comprador'] = (string)($xml->direccionComprador ?? $xml->infoFactura->direccionComprador ?? 'N/A');
    $info['tipo_identificacion_comprador'] = (string)($xml->tipoIdentificacionComprador ?? $xml->infoFactura->tipoIdentificacionComprador ?? '04');
    
    // Información de la factura
    $info['importe_total'] = (float)($xml->importeTotal ?? $xml->infoFactura->importeTotal ?? 0);
    $info['total_sin_impuestos'] = (float)($xml->totalSinImpuestos ?? $xml->infoFactura->totalSinImpuestos ?? $info['importe_total']);
    $info['total_descuento'] = (float)($xml->totalDescuento ?? $xml->infoFactura->totalDescuento ?? 0);
    
    // Extraer y sumar todos los valores de impuestos
    $totalImpuestosSuma = 0;
    if (isset($xml->infoFactura->totalConImpuestos->totalImpuesto)) {
        foreach ($xml->infoFactura->totalConImpuestos->totalImpuesto as $impuesto) {
            $totalImpuestosSuma += (float)($impuesto->valor ?? 0);
        }
    }
    // Ya no asignamos a $info['total_impuestos'] porque la columna no existe
    // $info['total_impuestos'] = $totalImpuestosSuma;

    $info['moneda'] = (string)($xml->moneda ?? $xml->infoFactura->moneda ?? 'USD');
    $info['forma_pago'] = (string)($xml->formaPago ?? $xml->infoFactura->formaPago ?? '01');
    
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

/**
 * Convertir fecha de formato XML a formato DATE de MySQL
 */
function convertirFecha($fecha) {
    if (empty($fecha)) {
        return date('Y-m-d');
    }
    
    // Limpiar la fecha de caracteres extra
    $fecha = trim($fecha);
    
    // Intentar diferentes formatos de fecha comunes en XML del SRI
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
    
    // Intentar con DateTime nativo de PHP para formatos ISO
    try {
        $fechaObj = new DateTime($fecha);
        return $fechaObj->format('Y-m-d');
    } catch (Exception $e) {
        return date('Y-m-d');
    }
}

/**
 * Extraer detalles de la factura del XML
 */
function extraerDetallesFactura($xml) {
    $detalles = [];
    
    // Buscar el nodo <detalles> que es el contenedor estándar
    if (!isset($xml->detalles)) {
        return [];
    }

    // Iterar sobre cada nodo <detalle> dentro de <detalles>
    foreach ($xml->detalles->detalle as $detalle) {
        $infoAdicional = '';
        if (isset($detalle->detallesAdicionales)) {
            foreach ($detalle->detallesAdicionales->detAdicional as $adicional) {
                $infoAdicional .= (string)$adicional['nombre'] . ': ' . (string)$adicional['valor'] . '; ';
            }
        }

        $detalles[] = [
            'codigo_principal' => (string)($detalle->codigoPrincipal ?? 'N/A'),
            'descripcion' => (string)($detalle->descripcion ?? 'N/A'),
            'cantidad' => (float)($detalle->cantidad ?? 0),
            'precio_unitario' => (float)($detalle->precioUnitario ?? 0),
            'descuento' => (float)($detalle->descuento ?? 0),
            'precio_total_sin_impuesto' => (float)($detalle->precioTotalSinImpuesto ?? 0),
            'informacion_adicional' => trim($infoAdicional)
        ];
    }
    
    return $detalles;
}

/**
 * Extraer información adicional del XML
 */
function extraerInfoAdicional($xml) {
    $infoAdicional = [];
    
    if (isset($xml->infoAdicional->campoAdicional)) {
        foreach ($xml->infoAdicional->campoAdicional as $campo) {
            $infoAdicional[] = [
                'nombre' => (string)$campo['nombre'],
                'valor' => (string)$campo
            ];
        }
    }
    
    return $infoAdicional;
}

/**
 * Extraer información de pagos del XML
 */
function extraerPagos($xml) {
    $pagos = [];
    
    // Buscar elementos de pago
    $pagosElement = $xml->pagos ?? $xml->infoFactura->pagos ?? null;
    
    if ($pagosElement) {
        $pagoElement = $pagosElement->pago ?? [];
        
        if (is_array($pagoElement)) {
            foreach ($pagoElement as $pago) {
                $pagoInfo = [];
                $pagoInfo['formaPago'] = (string)($pago->formaPago ?? '01');
                $pagoInfo['total'] = (float)($pago->total ?? 0);
                $pagos[] = $pagoInfo;
            }
        } else {
            // Si es un solo elemento
            $pagoInfo = [];
            $pagoInfo['formaPago'] = (string)($pagoElement->formaPago ?? '01');
            $pagoInfo['total'] = (float)($pagoElement->total ?? 0);
            $pagos[] = $pagoInfo;
        }
    }
    
    return $pagos;
}

/**
 * Extraer información de impuestos totales del XML
 */
function extraerTotalImpuestos($xml) {
    $impuestos = [];
    
    // Buscar elementos de impuestos totales
    $impuestosElement = $xml->totalImpuestos ?? $xml->infoFactura->totalImpuestos ?? null;
    
    if ($impuestosElement) {
        $impuestoElement = $impuestosElement->impuesto ?? [];
        
        if (is_array($impuestoElement)) {
            foreach ($impuestoElement as $impuesto) {
                $impuestoInfo = [];
                $impuestoInfo['codigo'] = (string)($impuesto->codigo ?? '2');
                $impuestoInfo['codigoPorcentaje'] = (string)($impuesto->codigoPorcentaje ?? '4');
                $impuestoInfo['baseImponible'] = (float)($impuesto->baseImponible ?? 0);
                $impuestoInfo['valor'] = (float)($impuesto->valor ?? 0);
                $impuestos[] = $impuestoInfo;
            }
        } else {
            // Si es un solo elemento
            $impuestoInfo = [];
            $impuestoInfo['codigo'] = (string)($impuestoElement->codigo ?? '2');
            $impuestoInfo['codigoPorcentaje'] = (string)($impuestoElement->codigoPorcentaje ?? '4');
            $impuestoInfo['baseImponible'] = (float)($impuestoElement->baseImponible ?? 0);
            $impuestoInfo['valor'] = (float)($impuestoElement->valor ?? 0);
            $impuestos[] = $impuestoInfo;
        }
    }
    
    return $impuestos;
}

/**
 * Extraer información de impuestos por detalle del XML
 */
function extraerImpuestosDetalle($xml) {
    $impuestosDetalle = [];
    
    // Buscar elementos de detalle
    $detallesFactura = $xml->detallesFactura->detalleFactura ?? $xml->detalle ?? [];
    
    if (!is_array($detallesFactura)) {
        $detallesFactura = [$detallesFactura];
    }
    
    foreach ($detallesFactura as $index => $detalle) {
        $impuestosElement = $detalle->impuestos ?? null;
        
        if ($impuestosElement) {
            $impuestoElement = $impuestosElement->impuesto ?? [];
            
            if (is_array($impuestoElement)) {
                foreach ($impuestoElement as $impuesto) {
                    $impuestoInfo = [];
                    $impuestoInfo['detalle_index'] = $index;
                    $impuestoInfo['codigo'] = (string)($impuesto->codigo ?? '2');
                    $impuestoInfo['codigoPorcentaje'] = (string)($impuesto->codigoPorcentaje ?? '4');
                    $impuestoInfo['tarifa'] = (float)($impuesto->tarifa ?? 15.00);
                    $impuestoInfo['baseImponible'] = (float)($impuesto->baseImponible ?? 0);
                    $impuestoInfo['valor'] = (float)($impuesto->valor ?? 0);
                    $impuestosDetalle[] = $impuestoInfo;
                }
            } else {
                // Si es un solo elemento
                $impuestoInfo = [];
                $impuestoInfo['detalle_index'] = $index;
                $impuestoInfo['codigo'] = (string)($impuestoElement->codigo ?? '2');
                $impuestoInfo['codigoPorcentaje'] = (string)($impuestoElement->codigoPorcentaje ?? '4');
                $impuestoInfo['tarifa'] = (float)($impuestoElement->tarifa ?? 15.00);
                $impuestoInfo['baseImponible'] = (float)($impuestoElement->baseImponible ?? 0);
                $impuestoInfo['valor'] = (float)($impuestoElement->valor ?? 0);
                $impuestosDetalle[] = $impuestoInfo;
            }
        }
    }
    
    return $impuestosDetalle;
}
?> 