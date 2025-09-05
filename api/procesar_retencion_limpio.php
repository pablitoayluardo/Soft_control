<?php
// =====================================================
// PROCESADOR DE XML DE RETENCIONES - VERSIÓN LIMPIA
// =====================================================

// Función para devolver respuesta JSON y terminar
function returnJsonResponse($data, $httpCode = 200) {
    // Limpiar cualquier salida previa que rompa el JSON
    if (function_exists('ob_get_length') && ob_get_length()) {
        @ob_clean();
    }
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    echo json_encode($data);
    exit();
}

// Habilitar reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    returnJsonResponse(['success' => true, 'message' => 'OPTIONS request handled']);
}

// Log inicial
error_log("=== INICIO PROCESAR_RETENCION_RETE_TABLES LIMPIO ===");
error_log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);

try {
    // Configuración de la base de datos
    require_once '../config.php';
    
    // Inicializar conexión a BD
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    error_log("Conexión a BD establecida");

    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Solo se acepta POST.');
    }

    // Verificar que se haya enviado un archivo
    if (!isset($_FILES['xml_file'])) {
        throw new Exception('No se recibió el archivo XML');
    }

    if ($_FILES['xml_file']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
        ];
        $errorMsg = $uploadErrors[$_FILES['xml_file']['error']] ?? 'Error desconocido en la carga';
        throw new Exception($errorMsg);
    }

    $xmlFile = $_FILES['xml_file'];
    error_log("Archivo recibido: " . $xmlFile['name'] . " (" . $xmlFile['size'] . " bytes)");
    
    // Validar tipo/extension de archivo (compatible con PHP 7.x)
    $fileName = isset($xmlFile['name']) ? (string)$xmlFile['name'] : '';
    $fileType = isset($xmlFile['type']) ? (string)$xmlFile['type'] : '';
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $isXmlByExt = ($extension === 'xml');
    $isXmlByMime = (stripos($fileType, 'xml') !== false);
    if (!$isXmlByExt && !$isXmlByMime) {
        throw new Exception('El archivo debe ser un XML válido (.xml)');
    }

    // Leer contenido del archivo
    $xmlContent = file_get_contents($xmlFile['tmp_name']);
    if (!$xmlContent) {
        throw new Exception('No se pudo leer el contenido del archivo');
    }

    error_log("Contenido XML leído: " . strlen($xmlContent) . " caracteres");

    // Parsear XML
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlContent);
    $xmlErrors = libxml_get_errors();
    libxml_clear_errors();
    
    if (!$xml) {
        $errorMsg = 'El archivo XML no es válido';
        if (!empty($xmlErrors)) {
            $errorMsg .= ': ' . $xmlErrors[0]->message;
        }
        throw new Exception($errorMsg);
    }

    error_log("XML parseado correctamente");

    // Función para convertir fechas
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

    // Verificar que sea un comprobante de retención
    if (!isset($xml->infoTributaria) || !isset($xml->infoCompRetencion)) {
        throw new Exception('El archivo no es un comprobante de retención válido');
    }

    error_log("Comprobante de retención válido detectado");

    // Extraer datos del XML según la estructura real
    $infoTributaria = $xml->infoTributaria;
    $infoCompRetencion = $xml->infoCompRetencion;
    
    // Verificar si ya existe la retención por clave de acceso
    $claveAcceso = (string)$infoTributaria->claveAcceso;
    $stmt = $pdo->prepare("SELECT id FROM rete_cabe WHERE clave_acceso = ?");
    $stmt->execute([$claveAcceso]);
    $retencionExistente = $stmt->fetch();
    
    if ($retencionExistente) {
        returnJsonResponse([
            'success' => false,
            'message' => 'La retención ya existe en la base de datos',
            'retencion_id' => $retencionExistente['id']
        ]);
    }

    // Insertar en rete_cabe con mapeo exacto
    $sqlCabe = "
        INSERT INTO rete_cabe (
            clave_acceso, numero_autorizacion, fecha_autorizacion, ambiente,
            tipo_emision, ruc_emisor, razon_social_emisor, nombre_comercial_emisor,
            establecimiento, punto_emision, secuencial, fecha_emision,
            periodo_fiscal, razon_social_retenido, identificacion_retenido,
            tipo_identificacion, cod_doc
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ";
    
    $datosCabe = [
        (string)$infoTributaria->claveAcceso,                    // clave_acceso
        (string)$xml->numeroAutorizacion,                        // numero_autorizacion
        convertirFecha((string)$xml->fechaAutorizacion),          // fecha_autorizacion
        (string)$infoTributaria->ambiente,                       // ambiente
        (int)$infoTributaria->tipoEmision,                      // tipo_emision
        (string)$infoTributaria->ruc,                           // ruc_emisor
        (string)$infoTributaria->razonSocial,                   // razon_social_emisor
        (string)$infoTributaria->nombreComercial,               // nombre_comercial_emisor
        (string)$infoTributaria->estab,                         // establecimiento
        (string)$infoTributaria->ptoEmi,                        // punto_emision
        (string)$infoTributaria->secuencial,                    // secuencial
        convertirFecha((string)$infoCompRetencion->fechaEmision), // fecha_emision
        (string)$infoCompRetencion->periodoFiscal,              // periodo_fiscal
        (string)$infoCompRetencion->razonSocialSujetoRetenido,  // razon_social_retenido
        (string)$infoCompRetencion->identificacionSujetoRetenido, // identificacion_retenido
        (string)$infoCompRetencion->tipoIdentificacionSujetoRetenido, // tipo_identificacion
        (string)$infoTributaria->codDoc                         // cod_doc
    ];
    
    $stmt = $pdo->prepare($sqlCabe);
    $stmt->execute($datosCabe);
    $idRetencion = $pdo->lastInsertId();
    
    error_log("Retención insertada con ID: $idRetencion");

    // Procesar detalles de impuestos
    $detallesInsertados = 0;
    if (isset($xml->impuestos) && isset($xml->impuestos->impuesto)) {
        foreach ($xml->impuestos->impuesto as $impuesto) {
            // Insertar en rete_deta con mapeo exacto
            $sqlDeta = "
                INSERT INTO rete_deta (
                    id_rete_cabe, cod_sustento, cod_doc_sustento, num_doc_sustento,
                    fecha_emision_doc_sustento, codigo_retencion, base_imponible,
                    porcentaje_retener, valor_retenido
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            
            $datosDeta = [
                $idRetencion,                                    // id_rete_cabe
                (string)$impuesto->codSustento,                  // cod_sustento
                (string)$impuesto->codDocSustento,               // cod_doc_sustento
                (string)$impuesto->numDocSustento,               // num_doc_sustento
                convertirFecha((string)$impuesto->fechaEmisionDocSustento), // fecha_emision_doc_sustento
                (string)$impuesto->codigoRetencion,              // codigo_retencion
                (float)$impuesto->baseImponible,                 // base_imponible
                (float)$impuesto->porcentajeRetener,             // porcentaje_retener
                (float)$impuesto->valorRetenido                  // valor_retenido
            ];
            
            $stmt = $pdo->prepare($sqlDeta);
            $stmt->execute($datosDeta);
            $detallesInsertados++;
            
            error_log("Detalle insertado: " . $impuesto->numDocSustento);
        }
    }

    // Verificar resultado final
    $stmt = $pdo->prepare("
        SELECT 
            rc.id,
            rc.clave_acceso,
            rc.numero_autorizacion,
            rc.razon_social_emisor,
            rc.razon_social_retenido,
            COUNT(rd.id) as total_detalles
        FROM rete_cabe rc
        LEFT JOIN rete_deta rd ON rc.id = rd.id_rete_cabe
        WHERE rc.id = ?
        GROUP BY rc.id
    ");
    $stmt->execute([$idRetencion]);
    $resultado = $stmt->fetch();

    error_log("Procesamiento completado. Retención ID: $idRetencion, Detalles: $detallesInsertados");

    returnJsonResponse([
        'success' => true,
        'message' => 'Retención procesada exitosamente',
        'data' => [
            'retencion_id' => $resultado['id'],
            'clave_acceso' => $resultado['clave_acceso'],
            'numero_autorizacion' => $resultado['numero_autorizacion'],
            'emisor' => $resultado['razon_social_emisor'],
            'retenido' => $resultado['razon_social_retenido'],
            'total_detalles' => $resultado['total_detalles']
        ]
    ]);

} catch (Exception $e) {
    error_log("Error en procesar_retencion_rete_tables: " . $e->getMessage());
    returnJsonResponse([
        'success' => false,
        'message' => 'Error al procesar la retención: ' . $e->getMessage()
    ], 500);
} catch (PDOException $e) {
    error_log("Error de base de datos en procesar_retencion_rete_tables: " . $e->getMessage());
    returnJsonResponse([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ], 500);
}
?>
