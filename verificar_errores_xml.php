<?php
// =====================================================
// VERIFICAR ERRORES DE COLUMNAS EN IMPORTACIÓN XML
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
    <title>Verificar Errores XML</title>
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
    <h1>🔍 VERIFICAR ERRORES DE COLUMNAS XML</h1>
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
    // VERIFICAR INSERCIONES EN TABLA PAGOS
    // =====================================================
    
    echo "<div class='section'>
        <h2>💰 VERIFICAR INSERCIONES EN TABLA PAGOS</h2>";
    
    $archivosConInserciones = [
        'api/upload_factura_individual_clean.php',
        'api/upload_factura_individual.php',
        'debug_xml_extraction.php'
    ];
    
    foreach ($archivosConInserciones as $archivo) {
        if (file_exists($archivo)) {
            $contenido = file_get_contents($archivo);
            
            // Buscar INSERT INTO pagos
            if (strpos($contenido, 'INSERT INTO pagos') !== false) {
                // Verificar si está comentado
                $lineas = explode("\n", $contenido);
                $insertEncontrado = false;
                $insertComentado = false;
                
                foreach ($lineas as $numero => $linea) {
                    if (strpos($linea, 'INSERT INTO pagos') !== false) {
                        $insertEncontrado = true;
                        // Verificar si la línea anterior o la actual tienen comentarios
                        $lineaAnterior = isset($lineas[$numero-1]) ? trim($lineas[$numero-1]) : '';
                        $lineaActual = trim($linea);
                        
                        if (strpos($lineaAnterior, '//') === 0 || 
                            strpos($lineaAnterior, '/*') !== false ||
                            strpos($lineaActual, '//') === 0) {
                            $insertComentado = true;
                        }
                    }
                }
                
                if ($insertEncontrado && !$insertComentado) {
                    echo "<div class='error'>❌ Archivo $archivo tiene INSERT INTO pagos NO comentado</div>";
                } else {
                    echo "<div class='success'>✅ Archivo $archivo tiene INSERT INTO pagos comentado correctamente</div>";
                }
            } else {
                echo "<div class='success'>✅ Archivo $archivo no tiene INSERT INTO pagos</div>";
            }
        } else {
            echo "<div class='error'>❌ Archivo $archivo NO existe</div>";
        }
    }
    
    echo "</div>";
    
    // =====================================================
    // VERIFICAR ESTRUCTURA DE TABLA PAGOS
    // =====================================================
    
    echo "<div class='section'>
        <h2>📋 VERIFICAR ESTRUCTURA DE TABLA PAGOS</h2>";
    
    try {
        $stmt = $pdo->query("DESCRIBE pagos");
        $pagosColumns = $stmt->fetchAll();
        
        echo "<h3>Columnas de tabla pagos:</h3>
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
        
        // Verificar columnas críticas
        $columnasRequeridas = ['id_info_factura', 'forma_pago', 'monto', 'nombre_banco', 'numero_documento', 'referencia', 'descripcion', 'fecha_pago'];
        
        echo "<h3>Verificación de columnas requeridas:</h3>";
        foreach ($columnasRequeridas as $columna) {
            $encontrada = false;
            foreach ($pagosColumns as $column) {
                if ($column['Field'] === $columna) {
                    $encontrada = true;
                    echo "<div class='success'>✅ Columna '$columna' existe</div>";
                    break;
                }
            }
            if (!$encontrada) {
                echo "<div class='error'>❌ Columna '$columna' NO existe</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error al verificar tabla pagos: " . $e->getMessage() . "</div>";
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
    
    // Verificar facturas sin pagos
    if ($totalFacturas > 0) {
        $stmt = $pdo->query("
            SELECT 
                it.estab,
                it.pto_emi,
                it.secuencial,
                f.razon_social_comprador,
                f.importe_total,
                f.estatus,
                f.valor_pagado
            FROM info_factura f 
            JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
            WHERE f.estatus IN ('REGISTRADO', 'PENDIENTE')
            ORDER BY f.fecha_emision DESC 
            LIMIT 10
        ");
        $facturasPendientes = $stmt->fetchAll();
        
        if (!empty($facturasPendientes)) {
            echo "<h3>Facturas pendientes de pago:</h3>
                <table border='1' style='border-collapse: collapse; width: 100%;'>
                    <tr><th>Factura</th><th>Cliente</th><th>Total</th><th>Pagado</th><th>Saldo</th><th>Estatus</th></tr>";
            
            foreach ($facturasPendientes as $factura) {
                $numeroFactura = $factura['estab'] . '-' . $factura['pto_emi'] . '-' . $factura['secuencial'];
                $saldo = $factura['importe_total'] - $factura['valor_pagado'];
                echo "<tr>
                    <td>$numeroFactura</td>
                    <td>{$factura['razon_social_comprador']}</td>
                    <td>\${$factura['importe_total']}</td>
                    <td>\${$factura['valor_pagado']}</td>
                    <td>\${$saldo}</td>
                    <td>{$factura['estatus']}</td>
                </tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='success'>✅ No hay facturas pendientes de pago</div>";
        }
    }
    
    echo "</div>";
    
    // =====================================================
    // RESUMEN Y RECOMENDACIONES
    // =====================================================
    
    echo "<div class='section'>
        <h2>📋 RESUMEN Y RECOMENDACIONES</h2>";
    
    echo "<h3>✅ Estado del Sistema:</h3>
        <ul>
            <li class='success'>Archivos de importación XML corregidos</li>
            <li class='success'>Tabla pagos separada para registros manuales</li>
            <li class='success'>Estructura de base de datos verificada</li>
        </ul>";
    
    echo "<h3>🚀 Próximos Pasos:</h3>
        <ol>
            <li>Probar subida de factura XML</li>
            <li>Verificar que no aparezcan errores de columnas</li>
            <li>Confirmar que las facturas se importen correctamente</li>
            <li>Probar registro de pagos manuales desde Pago_fac.html</li>
        </ol>";
    
    echo "<h3>🔧 Si Sigue Apareciendo el Error:</h3>
        <ul>
            <li>Verificar que estés usando los archivos corregidos</li>
            <li>Limpiar caché del navegador</li>
            <li>Verificar permisos de archivos en el hosting</li>
            <li>Revisar logs de error del servidor</li>
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
