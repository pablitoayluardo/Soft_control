<?php
// =====================================================
// PROCESADOR DE XML DE RETENCIONES - NUEVAS TABLAS
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
error_log("=== INICIO PROCESAR_RETENCION_NUEVAS_TABLAS ===");
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

    // Verificar que se recibió el archivo
    if (!isset($_FILES['xmlFile']) || $_FILES['xmlFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se recibió el archivo XML o hubo un error en la subida.');
    }

    $archivo = $_FILES['xmlFile'];
    error_log("Archivo recibido: " . $archivo['name'] . " (" . $archivo['size'] . " bytes)");

    // Verificar extensión del archivo
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if ($extension !== 'xml') {
        throw new Exception('El archivo debe ser un XML válido.');
    }

    // Leer contenido del archivo
    $xmlContent = file_get_contents($archivo['tmp_name']);
    if ($xmlContent === false) {
        throw new Exception('No se pudo leer el contenido del archivo XML.');
    }

    error_log("Contenido XML leído: " . strlen($xmlContent) . " caracteres");

    // Parsear XML
    $xml = simplexml_load_string($xmlContent);
    if ($xml === false) {
        throw new Exception('El archivo no es un XML válido.');
    }

    error_log("XML parseado correctamente");

    // Verificar que sea un comprobante de retención
    $rootTag = $xml->getName();
    error_log("Tag raíz del XML: " . $rootTag);

    if ($rootTag !== 'autorizacion') {
        throw new Exception('El archivo no es una autorización de retención válida.');
    }

    // Extraer datos de autorización
    $estado = (string)$xml->estado;
    $numeroAutorizacion = (string)$xml->numeroAutorizacion;
    $fechaAutorizacionRaw = (string)$xml->fechaAutorizacion;
    $ambiente = (string)$xml->ambiente;
    
    // Convertir fecha a formato MySQL
    $fechaAutorizacion = date('Y-m-d H:i:s', strtotime($fechaAutorizacionRaw));

    error_log("Datos de autorización extraídos: estado=$estado, numero=$numeroAutorizacion");

    // Extraer comprobante del CDATA
    $comprobanteElement = $xml->comprobante;
    if (!$comprobanteElement) {
        throw new Exception('No se encontró el elemento comprobante en la autorización.');
    }

    $comprobanteContent = (string)$comprobanteElement;
    error_log("Contenido CDATA extraído: " . strlen($comprobanteContent) . " caracteres");

    // Parsear el comprobante
    $comprobante = simplexml_load_string($comprobanteContent);
    if ($comprobante === false) {
        throw new Exception('No se pudo parsear el contenido del comprobante.');
    }

    error_log("Comprobante parseado correctamente");

    // Extraer información tributaria
    $infoTributaria = $comprobante->infoTributaria;
    $infoCompRetencion = $comprobante->infoCompRetencion;

    if (!$infoTributaria || !$infoCompRetencion) {
        throw new Exception('Faltan elementos requeridos en el comprobante.');
    }

    // Datos del emisor
    $rucEmisor = (string)$infoTributaria->ruc;
    $razonSocialEmisor = (string)$infoTributaria->razonSocial;
    $nombreComercialEmisor = (string)$infoTributaria->nombreComercial;
    $estab = (string)$infoTributaria->estab;
    $ptoEmi = (string)$infoTributaria->ptoEmi;
    $secuencial = (string)$infoTributaria->secuencial;
    $claveAcceso = (string)$infoTributaria->claveAcceso;

    // Datos del receptor
    $rucReceptor = (string)$infoCompRetencion->identificacionSujetoRetenido;
    $razonSocialReceptor = (string)$infoCompRetencion->razonSocialSujetoRetenido;
    $periodoFiscal = (string)$infoCompRetencion->periodoFiscal;
    $fechaEmisionRaw = (string)$infoCompRetencion->fechaEmision;
    $fechaEmision = date('Y-m-d', strtotime($fechaEmisionRaw));

    error_log("Datos extraídos: emisor=$rucEmisor, receptor=$rucReceptor");

    // Verificar si el comprobante ya existe ANTES de iniciar la transacción
    error_log("Verificando si el comprobante ya existe");
    $numeroComprobante = $estab . '-' . $ptoEmi . '-' . $secuencial;
    
    $stmt = $pdo->prepare("
        SELECT id, numero_comprobante, fecha_emision, emisor.razon_social as emisor_nombre
        FROM ComprobantesRetencion cr
        JOIN Contribuyentes emisor ON cr.emisor_id = emisor.id
        WHERE cr.numero_comprobante = ? OR cr.clave_acceso = ?
    ");
    $stmt->execute([$numeroComprobante, $claveAcceso]);
    $comprobanteExistente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($comprobanteExistente) {
        returnJsonResponse([
            'success' => false,
            'message' => "⚠️ Este comprobante de retención ya fue procesado anteriormente.\n\n" .
                       "📄 Número: " . $comprobanteExistente['numero_comprobante'] . "\n" .
                       "📅 Fecha: " . date('d/m/Y', strtotime($comprobanteExistente['fecha_emision'])) . "\n" .
                       "🏢 Emisor: " . $comprobanteExistente['emisor_nombre'] . "\n\n" .
                       "💡 Si necesitas procesarlo nuevamente, primero elimínalo desde la lista de retenciones."
        ]);
    }

    // Iniciar transacción
    $pdo->beginTransaction();
    error_log("Transacción iniciada");

    try {
        // 1. Insertar o actualizar emisor en Contribuyentes
        $stmt = $pdo->prepare("
            INSERT INTO Contribuyentes (identificacion, razon_social, nombre_comercial, tipo_identificacion) 
            VALUES (?, ?, ?, '04') 
            ON DUPLICATE KEY UPDATE 
                razon_social = VALUES(razon_social),
                nombre_comercial = VALUES(nombre_comercial)
        ");
        $stmt->execute([$rucEmisor, $razonSocialEmisor, $nombreComercialEmisor]);
        $emisorId = $pdo->lastInsertId();
        if ($emisorId == 0) {
            // Si no se insertó (porque ya existía), obtener el ID
            $stmt = $pdo->prepare("SELECT id FROM Contribuyentes WHERE identificacion = ?");
            $stmt->execute([$rucEmisor]);
            $emisorId = $stmt->fetchColumn();
        }
        error_log("Emisor procesado: ID=$emisorId");

        // 2. Insertar o actualizar receptor en Contribuyentes
        $stmt = $pdo->prepare("
            INSERT INTO Contribuyentes (identificacion, razon_social, tipo_identificacion) 
            VALUES (?, ?, '04') 
            ON DUPLICATE KEY UPDATE 
                razon_social = VALUES(razon_social)
        ");
        $stmt->execute([$rucReceptor, $razonSocialReceptor]);
        $receptorId = $pdo->lastInsertId();
        if ($receptorId == 0) {
            // Si no se insertó (porque ya existía), obtener el ID
            $stmt = $pdo->prepare("SELECT id FROM Contribuyentes WHERE identificacion = ?");
            $stmt->execute([$rucReceptor]);
            $receptorId = $stmt->fetchColumn();
        }
        error_log("Receptor procesado: ID=$receptorId");

        // 3. Insertar comprobante de retención
        error_log("Insertando comprobante de retención");
        
        $stmt = $pdo->prepare("
            INSERT INTO ComprobantesRetencion (
                clave_acceso, numero_autorizacion, estado, numero_comprobante,
                fecha_emision, fecha_autorizacion, periodo_fiscal,
                emisor_id, receptor_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $claveAcceso,
            $numeroAutorizacion,
            $estado,
            $numeroComprobante,
            $fechaEmision,
            $fechaAutorizacion,
            $periodoFiscal,
            $emisorId,
            $receptorId
        ]);
        
        $comprobanteId = $pdo->lastInsertId();
        error_log("Comprobante insertado: ID=$comprobanteId");

        // 4. Procesar documentos sustentos
        $docsSustento = $comprobante->docsSustento;
        if ($docsSustento) {
            foreach ($docsSustento->docSustento as $docSustento) {
                $tipoDocSustento = (string)$docSustento->codDocSustento;
                $numeroDocSustento = (string)$docSustento->numDocSustento;
                $fechaEmisionDocSustentoRaw = (string)$docSustento->fechaEmisionDocSustento;
                $fechaEmisionDocSustento = date('Y-m-d', strtotime($fechaEmisionDocSustentoRaw));
                $totalSinImpuestos = (float)$docSustento->totalSinImpuestos;
                $importeTotal = (float)$docSustento->importeTotal;

                // Insertar documento sustentante
                $stmt = $pdo->prepare("
                    INSERT INTO DocumentosSustento (
                        comprobante_retencion_id, tipo_documento_sustento, numero_documento_sustento,
                        fecha_emision_sustento, total_sin_impuestos, importe_total
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $comprobanteId,
                    $tipoDocSustento,
                    $numeroDocSustento,
                    $fechaEmisionDocSustento,
                    $totalSinImpuestos,
                    $importeTotal
                ]);
                
                error_log("Documento sustentante insertado: $numeroDocSustento");

                // 5. Procesar retenciones
                $retenciones = $docSustento->retenciones;
                if ($retenciones) {
                    foreach ($retenciones->retencion as $retencion) {
                        $codigoImpuesto = (string)$retencion->codigo;
                        $codigoRetencion = (string)$retencion->codigoRetencion;
                        $baseImponible = (float)$retencion->baseImponible;
                        $porcentajeRetener = (float)$retencion->porcentajeRetener;
                        $valorRetenido = (float)$retencion->valorRetenido;

                        // Insertar detalle de retención
                        $stmt = $pdo->prepare("
                            INSERT INTO DetalleRetenciones (
                                comprobante_retencion_id, codigo_impuesto, codigo_retencion,
                                base_imponible, porcentaje_retener, valor_retenido
                            ) VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $comprobanteId,
                            $codigoImpuesto,
                            $codigoRetencion,
                            $baseImponible,
                            $porcentajeRetener,
                            $valorRetenido
                        ]);
                        
                        error_log("Retención insertada: código=$codigoRetencion, valor=$valorRetenido");
                    }
                }
            }
        }

        // Confirmar transacción
        $pdo->commit();
        error_log("Transacción confirmada exitosamente");

        // Respuesta exitosa
        returnJsonResponse([
            'success' => true,
            'message' => "✅ ¡Retención procesada exitosamente!\n\n" .
                        "📄 Comprobante: " . $numeroComprobante . "\n" .
                        "🏢 Emisor: " . $razonSocialEmisor . "\n" .
                        "👤 Receptor: " . $razonSocialReceptor . "\n" .
                        "📅 Fecha: " . date('d/m/Y', strtotime($fechaEmision)) . "\n" .
                        "📊 Período: " . $periodoFiscal,
            'data' => [
                'comprobante_id' => $comprobanteId,
                'numero_comprobante' => $numeroComprobante,
                'emisor' => $razonSocialEmisor,
                'receptor' => $razonSocialReceptor,
                'fecha_emision' => $fechaEmision,
                'periodo_fiscal' => $periodoFiscal
            ]
        ]);

    } catch (Exception $e) {
        // Rollback en caso de error
        $pdo->rollback();
        error_log("Error en transacción, rollback ejecutado: " . $e->getMessage());
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    returnJsonResponse([
        'success' => false,
        'message' => "❌ Error al procesar la retención:\n\n" . 
                    "🔍 Detalle: " . $e->getMessage() . "\n\n" .
                    "💡 Verifica que el archivo XML sea válido y no esté corrupto.\n" .
                    "📞 Si el problema persiste, contacta al administrador del sistema."
    ], 500);
}
?>
