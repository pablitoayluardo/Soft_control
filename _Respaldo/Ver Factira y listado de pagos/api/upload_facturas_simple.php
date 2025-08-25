<?php
// =====================================================
// API SIMPLIFICADA PARA SUBIR FACTURAS DESDE DIRECTORIO
// =====================================================

// Configurar headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Configuración directa de base de datos
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';
$charset = 'utf8mb4';

try {
    // Conexión directa a la base de datos
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Obtener parámetros
    $directorio = isset($_POST['directorio']) ? $_POST['directorio'] : 'xml/fact_gc/';
    $activarSubida = isset($_POST['activar_subida']) ? (bool)$_POST['activar_subida'] : false;

    if (!$activarSubida) {
        echo json_encode([
            'success' => false,
            'message' => 'Debe activar la opción para subir facturas'
        ]);
        exit;
    }

    // Verificar que el directorio existe
    if (!is_dir($directorio)) {
        echo json_encode([
            'success' => false,
            'message' => 'El directorio especificado no existe: ' . $directorio
        ]);
        exit;
    }

    // Obtener archivos XML del directorio
    $archivos = glob($directorio . '*.xml');

    if (empty($archivos)) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontraron archivos XML en el directorio: ' . $directorio
        ]);
        exit;
    }

    $resultados = [
        'total_archivos' => count($archivos),
        'exitosos' => 0,
        'duplicados' => 0,
        'errores' => 0,
        'detalles' => []
    ];

    foreach ($archivos as $archivo) {
        try {
            $xmlContent = file_get_contents($archivo);

            if (!$xmlContent) {
                $resultados['errores']++;
                $resultados['detalles'][] = [
                    'archivo' => basename($archivo),
                    'estado' => 'error',
                    'mensaje' => 'No se pudo leer el archivo'
                ];
                continue;
            }

            $xml = simplexml_load_string($xmlContent);
            if (!$xml) {
                $resultados['errores']++;
                $resultados['detalles'][] = [
                    'archivo' => basename($archivo),
                    'estado' => 'error',
                    'mensaje' => 'Error al parsear XML'
                ];
                continue;
            }

            // Verificar estructura básica del XML
            if (!isset($xml->infoTributaria) || !isset($xml->infoFactura)) {
                $resultados['errores']++;
                $resultados['detalles'][] = [
                    'archivo' => basename($archivo),
                    'estado' => 'error',
                    'mensaje' => 'Estructura XML inválida'
                ];
                continue;
            }

            $claveAcceso = (string)$xml->infoTributaria->claveAcceso;

            // Verificar si ya existe
            $stmt = $pdo->prepare("SELECT id FROM info_tributaria WHERE clave_acceso = ?");
            $stmt->execute([$claveAcceso]);
            $existe = $stmt->fetch();

            if ($existe) {
                $resultados['duplicados']++;
                $resultados['detalles'][] = [
                    'archivo' => basename($archivo),
                    'estado' => 'duplicado',
                    'mensaje' => 'Factura ya existe en la base de datos',
                    'clave_acceso' => $claveAcceso
                ];
                continue;
            }

            // Insertar información tributaria
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

            // Insertar información de factura
            $infoFactura = $xml->infoFactura;

            $sql = "INSERT INTO info_factura (
                info_tributaria_id, fecha_emision, dir_establecimiento, obligado_contabilidad,
                tipo_identificacion_comprador, razon_social_comprador, identificacion_comprador,
                direccion_comprador, total_sin_impuestos, total_descuento, importe_total,
                moneda, forma_pago, estatus, retencion, valor_pagado, observacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
                (string)$infoFactura->pagos->pago->formaPago,
                'PENDIENTE', // estatus por defecto
                0.00, // retencion por defecto
                0.00, // valor_pagado por defecto
                '' // observacion por defecto
            ]);

            $infoFacturaId = $pdo->lastInsertId();

            // Insertar detalles
            $detalles = $xml->detalles->detalle;
            $totalDetalles = 0;

            foreach ($detalles as $detalle) {
                $sql = "INSERT INTO detalle_factura_sri (
                    info_factura_id, codigo_principal, descripcion, cantidad, precio_unitario,
                    descuento, precio_total_sin_impuesto, codigo_impuesto, codigo_porcentaje,
                    tarifa, base_imponible, valor_impuesto, informacion_adicional
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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

            // Insertar información adicional
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
            }

            $resultados['exitosos']++;
            $resultados['detalles'][] = [
                'archivo' => basename($archivo),
                'estado' => 'exitoso',
                'mensaje' => 'Factura cargada correctamente',
                'clave_acceso' => $claveAcceso,
                'detalles' => $totalDetalles
            ];

        } catch (Exception $e) {
            $resultados['errores']++;
            $resultados['detalles'][] = [
                'archivo' => basename($archivo),
                'estado' => 'error',
                'mensaje' => $e->getMessage()
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $resultados,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ]);
}
?> 