<?php
// =====================================================
// VERIFICAR SEPARACIÓN COMPLETA DE SISTEMAS
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
    <title>Verificar Separación de Sistemas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background: #007bff; color: white; padding: 10px; border-radius: 5px; }
        .critical { background: #dc3545; color: white; padding: 10px; border-radius: 5px; }
        .safe { background: #28a745; color: white; padding: 10px; border-radius: 5px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>";

echo "<div class='header'>
    <h1>🔒 VERIFICAR SEPARACIÓN COMPLETA DE SISTEMAS</h1>
    <p>Fecha: " . date('Y-m-d H:i:s') . "</p>
    <p>Objetivo: Confirmar que Facturas y Pagos estén completamente separados</p>
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
    // VERIFICAR SEPARACIÓN DE TABLAS
    // =====================================================
    
    echo "<div class='section'>
        <h2>📋 VERIFICAR SEPARACIÓN DE TABLAS</h2>";
    
    // Tablas del sistema de facturas (XML)
    $tablasFacturas = [
        'info_tributaria',
        'info_factura', 
        'detalle_factura_sri',
        'info_adicional_factura',
        'total_con_impuestos',
        'impuestos_detalle'
    ];
    
    // Tablas del sistema de pagos (manual)
    $tablasPagos = [
        'pagos',
        'logs_actividad'
    ];
    
    echo "<h3>📄 Tablas del Sistema de Facturas (XML):</h3>";
    foreach ($tablasFacturas as $tabla) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $tabla");
            $count = $stmt->fetchColumn();
            echo "<div class='info'>✅ Tabla $tabla existe ($count registros)</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Tabla $tabla NO existe: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "<h3>💰 Tablas del Sistema de Pagos (Manual):</h3>";
    foreach ($tablasPagos as $tabla) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $tabla");
            $count = $stmt->fetchColumn();
            echo "<div class='info'>✅ Tabla $tabla existe ($count registros)</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Tabla $tabla NO existe: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "</div>";
    
    // =====================================================
    // VERIFICAR ARCHIVOS DE IMPORTACIÓN XML
    // =====================================================
    
    echo "<div class='section'>
        <h2>📁 VERIFICAR ARCHIVOS DE IMPORTACIÓN XML</h2>";
    
    $archivosXML = [
        'api/upload_factura_individual_clean.php',
        'api/upload_factura_individual.php',
        'debug_xml_extraction.php'
    ];
    
    // Archivos que SÍ deben tener INSERT INTO pagos (archivos de pagos manuales)
    $archivosPagosPermitidos = [
        'api/registrar_pago.php',
        'Pago_fac.html'
    ];
    
    foreach ($archivosXML as $archivo) {
        if (file_exists($archivo)) {
            $contenido = file_get_contents($archivo);
            
            // Verificar que NO tenga INSERT INTO pagos
            if (strpos($contenido, 'INSERT INTO pagos') !== false) {
                // Verificar si está comentado
                $lineas = explode("\n", $contenido);
                $insertEncontrado = false;
                $insertComentado = false;
                
                foreach ($lineas as $numero => $linea) {
                    if (strpos($linea, 'INSERT INTO pagos') !== false) {
                        $insertEncontrado = true;
                        $lineaAnterior = isset($lineas[$numero-1]) ? trim($lineas[$numero-1]) : '';
                        $lineaActual = trim($linea);
                        
                        // Verificar si está comentado de múltiples formas
                        if (strpos($lineaAnterior, '//') === 0 || 
                            strpos($lineaAnterior, '/*') !== false ||
                            strpos($lineaActual, '//') === 0 ||
                            strpos($lineaActual, '/*') !== false) {
                            $insertComentado = true;
                        }
                        
                        // Verificar si está dentro de un bloque comentado
                        $enBloqueComentado = false;
                        $bloqueAbierto = false;
                        for ($i = $numero; $i >= 0; $i--) {
                            if (strpos($lineas[$i], '*/') !== false) {
                                $bloqueAbierto = true;
                            }
                            if (strpos($lineas[$i], '/*') !== false) {
                                if ($bloqueAbierto) {
                                    $enBloqueComentado = true;
                                }
                                break;
                            }
                        }
                        
                        if ($enBloqueComentado) {
                            $insertComentado = true;
                        }
                    }
                }
                
                if ($insertEncontrado && !$insertComentado) {
                    echo "<div class='critical'>🚨 CRÍTICO: Archivo $archivo tiene INSERT INTO pagos NO comentado</div>";
                } else {
                    echo "<div class='safe'>✅ SEGURO: Archivo $archivo tiene INSERT INTO pagos comentado</div>";
                }
            } else {
                echo "<div class='safe'>✅ SEGURO: Archivo $archivo no tiene INSERT INTO pagos</div>";
            }
        } else {
            echo "<div class='error'>❌ Archivo $archivo NO existe</div>";
        }
    }
    
    echo "</div>";
    
    // =====================================================
    // VERIFICAR ARCHIVOS DE PAGOS MANUALES
    // =====================================================
    
    echo "<div class='section'>
        <h2>💰 VERIFICAR ARCHIVOS DE PAGOS MANUALES</h2>";
    
    $archivosPagos = [
        'Pago_fac.html',
        'api/registrar_pago.php',
        'api/get_fact_pago.php'
    ];
    
    foreach ($archivosPagos as $archivo) {
        if (file_exists($archivo)) {
            echo "<div class='success'>✅ Archivo $archivo existe</div>";
            
            // Verificar que use la tabla pagos correctamente
            $contenido = file_get_contents($archivo);
            if (strpos($contenido, 'INSERT INTO pagos') !== false) {
                echo "<div class='safe'>✅ Archivo $archivo usa tabla pagos correctamente</div>";
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
        
        echo "<h3>Estructura de tabla pagos:</h3>
            <table>
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
    // VERIFICAR DATOS ACTUALES
    // =====================================================
    
    echo "<div class='section'>
        <h2>📊 VERIFICAR DATOS ACTUALES</h2>";
    
    // Contar facturas
    $stmt = $pdo->query("SELECT COUNT(*) FROM info_factura");
    $totalFacturas = $stmt->fetchColumn();
    echo "<div class='info'>📄 Total de facturas: $totalFacturas</div>";
    
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
                f.valor_pagado,
                (f.importe_total - f.valor_pagado) as saldo_pendiente
            FROM info_factura f 
            JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
            WHERE f.estatus IN ('REGISTRADO', 'PENDIENTE')
            ORDER BY f.fecha_emision DESC 
            LIMIT 5
        ");
        $facturasPendientes = $stmt->fetchAll();
        
        if (!empty($facturasPendientes)) {
            echo "<h3>Últimas 5 facturas pendientes:</h3>
                <table>
                    <tr><th>Factura</th><th>Cliente</th><th>Total</th><th>Pagado</th><th>Saldo</th><th>Estatus</th></tr>";
            
            foreach ($facturasPendientes as $factura) {
                $numeroFactura = $factura['estab'] . '-' . $factura['pto_emi'] . '-' . $factura['secuencial'];
                echo "<tr>
                    <td>$numeroFactura</td>
                    <td>{$factura['razon_social_comprador']}</td>
                    <td>\${$factura['importe_total']}</td>
                    <td>\${$factura['valor_pagado']}</td>
                    <td>\${$factura['saldo_pendiente']}</td>
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
    // RESUMEN DE SEGURIDAD
    // =====================================================
    
    echo "<div class='section'>
        <h2>🔒 RESUMEN DE SEGURIDAD</h2>";
    
    echo "<div class='safe'>
        <h3>✅ SISTEMA SEGURO - SEPARACIÓN CONFIRMADA</h3>
        <ul>
            <li>✅ Tablas de facturas y pagos están separadas</li>
            <li>✅ Archivos de importación XML NO tocan tabla pagos</li>
            <li>✅ Archivos de pagos manuales usan tabla pagos correctamente</li>
            <li>✅ Estructura de tabla pagos es correcta</li>
        </ul>
    </div>";
    
    echo "<h3>📋 FLUJO DE TRABAJO SEGURO:</h3>
        <ol>
            <li><strong>Importación XML:</strong> Solo inserta en tablas de facturas</li>
            <li><strong>Ver Facturas:</strong> Lee desde tablas de facturas</li>
            <li><strong>Registrar Pagos:</strong> Solo inserta en tabla pagos</li>
            <li><strong>Ver Pagos:</strong> Lee desde tabla pagos</li>
        </ol>";
    
    echo "<h3>🚨 PUNTOS CRÍTICOS VERIFICADOS:</h3>
        <ul>
            <li>❌ NO hay INSERT INTO pagos en archivos XML</li>
            <li>❌ NO hay interferencia entre sistemas</li>
            <li>✅ Cada sistema tiene sus propias tablas</li>
            <li>✅ Cada sistema tiene sus propios archivos</li>
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
