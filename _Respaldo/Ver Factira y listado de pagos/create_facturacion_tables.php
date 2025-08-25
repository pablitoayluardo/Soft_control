<?php
// =====================================================
// CREACIÓN DE TABLAS DE FACTURACIÓN SRI
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🏢 Creando Tablas de Facturación SRI</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
    
    // =====================================================
    // CREAR TABLAS ESPECÍFICAS PARA SRI
    // =====================================================
    
    // Tabla de información tributaria
    $sql = "CREATE TABLE IF NOT EXISTS info_tributaria (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ambiente ENUM('1', '2') NOT NULL COMMENT '1=Pruebas, 2=Producción',
        tipo_emision ENUM('1', '2') NOT NULL COMMENT '1=Normal, 2=Indisponibilidad',
        razon_social VARCHAR(200) NOT NULL,
        nombre_comercial VARCHAR(200),
        ruc VARCHAR(13) NOT NULL,
        clave_acceso VARCHAR(100) UNIQUE NOT NULL,
        cod_doc VARCHAR(2) NOT NULL COMMENT '01=Factura, 04=Nota Crédito',
        estab VARCHAR(3) NOT NULL,
        pto_emi VARCHAR(3) NOT NULL,
        secuencial VARCHAR(9) NOT NULL,
        dir_matriz TEXT,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla info_tributaria creada</p>";
    
    // Tabla de información de factura
    $sql = "CREATE TABLE IF NOT EXISTS info_factura (
        id INT AUTO_INCREMENT PRIMARY KEY,
        info_tributaria_id INT NOT NULL,
        fecha_emision DATE NOT NULL,
        dir_establecimiento TEXT,
        obligado_contabilidad ENUM('SI', 'NO') DEFAULT 'NO',
        tipo_identificacion_comprador VARCHAR(2) NOT NULL COMMENT '04=RUC, 05=Cédula, 06=Pasaporte',
        razon_social_comprador VARCHAR(200) NOT NULL,
        identificacion_comprador VARCHAR(13) NOT NULL,
        direccion_comprador TEXT,
        total_sin_impuestos DECIMAL(10,2) DEFAULT 0.00,
        total_descuento DECIMAL(10,2) DEFAULT 0.00,
        total_impuestos DECIMAL(10,2) DEFAULT 0.00,
        propina DECIMAL(10,2) DEFAULT 0.00,
        importe_total DECIMAL(10,2) NOT NULL,
        moneda VARCHAR(10) DEFAULT 'DOLAR',
        forma_pago VARCHAR(2) COMMENT '01=Efectivo, 20=Transferencia',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (info_tributaria_id) REFERENCES info_tributaria(id)
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla info_factura creada</p>";
    
    // Tabla de detalles de factura
    $sql = "CREATE TABLE IF NOT EXISTS detalle_factura_sri (
        id INT AUTO_INCREMENT PRIMARY KEY,
        info_factura_id INT NOT NULL,
        codigo_principal VARCHAR(50) NOT NULL,
        descripcion TEXT NOT NULL,
        cantidad DECIMAL(10,2) NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        descuento DECIMAL(10,2) DEFAULT 0.00,
        precio_total_sin_impuesto DECIMAL(10,2) NOT NULL,
        codigo_impuesto VARCHAR(2) COMMENT '2=IVA',
        codigo_porcentaje VARCHAR(2) COMMENT '4=15%',
        tarifa DECIMAL(5,2) COMMENT '15.00',
        base_imponible DECIMAL(10,2) NOT NULL,
        valor_impuesto DECIMAL(10,2) NOT NULL,
        informacion_adicional TEXT,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (info_factura_id) REFERENCES info_factura(id)
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla detalle_factura_sri creada</p>";
    
    // Tabla de información adicional
    $sql = "CREATE TABLE IF NOT EXISTS info_adicional_factura (
        id INT AUTO_INCREMENT PRIMARY KEY,
        info_factura_id INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        valor TEXT,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (info_factura_id) REFERENCES info_factura(id)
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla info_adicional_factura creada</p>";
    
    // =====================================================
    // CARGAR DATOS DEL XML
    // =====================================================
    
    echo "<h3>📄 Cargando datos del XML...</h3>";
    
    // Leer el archivo XML
    $xmlFile = 'xml/fact_gc/3007202501172164244300120021000000018661440544518.xml';
    $xmlContent = file_get_contents($xmlFile);
    
    if (!$xmlContent) {
        throw new Exception('No se pudo leer el archivo XML');
    }
    
    // Parsear XML
    $xml = simplexml_load_string($xmlContent);
    
    if (!$xml) {
        throw new Exception('Error al parsear el XML');
    }
    
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
    
    // =====================================================
    // VERIFICAR DATOS INSERTADOS
    // =====================================================
    
    echo "<h3>📊 Verificación de datos insertados:</h3>";
    
    $checks = [
        'info_tributaria' => 'SELECT COUNT(*) as total FROM info_tributaria',
        'info_factura' => 'SELECT COUNT(*) as total FROM info_factura',
        'detalle_factura_sri' => 'SELECT COUNT(*) as total FROM detalle_factura_sri',
        'info_adicional_factura' => 'SELECT COUNT(*) as total FROM info_adicional_factura'
    ];
    
    foreach ($checks as $table => $query) {
        $stmt = $pdo->query($query);
        $count = $stmt->fetch()['total'];
        echo "<p><strong>$table:</strong> $count registros</p>";
    }
    
    // Mostrar detalles de la factura
    echo "<h3>📋 Detalles de la factura cargada:</h3>";
    
    $sql = "SELECT 
        it.razon_social,
        it.nombre_comercial,
        it.ruc,
        it.clave_acceso,
        if.fecha_emision,
        if.razon_social_comprador,
        if.identificacion_comprador,
        if.importe_total,
        if.moneda
    FROM info_tributaria it
    JOIN info_factura if ON it.id = if.info_tributaria_id
    WHERE it.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$infoTributariaId]);
    $factura = $stmt->fetch();
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Emisor:</strong> " . $factura['razon_social'] . " (" . $factura['nombre_comercial'] . ")</p>";
    echo "<p><strong>RUC:</strong> " . $factura['ruc'] . "</p>";
    echo "<p><strong>Clave de Acceso:</strong> " . $factura['clave_acceso'] . "</p>";
    echo "<p><strong>Fecha:</strong> " . $factura['fecha_emision'] . "</p>";
    echo "<p><strong>Comprador:</strong> " . $factura['razon_social_comprador'] . "</p>";
    echo "<p><strong>Identificación:</strong> " . $factura['identificacion_comprador'] . "</p>";
    echo "<p><strong>Total:</strong> $" . $factura['importe_total'] . " " . $factura['moneda'] . "</p>";
    echo "</div>";
    
    echo "<h3>🎯 Sistema de facturación listo:</h3>";
    echo "<p><a href='index.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Probar Login</a></p>";
    echo "<p><a href='dashboard.html' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 Acceder al Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Creación de tablas de facturación completada - Sistema de Control GloboCity</em></p>";
?> 