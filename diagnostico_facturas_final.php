<?php
// =====================================================
// DIAGNÓSTICO FINAL - FACTURAS NO SE MUESTRAN
// =====================================================

// Configuración de base de datos
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';
$charset = 'utf8mb4';

echo "<h1>🔍 DIAGNÓSTICO FINAL - FACTURAS NO SE MUESTRAN</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
    pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f2f2f2; }
</style>";

try {
    // 1. CONEXIÓN A LA BASE DE DATOS
    echo "<div class='section'>";
    echo "<h2>1. 🔌 CONEXIÓN A LA BASE DE DATOS</h2>";
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
    echo "</div>";

    // 2. VERIFICAR EXISTENCIA DE TABLAS
    echo "<div class='section'>";
    echo "<h2>2. 📋 VERIFICAR EXISTENCIA DE TABLAS</h2>";
    
    $tables = ['info_factura', 'info_tributaria', 'detalle_factura_sri', 'info_adicional_factura'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $pdo->query($sql);
        $exists = $stmt->fetch();
        $tableStatus[$table] = $exists ? true : false;
        
        if ($exists) {
            echo "<div class='success'>✅ Tabla <strong>$table</strong> existe</div>";
        } else {
            echo "<div class='error'>❌ Tabla <strong>$table</strong> NO existe</div>";
        }
    }
    echo "</div>";

    // 3. VERIFICAR DATOS EN TABLAS
    echo "<div class='section'>";
    echo "<h2>3. 📊 VERIFICAR DATOS EN TABLAS</h2>";
    
    foreach ($tables as $table) {
        if ($tableStatus[$table]) {
            $sql = "SELECT COUNT(*) as total FROM $table";
            $stmt = $pdo->query($sql);
            $count = $stmt->fetch()['total'];
            
            echo "<div class='info'>📊 Tabla <strong>$table</strong>: $count registros</div>";
            
            if ($count > 0 && $table === 'info_factura') {
                // Mostrar algunos registros de ejemplo
                $sql = "SELECT * FROM $table LIMIT 3";
                $stmt = $pdo->query($sql);
                $records = $stmt->fetchAll();
                
                echo "<h4>Primeros 3 registros de $table:</h4>";
                echo "<table>";
                if (!empty($records)) {
                    echo "<tr>";
                    foreach (array_keys($records[0]) as $column) {
                        echo "<th>$column</th>";
                    }
                    echo "</tr>";
                    
                    foreach ($records as $record) {
                        echo "<tr>";
                        foreach ($record as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                }
                echo "</table>";
            }
        }
    }
    echo "</div>";

    // 4. VERIFICAR RELACIÓN ENTRE TABLAS
    echo "<div class='section'>";
    echo "<h2>4. 🔗 VERIFICAR RELACIÓN ENTRE TABLAS</h2>";
    
    if ($tableStatus['info_factura'] && $tableStatus['info_tributaria']) {
        $sql = "SELECT COUNT(*) as total FROM info_factura f 
                JOIN info_tributaria it ON f.info_tributaria_id = it.id";
        $stmt = $pdo->query($sql);
        $joinCount = $stmt->fetch()['total'];
        
        echo "<div class='info'>🔗 Registros con JOIN info_factura + info_tributaria: $joinCount</div>";
        
        if ($joinCount > 0) {
            // Mostrar datos del JOIN
            $sql = "SELECT 
                it.estab,
                it.pto_emi,
                it.secuencial,
                f.fecha_emision as fecha_emision,
                f.razon_social_comprador as cliente,
                f.direccion_comprador as direccion,
                f.importe_total as total,
                f.estatus,
                f.retencion,
                f.valor_pagado,
                f.observacion
            FROM info_factura f 
            JOIN info_tributaria it ON f.info_tributaria_id = it.id
            ORDER BY f.fecha_emision DESC
            LIMIT 3";
            
            $stmt = $pdo->query($sql);
            $joinData = $stmt->fetchAll();
            
            echo "<h4>Datos del JOIN (primeros 3 registros):</h4>";
            echo "<table>";
            if (!empty($joinData)) {
                echo "<tr>";
                foreach (array_keys($joinData[0]) as $column) {
                    echo "<th>$column</th>";
                }
                echo "</tr>";
                
                foreach ($joinData as $record) {
                    echo "<tr>";
                    foreach ($record as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        }
    }
    echo "</div>";

    // 5. PROBAR LA API DIRECTAMENTE
    echo "<div class='section'>";
    echo "<h2>5. 🌐 PROBAR LA API DIRECTAMENTE</h2>";
    
    // Simular la llamada a la API
    $page = 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    if ($tableStatus['info_factura'] && $tableStatus['info_tributaria']) {
        $sql = "SELECT COUNT(*) as total FROM info_factura";
        $stmt = $pdo->query($sql);
        $totalFacturas = $stmt->fetch()['total'];
        
        if ($totalFacturas == 0) {
            echo "<div class='warning'>⚠️ No hay facturas registradas en info_factura</div>";
            echo "<div class='info'>La API debería retornar: success: true, data: [], total: 0</div>";
        } else {
            $sql = "SELECT 
                it.estab,
                it.pto_emi,
                it.secuencial,
                f.fecha_emision as fecha_emision,
                f.razon_social_comprador as cliente,
                f.direccion_comprador as direccion,
                f.importe_total as total,
                f.estatus,
                f.retencion,
                f.valor_pagado,
                f.observacion
            FROM info_factura f 
            JOIN info_tributaria it ON f.info_tributaria_id = it.id
            ORDER BY f.fecha_emision DESC
            LIMIT ? OFFSET ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$limit, $offset]);
            $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div class='success'>✅ API encontró " . count($facturas) . " facturas</div>";
            
            if (!empty($facturas)) {
                echo "<h4>Datos que debería mostrar la API:</h4>";
                echo "<table>";
                echo "<tr>";
                foreach (array_keys($facturas[0]) as $column) {
                    echo "<th>$column</th>";
                }
                echo "</tr>";
                
                foreach ($facturas as $factura) {
                    echo "<tr>";
                    foreach ($factura as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    }
    echo "</div>";

    // 6. VERIFICAR ESTRUCTURA DE TABLAS
    echo "<div class='section'>";
    echo "<h2>6. 🏗️ VERIFICAR ESTRUCTURA DE TABLAS</h2>";
    
    foreach ($tables as $table) {
        if ($tableStatus[$table]) {
            $sql = "DESCRIBE $table";
            $stmt = $pdo->query($sql);
            $columns = $stmt->fetchAll();
            
            echo "<h4>Estructura de la tabla $table:</h4>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th><th>Extra</th></tr>";
            
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    echo "</div>";

    // 7. RESUMEN Y RECOMENDACIONES
    echo "<div class='section'>";
    echo "<h2>7. 📋 RESUMEN Y RECOMENDACIONES</h2>";
    
    $totalFacturas = 0;
    if ($tableStatus['info_factura']) {
        $sql = "SELECT COUNT(*) as total FROM info_factura";
        $stmt = $pdo->query($sql);
        $totalFacturas = $stmt->fetch()['total'];
    }
    
    echo "<div class='info'>";
    echo "<h3>Estado actual del sistema:</h3>";
    echo "<ul>";
    echo "<li>✅ Conexión a base de datos: <strong>FUNCIONANDO</strong></li>";
    echo "<li>✅ Tablas requeridas: <strong>" . count(array_filter($tableStatus)) . "/" . count($tables) . "</strong></li>";
    echo "<li>✅ Facturas registradas: <strong>$totalFacturas</strong></li>";
    echo "</ul>";
    echo "</div>";
    
    if ($totalFacturas == 0) {
        echo "<div class='warning'>";
        echo "<h3>⚠️ PROBLEMA IDENTIFICADO:</h3>";
        echo "<p>No hay facturas registradas en la base de datos. Esto explica por qué no se muestra nada en el frontend.</p>";
        echo "<p><strong>Solución:</strong> Sube al menos una factura XML para ver los datos en la lista.</p>";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "<h3>✅ SISTEMA FUNCIONANDO:</h3>";
        echo "<p>El sistema tiene $totalFacturas facturas registradas y debería mostrarlas correctamente.</p>";
        echo "<p>Si no se ven en el frontend, el problema puede ser:</p>";
        echo "<ul>";
        echo "<li>Error en JavaScript del frontend</li>";
        echo "<li>Problema de CORS en la API</li>";
        echo "<li>Error en la consola del navegador</li>";
        echo "</ul>";
        echo "</div>";
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ ERROR CRÍTICO</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>8. 🔧 PRÓXIMOS PASOS</h2>";
echo "<div class='info'>";
echo "<p><strong>Si no hay facturas registradas:</strong></p>";
echo "<ol>";
echo "<li>Ve a la sección 'Ver Facturas' en facturacion.html</li>";
echo "<li>Haz clic en 'Buscar Archivo XML'</li>";
echo "<li>Selecciona un archivo XML de factura</li>";
echo "<li>Confirma el registro</li>";
echo "<li>Verifica que aparezca en la lista</li>";
echo "</ol>";
echo "<p><strong>Si hay facturas pero no se muestran:</strong></p>";
echo "<ol>";
echo "<li>Abre las herramientas de desarrollador del navegador (F12)</li>";
echo "<li>Ve a la pestaña 'Console'</li>";
echo "<li>Recarga la página y busca errores</li>";
echo "<li>Ve a la pestaña 'Network' y verifica las llamadas a la API</li>";
echo "</ol>";
echo "</div>";
echo "</div>";
?> 