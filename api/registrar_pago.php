<?php
// =====================================================
// API PARA REGISTRAR PAGOS DE FACTURAS
// =====================================================

// Configurar headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir configuración
require_once '../config.php';

try {
    // Verificar que las constantes estén definidas
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Configuración de base de datos incompleta');
    }
    
    // Usar las constantes definidas en config.php
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Datos JSON inválidos');
    }
    
    // Debug: Mostrar todos los datos recibidos
    error_log("DEBUG - Datos recibidos: " . json_encode($input));
    
         // Validar campos requeridos
     $camposRequeridos = ['monto', 'metodo_pago', 'fecha_pago'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($input[$campo]) || empty($input[$campo])) {
            throw new Exception("Campo requerido faltante: $campo");
        }
    }
    
         // Validar monto
     $monto = floatval($input['monto']);
     if ($monto <= 0) {
         throw new Exception('El monto debe ser mayor a 0');
     }
     
     // Validar fecha de pago
     $fechaPago = $input['fecha_pago'];
     if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaPago)) {
         throw new Exception('Formato de fecha inválido');
     }
    
         // Validar método de pago
     $metodosPagoValidos = [
         'efectivo', 'tarjeta', 'transferencia', 'cheque', 
         'deposito', 'pago_movil', 'otro'
     ];
     if (!in_array($input['metodo_pago'], $metodosPagoValidos)) {
         throw new Exception('Método de pago no válido');
     }
    
         // Validar que si es transferencia, cheque o depósito, se proporcione institución
     if (in_array($input['metodo_pago'], ['transferencia', 'cheque', 'deposito'])) {
         if (!isset($input['institucion']) || empty($input['institucion'])) {
             throw new Exception('Institución bancaria requerida para este tipo de pago');
         }
     }
    
             // Buscar la factura por ID usando las tablas correctas
    $stmt = $pdo->prepare("
        SELECT 
            f.id_info_factura,
            f.razon_social_comprador,
            f.direccion_comprador,
            f.importe_total,
            COALESCE(f.valor_pagado, 0) as valor_pagado,
            it.id_info_tributaria,
            it.estab,
            it.pto_emi,
            it.secuencial,
            it.clave_acceso
        FROM info_factura f 
        JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
        WHERE it.id_info_tributaria = ?
    ");
    $stmt->execute([$input['id_info_factura']]);
    $factura = $stmt->fetch();
    
    if (!$factura) {
        throw new Exception('Factura no encontrada');
    }
    
    // Calcular el saldo usando la misma lógica que el frontend
    $importeTotal = floatval($factura['importe_total']);
    $valorPagado = floatval($factura['valor_pagado']);
    $saldoActual = $importeTotal - $valorPagado;
    
    // Debug: Mostrar valores para verificar
    error_log("DEBUG - ID Info Tributaria recibido: " . $input['id_info_factura']);
    error_log("DEBUG - ID Info Factura encontrado: " . $factura['id_info_factura']);
    error_log("DEBUG - Factura: " . $factura['estab'] . '-' . $factura['pto_emi'] . '-' . $factura['secuencial']);
    error_log("DEBUG - Importe Total: " . $importeTotal);
    error_log("DEBUG - Valor Pagado: " . $valorPagado);
    error_log("DEBUG - Saldo Calculado: " . $saldoActual);
    error_log("DEBUG - Monto a Pagar: " . $monto);
    
    if ($monto > $saldoActual) {
        throw new Exception("El monto excede el saldo pendiente de la factura. Saldo: $saldoActual, Monto: $monto");
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
                         // Insertar el registro de pago
        $stmt = $pdo->prepare("
            INSERT INTO pagos (
                id_info_factura, estab, pto_emi, secuencial, monto, forma_pago, nombre_banco, 
                numero_documento, referencia, descripcion, fecha_pago
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $factura['id_info_factura'],
            $factura['estab'],
            $factura['pto_emi'],
            $factura['secuencial'],
            $monto,
            $input['metodo_pago'],
            $input['institucion'] ?? null,
            $input['documento'] ?? null,
            $input['referencia'] ?? null,
            $input['observacion'] ?? null,
            $fechaPago
        ]);
        
        $pagoId = $pdo->lastInsertId();
        
                         // Actualizar el saldo y estatus de la factura
        $nuevoSaldo = $saldoActual - $monto;
        $nuevoValorPagado = $valorPagado + $monto;
        
        // Determinar el nuevo estatus basado en el saldo
        $nuevoEstatus = ($nuevoSaldo <= 0) ? 'PAGADA' : 'PENDIENTE';
        
        $stmt = $pdo->prepare("
            UPDATE info_factura 
            SET valor_pagado = ?, estatus = ?
            WHERE id_info_factura = ?
        ");
        $stmt->execute([$nuevoValorPagado, $nuevoEstatus, $factura['id_info_factura']]);
        
                // Registrar en logs de actividad
        $stmt = $pdo->prepare("
            INSERT INTO logs_actividad (
                usuario_id, accion, descripcion, fecha
            ) VALUES (?, ?, ?, NOW())
        ");
        
        $detalles = json_encode([
            'factura' => $factura['estab'] . '-' . $factura['pto_emi'] . '-' . $factura['secuencial'],
            'cliente' => $factura['razon_social_comprador'],
            'monto_pagado' => $monto,
            'metodo_pago' => $input['metodo_pago'],
            'fecha_pago' => $fechaPago,
            'usuario' => $input['usuario'] ?? 'Administrador',
            'saldo_anterior' => $saldoActual,
            'saldo_nuevo' => $nuevoSaldo,
            'estatus_nuevo' => $nuevoEstatus
        ]);
        
        $stmt->execute([
            1, // usuario_id (ajustar según sistema de usuarios)
            'REGISTRAR_PAGO',
            $detalles
        ]);
        
        // Confirmar transacción
        $pdo->commit();
        
                         // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => 'Pago registrado exitosamente',
            'data' => [
                'pago_id' => $pagoId,
                'factura' => $factura['estab'] . '-' . $factura['pto_emi'] . '-' . $factura['secuencial'],
                'monto' => $monto,
                'saldo_restante' => $nuevoSaldo,
                'estatus' => $nuevoEstatus,
                'fecha_pago' => $fechaPago,
                'usuario' => $input['usuario'] ?? 'Administrador'
            ]
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    // Respuesta de error
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}
?>
