<?php
// Script para arreglar el problema del JOIN usando datos reales del XML
require_once 'config.php';

try {
    // Usar las constantes definidas en config.php
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Arreglando problema del JOIN con datos reales del XML</h2>";
    
    // 1. Verificar estructura de las tablas
    echo "<h3>1. Verificando estructura de tablas...</h3>";
    
    // Verificar estructura de todas las tablas
    $tables = ['info_tributaria', 'info_factura', 'detalle_factura_sri'];
    $table_columns = [];
    
    foreach ($tables as $table) {
        $sql = "DESCRIBE $table";
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $table_columns[$table] = array_column($columns, 'Field');
        
        echo "<p>Columnas en $table: " . implode(', ', $table_columns[$table]) . "</p>";
    }
    
    // Verificar si existe info_adicional_factura
    $sql = "SHOW TABLES LIKE 'info_adicional_factura'";
    $stmt = $pdo->query($sql);
    $info_adicional_exists = $stmt->fetch();
    
    if ($info_adicional_exists) {
        $sql = "DESCRIBE info_adicional_factura";
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $table_columns['info_adicional_factura'] = array_column($columns, 'Field');
        echo "<p>Columnas en info_adicional_factura: " . implode(', ', $table_columns['info_adicional_factura']) . "</p>";
    } else {
        echo "<p>⚠️ Tabla info_adicional_factura no existe</p>";
    }
    
    // 2. Limpiar tablas existentes
    echo "<h3>2. Limpiando tablas...</h3>";
    $pdo->exec("DELETE FROM detalle_factura_sri");
    if ($info_adicional_exists) {
        $pdo->exec("DELETE FROM info_adicional_factura");
    }
    $pdo->exec("DELETE FROM info_factura");
    $pdo->exec("DELETE FROM info_tributaria");
    echo "<p>✅ Tablas limpiadas</p>";
    
    // 3. Leer y parsear el XML real
    echo "<h3>3. Leyendo XML real...</h3>";
    $xmlFile = 'xml/fact_gc/3007202501172164244300120021000000018661440544518.xml';
    
    if (!file_exists($xmlFile)) {
        throw new Exception("El archivo XML no existe: $xmlFile");
    }
    
    $xmlContent = file_get_contents($xmlFile);
    $xml = simplexml_load_string($xmlContent);
    
    if (!$xml) {
        throw new Exception('Error al parsear el XML');
    }
    
    echo "<p>✅ XML leído correctamente</p>";
    
    // 4. Extraer datos del XML para info_tributaria
    echo "<h3>4. Creando datos en info_tributaria desde XML...</h3>";
    
    $infoTributaria = $xml->infoTributaria;
    
    // Construir la consulta dinámicamente para info_tributaria
    $tributaria_columns = ['ambiente', 'tipo_emision', 'razon_social', 'nombre_comercial', 'ruc', 'clave_acceso', 'cod_doc', 'estab', 'pto_emi', 'secuencial'];
    $tributaria_values = [
        (string)$infoTributaria->ambiente,
        (string)$infoTributaria->tipoEmision,
        (string)$infoTributaria->razonSocial,
        (string)$infoTributaria->nombreComercial,
        (string)$infoTributaria->ruc,
        (string)$infoTributaria->claveAcceso,
        (string)$infoTributaria->codDoc,
        (string)$infoTributaria->estab,
        (string)$infoTributaria->ptoEmi,
        (string)$infoTributaria->secuencial
    ];
    
    // Agregar columnas opcionales si existen
    if (in_array('dir_matriz', $table_columns['info_tributaria'])) {
        $tributaria_columns[] = 'dir_matriz';
        $tributaria_values[] = (string)$infoTributaria->dirMatriz;
    }
    
    if (in_array('fecha_autorizacion', $table_columns['info_tributaria'])) {
        $tributaria_columns[] = 'fecha_autorizacion';
        $tributaria_values[] = '2025-07-30';
    }
    
    // Construir la consulta INSERT
    $columns_str = implode(', ', $tributaria_columns);
    $placeholders = str_repeat('?,', count($tributaria_columns) - 1) . '?';
    
    $sql = "INSERT INTO info_tributaria ($columns_str) VALUES ($placeholders)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($tributaria_values);
    $tributaria_id = $pdo->lastInsertId();
    
    echo "<p>✅ Datos creados en info_tributaria (ID: $tributaria_id)</p>";
    
    // 5. Extraer datos del XML para info_factura
    echo "<h3>5. Creando datos en info_factura desde XML...</h3>";
    
    $infoFactura = $xml->infoFactura;
    
    // Construir la consulta dinámicamente para info_factura
    $factura_columns = ['id_info_tributaria', 'razon_social_comprador', 'identificacion_comprador', 'direccion_comprador', 'importe_total', 'estatus', 'observacion'];
    $factura_values = [
        $tributaria_id,
        (string)$infoFactura->razonSocialComprador,
        (string)$infoFactura->identificacionComprador,
        (string)$infoFactura->direccionComprador,
        (float)$infoFactura->importeTotal,
        'PAGADA',
        'Factura real desde XML'
    ];
    
    // Agregar columnas opcionales si existen
    if (in_array('fecha_emision', $table_columns['info_factura'])) {
        $factura_columns[] = 'fecha_emision';
        $fecha_emision = DateTime::createFromFormat('d/m/Y', (string)$infoFactura->fechaEmision);
        $factura_values[] = $fecha_emision ? $fecha_emision->format('Y-m-d') : '2025-07-30';
    }
    
    if (in_array('dir_establecimiento', $table_columns['info_factura'])) {
        $factura_columns[] = 'dir_establecimiento';
        $factura_values[] = (string)$infoFactura->dirEstablecimiento;
    }
    
    if (in_array('obligado_contabilidad', $table_columns['info_factura'])) {
        $factura_columns[] = 'obligado_contabilidad';
        $factura_values[] = (string)$infoFactura->obligadoContabilidad;
    }
    
    if (in_array('tipo_identificacion_comprador', $table_columns['info_factura'])) {
        $factura_columns[] = 'tipo_identificacion_comprador';
        $factura_values[] = (string)$infoFactura->tipoIdentificacionComprador;
    }
    
    if (in_array('total_sin_impuestos', $table_columns['info_factura'])) {
        $factura_columns[] = 'total_sin_impuestos';
        $factura_values[] = (float)$infoFactura->totalSinImpuestos;
    }
    
    if (in_array('total_descuento', $table_columns['info_factura'])) {
        $factura_columns[] = 'total_descuento';
        $factura_values[] = (float)$infoFactura->totalDescuento;
    }
    
    if (in_array('moneda', $table_columns['info_factura'])) {
        $factura_columns[] = 'moneda';
        $factura_values[] = (string)$infoFactura->moneda;
    }
    
    if (in_array('forma_pago', $table_columns['info_factura'])) {
        $factura_columns[] = 'forma_pago';
        $factura_values[] = (string)$infoFactura->pagos->pago->formaPago;
    }
    
    if (in_array('retencion', $table_columns['info_factura'])) {
        $factura_columns[] = 'retencion';
        $factura_values[] = 0.00;
    }
    
    if (in_array('valor_pagado', $table_columns['info_factura'])) {
        $factura_columns[] = 'valor_pagado';
        $factura_values[] = (float)$infoFactura->importeTotal;
    }
    
    // Construir la consulta INSERT para info_factura
    $factura_columns_str = implode(', ', $factura_columns);
    $factura_placeholders = str_repeat('?,', count($factura_columns) - 1) . '?';
    
    $sql = "INSERT INTO info_factura ($factura_columns_str) VALUES ($factura_placeholders)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($factura_values);
    $factura_id = $pdo->lastInsertId();
    
    echo "<p>✅ Datos creados en info_factura (ID: $factura_id)</p>";
    
    // 6. Crear datos en info_adicional_factura si existe la tabla
    if ($info_adicional_exists) {
        echo "<h3>6. Creando datos en info_adicional_factura desde XML...</h3>";
        
        if (isset($xml->infoAdicional) && isset($xml->infoAdicional->campoAdicional)) {
            foreach ($xml->infoAdicional->campoAdicional as $campo) {
                $nombre = (string)$campo['nombre'];
                $valor = (string)$campo['valor'];
                
                // Construir la consulta dinámicamente para info_adicional_factura
                // Verificar el nombre correcto de la columna
                $factura_id_column = 'id_info_factura';
                if (!in_array('id_info_factura', $table_columns['info_adicional_factura'])) {
                    // Intentar con otros nombres posibles
                    if (in_array('info_factura_id', $table_columns['info_adicional_factura'])) {
                        $factura_id_column = 'info_factura_id';
                    } elseif (in_array('factura_id', $table_columns['info_adicional_factura'])) {
                        $factura_id_column = 'factura_id';
                    } else {
                        echo "<p style='color: orange;'>⚠️ No se encontró columna de referencia a factura en info_adicional_factura</p>";
                        continue;
                    }
                }
                
                $adicional_columns = [$factura_id_column, 'nombre', 'valor'];
                $adicional_values = [$factura_id, $nombre, $valor];
                
                // Agregar columnas opcionales si existen
                if (in_array('created_at', $table_columns['info_adicional_factura'])) {
                    $adicional_columns[] = 'created_at';
                    $adicional_values[] = date('Y-m-d H:i:s');
                }
                
                $adicional_columns_str = implode(', ', $adicional_columns);
                $adicional_placeholders = str_repeat('?,', count($adicional_columns) - 1) . '?';
                
                $sql = "INSERT INTO info_adicional_factura ($adicional_columns_str) VALUES ($adicional_placeholders)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($adicional_values);
            }
            
            echo "<p>✅ Datos creados en info_adicional_factura</p>";
        }
    }
    
    // 7. Crear datos en detalle_factura_sri
    echo "<h3>7. Creando datos en detalle_factura_sri desde XML...</h3>";
    
    if (isset($xml->detalles) && isset($xml->detalles->detalle)) {
        $detalle_count = 0;
        
        foreach ($xml->detalles->detalle as $detalle) {
            // Construir la consulta dinámicamente para detalle_factura_sri
            // Verificar el nombre correcto de la columna
            $factura_id_column = 'id_info_factura';
            if (!in_array('id_info_factura', $table_columns['detalle_factura_sri'])) {
                // Intentar con otros nombres posibles
                if (in_array('info_factura_id', $table_columns['detalle_factura_sri'])) {
                    $factura_id_column = 'info_factura_id';
                } elseif (in_array('factura_id', $table_columns['detalle_factura_sri'])) {
                    $factura_id_column = 'factura_id';
                } else {
                    echo "<p style='color: orange;'>⚠️ No se encontró columna de referencia a factura en detalle_factura_sri</p>";
                    continue;
                }
            }
            
            $detalle_columns = [$factura_id_column, 'codigo_principal', 'descripcion', 'cantidad', 'precio_unitario', 'precio_total_sin_impuesto'];
            $detalle_values = [
                $factura_id,
                (string)$detalle->codigoPrincipal,
                (string)$detalle->descripcion,
                (float)$detalle->cantidad,
                (float)$detalle->precioUnitario,
                (float)$detalle->precioTotalSinImpuesto
            ];
            
            // Agregar columnas opcionales si existen
            if (in_array('descuento', $table_columns['detalle_factura_sri'])) {
                $detalle_columns[] = 'descuento';
                $detalle_values[] = (float)$detalle->descuento;
            }
            
            if (in_array('codigo_impuesto', $table_columns['detalle_factura_sri'])) {
                $detalle_columns[] = 'codigo_impuesto';
                $detalle_values[] = (string)$detalle->impuestos->impuesto->codigo;
            }
            
            if (in_array('codigo_porcentaje', $table_columns['detalle_factura_sri'])) {
                $detalle_columns[] = 'codigo_porcentaje';
                $detalle_values[] = (string)$detalle->impuestos->impuesto->codigoPorcentaje;
            }
            
            if (in_array('tarifa', $table_columns['detalle_factura_sri'])) {
                $detalle_columns[] = 'tarifa';
                $detalle_values[] = (float)$detalle->impuestos->impuesto->tarifa;
            }
            
            if (in_array('base_imponible', $table_columns['detalle_factura_sri'])) {
                $detalle_columns[] = 'base_imponible';
                $detalle_values[] = (float)$detalle->impuestos->impuesto->baseImponible;
            }
            
            if (in_array('valor_impuesto', $table_columns['detalle_factura_sri'])) {
                $detalle_columns[] = 'valor_impuesto';
                $detalle_values[] = (float)$detalle->impuestos->impuesto->valor;
            }
            
            if (in_array('informacion_adicional', $table_columns['detalle_factura_sri'])) {
                $detalle_columns[] = 'informacion_adicional';
                $info_adicional = '';
                if (isset($detalle->detallesAdicionales->detAdicional)) {
                    $info_adicional = (string)$detalle->detallesAdicionales->detAdicional['valor'];
                }
                $detalle_values[] = $info_adicional;
            }
            
            $detalle_columns_str = implode(', ', $detalle_columns);
            $detalle_placeholders = str_repeat('?,', count($detalle_columns) - 1) . '?';
            
            $sql = "INSERT INTO detalle_factura_sri ($detalle_columns_str) VALUES ($detalle_placeholders)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($detalle_values);
            $detalle_count++;
        }
        
        echo "<p>✅ Datos creados en detalle_factura_sri ($detalle_count registros)</p>";
    }
    
    // 8. Verificar el JOIN
    echo "<h3>8. Verificando JOIN...</h3>";
    
    // Determinar los nombres correctos de las columnas para el JOIN
    $detalle_factura_column = 'id_info_factura';
    if (!in_array('id_info_factura', $table_columns['detalle_factura_sri'])) {
        if (in_array('info_factura_id', $table_columns['detalle_factura_sri'])) {
            $detalle_factura_column = 'info_factura_id';
        } elseif (in_array('factura_id', $table_columns['detalle_factura_sri'])) {
            $detalle_factura_column = 'factura_id';
        }
    }
    
    // Determinar el nombre correcto de la columna ID en detalle_factura_sri
    $detalle_id_column = 'id';
    if (!in_array('id', $table_columns['detalle_factura_sri'])) {
        if (in_array('id_detalle_factura_sri', $table_columns['detalle_factura_sri'])) {
            $detalle_id_column = 'id_detalle_factura_sri';
        } elseif (in_array('detalle_id', $table_columns['detalle_factura_sri'])) {
            $detalle_id_column = 'detalle_id';
        } else {
            // Si no hay columna ID, usar COUNT(*)
            $detalle_id_column = '*';
        }
    }
    
    if ($detalle_id_column == '*') {
        $sql = "SELECT 
            it.estab,
            it.pto_emi,
            it.secuencial,
            f.razon_social_comprador as cliente,
            f.importe_total as total,
            f.estatus,
            COUNT(*) as total_detalles
        FROM info_factura f 
        JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
        LEFT JOIN detalle_factura_sri d ON f.id_info_factura = d.$detalle_factura_column
        GROUP BY f.id_info_factura
        ORDER BY f.created_at DESC";
    } else {
        $sql = "SELECT 
            it.estab,
            it.pto_emi,
            it.secuencial,
            f.razon_social_comprador as cliente,
            f.importe_total as total,
            f.estatus,
            COUNT(d.$detalle_id_column) as total_detalles
        FROM info_factura f 
        JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
        LEFT JOIN detalle_factura_sri d ON f.id_info_factura = d.$detalle_factura_column
        GROUP BY f.id_info_factura
        ORDER BY f.created_at DESC";
    }
    
    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll();
    
    if (count($resultados) > 0) {
        echo "<p style='color: green;'>✅ JOIN funciona correctamente - " . count($resultados) . " registros encontrados</p>";
        
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estab</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Pto Emi</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Secuencial</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Cliente</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Total</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Estatus</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Detalles</th>";
        echo "</tr>";
        
        foreach ($resultados as $row) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estab'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['pto_emi'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['secuencial'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['cliente'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['total'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['estatus'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['total_detalles'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h4>📋 Detalles del XML procesado:</h4>";
        echo "<ul>";
        echo "<li><strong>Razón Social:</strong> " . (string)$infoTributaria->razonSocial . "</li>";
        echo "<li><strong>RUC:</strong> " . (string)$infoTributaria->ruc . "</li>";
        echo "<li><strong>Clave de Acceso:</strong> " . (string)$infoTributaria->claveAcceso . "</li>";
        echo "<li><strong>Establecimiento:</strong> " . (string)$infoTributaria->estab . "</li>";
        echo "<li><strong>Punto de Emisión:</strong> " . (string)$infoTributaria->ptoEmi . "</li>";
        echo "<li><strong>Secuencial:</strong> " . (string)$infoTributaria->secuencial . "</li>";
        echo "<li><strong>Cliente:</strong> " . (string)$infoFactura->razonSocialComprador . "</li>";
        echo "<li><strong>Total:</strong> $" . (string)$infoFactura->importeTotal . "</li>";
        echo "<li><strong>Total Detalles:</strong> " . count($xml->detalles->detalle) . " productos</li>";
        if ($info_adicional_exists && isset($xml->infoAdicional)) {
            echo "<li><strong>Campos Adicionales:</strong> " . count($xml->infoAdicional->campoAdicional) . " campos</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>❌ JOIN aún no funciona</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='facturacion.html'>📊 Ir a Facturación</a></p>";
    echo "<p><a href='test_connection_simple.php'>🔍 Verificar Estado</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 