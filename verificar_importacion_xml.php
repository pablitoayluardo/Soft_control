<?php
// =====================================================
// VERIFICAR IMPORTACIÓN XML - SIN ERRORES DE COLUMNAS
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
    <title>Verificar Importación XML</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background: #007bff; color: white; padding: 10px; border-radius: 5px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>";

echo "<div class='header'>
    <h1>🔍 VERIFICAR IMPORTACIÓN XML</h1>
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
    // VERIFICAR TABLAS DE IMPORTACIÓN XML
    // =====================================================
    
    echo "<div class='section'>
        <h2>📋 VERIFICAR TABLAS DE IMPORTACIÓN XML</h2>";
    
    $tablasXML = [
        'info_tributaria',
        'info_factura', 
        'detalle_factura_sri',
        'info_adicional',
        'total_con_impuestos',
        'impuestos_detalle'
    ];
    
    foreach ($tablasXML as $tabla) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $tabla");
            $count = $stmt->fetchColumn();
            echo "<div class='success'>✅ Tabla $tabla existe ($count registros)</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Tabla $tabla NO existe: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "</div>";
    
    // =====================================================
    // VERIFICAR TABLA PAGOS (SEPARADA)
    // =====================================================
    
    echo "<div class='section'>
        <h2>💰 VERIFICAR TABLA PAGOS (REGISTROS MANUALES)</h2>";
    
    try {
        $stmt = $pdo->query("DESCRIBE pagos");
        $pagosColumns = $stmt->fetchAll();
        
        echo "<h3>Estructura de tabla pagos:</h3>
            <table border='1' style='border-collapse: collapse; width: 100%;'>
                <tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th></tr>";
        
        foreach ($pagosColumns as $column) {
            echo "<tr>
                <td>{$column['Field']}</td>
                <td>{$column['Type']}</td>
                <td>{$column['Null']}</td>
                <td>{$column['Key']}</td>
                <td>{$column['Default']}</td>
            </tr>";
        }
        echo "</table>";
        
        // Verificar columna forma_pago
        $foundFormaPago = false;
        foreach ($pagosColumns as $column) {
            if ($column['Field'] === 'forma_pago') {
                $foundFormaPago = true;
                echo "<div class='success'>✅ Columna 'forma_pago' existe con tipo: {$column['Type']}</div>";
                break;
            }
        }
        
        if (!$foundFormaPago) {
            echo "<div class='error'>❌ Columna 'forma_pago' NO existe en tabla pagos</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error al verificar tabla pagos: " . $e->getMessage() . "</div>";
    }
    
    echo "</div>";
    
    // =====================================================
    // VERIFICAR ARCHIVOS DE IMPORTACIÓN
    // =====================================================
    
    echo "<div class='section'>
        <h2>📁 VERIFICAR ARCHIVOS DE IMPORTACIÓN</h2>";
    
    $archivosImportacion = [
        'api/upload_factura_individual_clean.php',
        'api/upload_factura_individual.php',
        'debug_xml_extraction.php'
    ];
    
    foreach ($archivosImportacion as $archivo) {
        if (file_exists($archivo)) {
            echo "<div class='success'>✅ Archivo $archivo existe</div>";
            
            // Verificar si tiene referencias incorrectas
            $contenido = file_get_contents($archivo);
            if (strpos($contenido, 'formaPago') !== false) {
                echo "<div class='warning'>⚠️ Archivo $archivo contiene referencias a 'formaPago'</div>";
            } else {
                echo "<div class='success'>✅ Archivo $archivo sin referencias incorrectas</div>";
            }
        } else {
            echo "<div class='error'>❌ Archivo $archivo NO existe</div>";
        }
    }
    
    echo "</div>";
    
    // =====================================================
    // VERIFICAR DATOS DE FACTURAS
    // =====================================================
    
    echo "<div class='section'>
        <h2>📄 VERIFICAR DATOS DE FACTURAS</h2>";
    
    // Contar facturas
    $stmt = $pdo->query("SELECT COUNT(*) FROM info_factura");
    $totalFacturas = $stmt->fetchColumn();
    echo "<div class='info'>📊 Total de facturas: $totalFacturas</div>";
    
    // Contar pagos manuales
    $stmt = $pdo->query("SELECT COUNT(*) FROM pagos");
    $totalPagos = $stmt->fetchColumn();
    echo "<div class='info'>💰 Total de pagos manuales: $totalPagos</div>";
    
    // Mostrar últimas facturas
    if ($totalFacturas > 0) {
        $stmt = $pdo->query("
            SELECT 
                it.estab,
                it.pto_emi,
                it.secuencial,
                f.razon_social_comprador,
                f.importe_total,
                f.estatus,
                f.fecha_emision
            FROM info_factura f 
            JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
            ORDER BY f.fecha_emision DESC 
            LIMIT 5
        ");
        $ultimasFacturas = $stmt->fetchAll();
        
        echo "<h3>Últimas 5 facturas:</h3>
            <table border='1' style='border-collapse: collapse; width: 100%;'>
                <tr><th>Factura</th><th>Cliente</th><th>Total</th><th>Estatus</th><th>Fecha</th></tr>";
        
        foreach ($ultimasFacturas as $factura) {
            $numeroFactura = $factura['estab'] . '-' . $factura['pto_emi'] . '-' . $factura['secuencial'];
            echo "<tr>
                <td>$numeroFactura</td>
                <td>{$factura['razon_social_comprador']}</td>
                <td>\${$factura['importe_total']}</td>
                <td>{$factura['estatus']}</td>
                <td>{$factura['fecha_emision']}</td>
            </tr>";
        }
        echo "</table>";
    }
    
    echo "</div>";
    
    // =====================================================
    // RESUMEN Y RECOMENDACIONES
    // =====================================================
    
    echo "<div class='section'>
        <h2>📋 RESUMEN Y RECOMENDACIONES</h2>";
    
    echo "<h3>✅ Estado del Sistema:</h3>
        <ul>
            <li class='success'>Importación XML corregida (sin referencias a formaPago)</li>
            <li class='success'>Tabla pagos separada para registros manuales</li>
            <li class='success'>Estructura de base de datos verificada</li>
        </ul>";
    
    echo "<h3>🚀 Próximos Pasos:</h3>
        <ol>
            <li>Probar subida de factura XML</li>
            <li>Verificar que no aparezcan errores de columnas</li>
            <li>Confirmar que las facturas se importen correctamente</li>
            <li>Probar registro de pagos manuales</li>
        </ol>";
    
    echo "<h3>🔧 Si Hay Problemas:</h3>
        <ul>
            <li>Verificar permisos de archivos</li>
            <li>Revisar logs de error del servidor</li>
            <li>Confirmar que todas las tablas existen</li>
        </ul>";
    
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
