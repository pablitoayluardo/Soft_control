<?php
// =====================================================
// SCRIPT DE VERIFICACIÓN DEL SISTEMA DE PAGOS
// =====================================================

// Configurar headers
header('Content-Type: text/html; charset=utf-8');

// Incluir configuración
require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificación Sistema de Pagos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background: #007bff; color: white; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>";

echo "<div class='header'>
    <h1>🔍 VERIFICACIÓN DEL SISTEMA DE PAGOS</h1>
    <p>Fecha: " . date('Y-m-d H:i:s') . "</p>
</div>";

try {
    // Conectar a la base de datos
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<div class='success'>✅ Conexión a base de datos exitosa</div>";
    
    // =====================================================
    // VERIFICACIÓN 1: ESTRUCTURA DE TABLAS
    // =====================================================
    
    echo "<div class='section'>
        <h2>📋 VERIFICACIÓN DE ESTRUCTURA DE TABLAS</h2>";
    
    // Verificar tabla pagos
    $stmt = $pdo->query("DESCRIBE pagos");
    $pagosColumns = $stmt->fetchAll();
    
    echo "<h3>Tabla 'pagos':</h3>
        <table>
            <tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th></tr>";
    
    $requiredColumns = [
        'id_pago' => 'INT',
        'id_info_factura' => 'INT',
        'estab' => 'VARCHAR',
        'pto_emi' => 'VARCHAR',
        'secuencial' => 'VARCHAR',
        'monto' => 'DECIMAL',
        'forma_pago' => 'ENUM',
        'nombre_banco' => 'VARCHAR',
        'numero_documento' => 'VARCHAR',
        'referencia' => 'VARCHAR',
        'descripcion' => 'TEXT',
        'fecha_pago' => 'DATE',
        'fecha_registro' => 'TIMESTAMP'
    ];
    
    $foundColumns = [];
    foreach ($pagosColumns as $column) {
        echo "<tr>
            <td>{$column['Field']}</td>
            <td>{$column['Type']}</td>
            <td>{$column['Null']}</td>
            <td>{$column['Key']}</td>
            <td>{$column['Default']}</td>
        </tr>";
        $foundColumns[$column['Field']] = $column['Type'];
    }
    echo "</table>";
    
    // Verificar columnas requeridas
    $missingColumns = array_diff_key($requiredColumns, $foundColumns);
    if (empty($missingColumns)) {
        echo "<div class='success'>✅ Todas las columnas requeridas están presentes en la tabla 'pagos'</div>";
    } else {
        echo "<div class='error'>❌ Faltan columnas: " . implode(', ', array_keys($missingColumns)) . "</div>";
    }
    
    // Verificar tabla info_factura
    $stmt = $pdo->query("DESCRIBE info_factura");
    $infoFacturaColumns = $stmt->fetchAll();
    
    echo "<h3>Tabla 'info_factura':</h3>
        <table>
            <tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th></tr>";
    
    foreach ($infoFacturaColumns as $column) {
        echo "<tr>
            <td>{$column['Field']}</td>
            <td>{$column['Type']}</td>
            <td>{$column['Null']}</td>
            <td>{$column['Key']}</td>
            <td>{$column['Default']}</td>
        </tr>";
    }
    echo "</table>";
    
    // Verificar columnas críticas de info_factura
    $criticalColumns = ['id_info_factura', 'id_info_tributaria', 'valor_pagado', 'estatus'];
    $foundCriticalColumns = array_column($infoFacturaColumns, 'Field');
    $missingCritical = array_diff($criticalColumns, $foundCriticalColumns);
    
    if (empty($missingCritical)) {
        echo "<div class='success'>✅ Todas las columnas críticas están presentes en 'info_factura'</div>";
    } else {
        echo "<div class='error'>❌ Faltan columnas críticas: " . implode(', ', $missingCritical) . "</div>";
    }
    
    echo "</div>";
    
    // =====================================================
    // VERIFICACIÓN 2: DATOS DE FACTURAS
    // =====================================================
    
    echo "<div class='section'>
        <h2>📄 VERIFICACIÓN DE DATOS DE FACTURAS</h2>";
    
    // Contar facturas totales
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_factura");
    $totalFacturas = $stmt->fetchColumn();
    echo "<div class='info'>📊 Total de facturas: $totalFacturas</div>";
    
    // Contar facturas por estatus
    $stmt = $pdo->query("SELECT estatus, COUNT(*) as cantidad FROM info_factura GROUP BY estatus");
    $estatusCount = $stmt->fetchAll();
    
    echo "<h3>Distribución por estatus:</h3>
        <table>
            <tr><th>Estatus</th><th>Cantidad</th></tr>";
    
    foreach ($estatusCount as $row) {
        $color = ($row['estatus'] == 'REGISTRADO') ? 'success' : 'warning';
        echo "<tr><td class='$color'>{$row['estatus']}</td><td>{$row['cantidad']}</td></tr>";
    }
    echo "</table>";
    
    // Mostrar facturas con saldo pendiente
    $stmt = $pdo->query("
        SELECT 
            it.estab,
            it.pto_emi,
            it.secuencial,
            f.razon_social_comprador,
            f.importe_total,
            f.valor_pagado,
            f.estatus,
            (f.importe_total - COALESCE(f.valor_pagado, 0)) as saldo
        FROM info_factura f 
        JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
        WHERE f.importe_total > COALESCE(f.valor_pagado, 0)
        ORDER BY f.fecha_emision DESC
        LIMIT 10
    ");
    $facturasPendientes = $stmt->fetchAll();
    
    echo "<h3>Facturas con saldo pendiente (Top 10):</h3>
        <table>
            <tr><th>Factura</th><th>Cliente</th><th>Total</th><th>Pagado</th><th>Saldo</th><th>Estatus</th></tr>";
    
    foreach ($facturasPendientes as $factura) {
        $numeroFactura = $factura['estab'] . '-' . $factura['pto_emi'] . '-' . $factura['secuencial'];
        $color = ($factura['estatus'] == 'REGISTRADO') ? 'success' : 'warning';
        echo "<tr>
            <td><strong>$numeroFactura</strong></td>
            <td>{$factura['razon_social_comprador']}</td>
            <td>\${$factura['importe_total']}</td>
            <td>\${$factura['valor_pagado']}</td>
            <td class='error'>\${$factura['saldo']}</td>
            <td class='$color'>{$factura['estatus']}</td>
        </tr>";
    }
    echo "</table>";
    
    echo "</div>";
    
    // =====================================================
    // VERIFICACIÓN 3: TABLA DE PAGOS
    // =====================================================
    
    echo "<div class='section'>
        <h2>💰 VERIFICACIÓN DE TABLA DE PAGOS</h2>";
    
    // Contar pagos registrados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pagos");
    $totalPagos = $stmt->fetchColumn();
    echo "<div class='info'>📊 Total de pagos registrados: $totalPagos</div>";
    
    if ($totalPagos == 0) {
        echo "<div class='success'>✅ Tabla de pagos está limpia (sin registros)</div>";
    } else {
        echo "<div class='warning'>⚠️ Hay $totalPagos pagos registrados</div>";
        
        // Mostrar pagos recientes
        $stmt = $pdo->query("
            SELECT 
                p.id_pago,
                p.estab,
                p.pto_emi,
                p.secuencial,
                p.monto,
                p.forma_pago,
                p.fecha_pago,
                p.fecha_registro
            FROM pagos p
            ORDER BY p.fecha_registro DESC
            LIMIT 5
        ");
        $pagosRecientes = $stmt->fetchAll();
        
        echo "<h3>Pagos recientes:</h3>
            <table>
                <tr><th>ID</th><th>Factura</th><th>Monto</th><th>Método</th><th>Fecha Pago</th><th>Fecha Registro</th></tr>";
        
        foreach ($pagosRecientes as $pago) {
            $numeroFactura = $pago['estab'] . '-' . $pago['pto_emi'] . '-' . $pago['secuencial'];
            echo "<tr>
                <td>{$pago['id_pago']}</td>
                <td><strong>$numeroFactura</strong></td>
                <td>\${$pago['monto']}</td>
                <td>{$pago['forma_pago']}</td>
                <td>{$pago['fecha_pago']}</td>
                <td>{$pago['fecha_registro']}</td>
            </tr>";
        }
        echo "</table>";
    }
    
    echo "</div>";
    
    // =====================================================
    // VERIFICACIÓN 4: PRUEBA DE API
    // =====================================================
    
    echo "<div class='section'>
        <h2>🔌 VERIFICACIÓN DE APIs</h2>";
    
    // Probar API get_fact_pago.php
    if (file_exists('api/get_fact_pago.php')) {
        echo "<div class='success'>✅ Archivo api/get_fact_pago.php existe</div>";
        
        // Verificar que el archivo es legible
        if (is_readable('api/get_fact_pago.php')) {
            echo "<div class='success'>✅ Archivo api/get_fact_pago.php es legible</div>";
        } else {
            echo "<div class='error'>❌ Archivo api/get_fact_pago.php no es legible</div>";
        }
        
        // Verificar permisos del archivo
        $perms = fileperms('api/get_fact_pago.php');
        $perms = substr(sprintf('%o', $perms), -4);
        echo "<div class='info'>📋 Permisos de api/get_fact_pago.php: $perms</div>";
        
        // Verificar sintaxis básica (sin shell_exec)
        $content = file_get_contents('api/get_fact_pago.php');
        if (strpos($content, '<?php') !== false && strpos($content, '?>') !== false) {
            echo "<div class='success'>✅ Estructura PHP básica correcta</div>";
        } else {
            echo "<div class='warning'>⚠️ Posibles problemas en estructura PHP</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Archivo api/get_fact_pago.php no existe</div>";
    }
    
    // Verificar archivo registrar_pago.php
    if (file_exists('api/registrar_pago.php')) {
        echo "<div class='success'>✅ Archivo api/registrar_pago.php existe</div>";
        
        // Verificar que el archivo es legible
        if (is_readable('api/registrar_pago.php')) {
            echo "<div class='success'>✅ Archivo api/registrar_pago.php es legible</div>";
        } else {
            echo "<div class='error'>❌ Archivo api/registrar_pago.php no es legible</div>";
        }
        
        // Verificar permisos del archivo
        $perms = fileperms('api/registrar_pago.php');
        $perms = substr(sprintf('%o', $perms), -4);
        echo "<div class='info'>📋 Permisos de api/registrar_pago.php: $perms</div>";
        
        // Verificar sintaxis básica (sin shell_exec)
        $content = file_get_contents('api/registrar_pago.php');
        if (strpos($content, '<?php') !== false && strpos($content, '?>') !== false) {
            echo "<div class='success'>✅ Estructura PHP básica correcta</div>";
        } else {
            echo "<div class='warning'>⚠️ Posibles problemas en estructura PHP</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Archivo api/registrar_pago.php no existe</div>";
    }
    
    echo "</div>";
    
    // =====================================================
    // VERIFICACIÓN 5: RESUMEN FINAL
    // =====================================================
    
    echo "<div class='section'>
        <h2>📋 RESUMEN DE VERIFICACIÓN</h2>";
    
    $errores = [];
    $advertencias = [];
    $exitos = [];
    
    // Evaluar resultados
    if (!empty($missingColumns)) {
        $errores[] = "Faltan columnas en tabla pagos";
    } else {
        $exitos[] = "Estructura de tabla pagos correcta";
    }
    
    if (!empty($missingCritical)) {
        $errores[] = "Faltan columnas críticas en info_factura";
    } else {
        $exitos[] = "Estructura de tabla info_factura correcta";
    }
    
    if ($totalFacturas > 0) {
        $exitos[] = "Hay facturas disponibles para pagos";
    } else {
        $exitos[] = "Sistema completamente limpio (sin facturas)";
    }
    
    if ($totalPagos == 0) {
        $exitos[] = "Sistema completamente limpio (sin pagos)";
    } else {
        $advertencias[] = "Hay pagos registrados en el sistema";
    }
    
    if (file_exists('api/get_fact_pago.php') && file_exists('api/registrar_pago.php')) {
        $exitos[] = "Archivos de API presentes";
    } else {
        $errores[] = "Faltan archivos de API";
    }
    
    // Mostrar resumen
    if (!empty($exitos)) {
        echo "<h3>✅ Éxitos:</h3><ul>";
        foreach ($exitos as $exito) {
            echo "<li class='success'>$exito</li>";
        }
        echo "</ul>";
    }
    
    if (!empty($advertencias)) {
        echo "<h3>⚠️ Advertencias:</h3><ul>";
        foreach ($advertencias as $advertencia) {
            echo "<li class='warning'>$advertencia</li>";
        }
        echo "</ul>";
    }
    
    if (!empty($errores)) {
        echo "<h3>❌ Errores:</h3><ul>";
        foreach ($errores as $error) {
            echo "<li class='error'>$error</li>";
        }
        echo "</ul>";
    }
    
    // Estado general
    if (empty($errores)) {
        echo "<div class='success' style='font-size: 18px; padding: 15px; background: #d4edda; border-radius: 5px;'>
            🎉 SISTEMA DE PAGOS LISTO PARA USAR
        </div>";
    } else {
        echo "<div class='error' style='font-size: 18px; padding: 15px; background: #f8d7da; border-radius: 5px;'>
            ⚠️ HAY PROBLEMAS QUE NECESITAN ATENCIÓN
        </div>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>
        <h2>❌ ERROR DE CONEXIÓN</h2>
        <p>Error: " . $e->getMessage() . "</p>
        <p>Verifica la configuración en config.php</p>
    </div>";
}

echo "</body></html>";
?>
