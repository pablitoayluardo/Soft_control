<?php
// =====================================================
// SCRIPT DE PRUEBA DE APIs
// =====================================================

// Configurar headers
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Prueba de APIs</title>
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
    <h1>🔌 PRUEBA DE APIs DEL SISTEMA DE PAGOS</h1>
    <p>Fecha: " . date('Y-m-d H:i:s') . "</p>
</div>";

// =====================================================
// PRUEBA 1: API GET_FACT_PAGO.PHP
// =====================================================

echo "<div class='section'>
    <h2>📄 PRUEBA DE API get_fact_pago.php</h2>";

if (file_exists('api/get_fact_pago.php')) {
    echo "<div class='success'>✅ Archivo existe</div>";
    
    // Incluir el archivo y capturar la salida
    ob_start();
    try {
        // Simular una llamada GET
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        include 'api/get_fact_pago.php';
        $output = ob_get_contents();
    } catch (Exception $e) {
        $output = "Error: " . $e->getMessage();
    }
    ob_end_clean();
    
    echo "<div class='info'>📋 Respuesta de la API:</div>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Intentar decodificar JSON
    $data = json_decode($output, true);
    if ($data !== null) {
        echo "<div class='success'>✅ Respuesta JSON válida</div>";
        if (isset($data['success'])) {
            echo "<div class='success'>✅ Campo 'success' presente</div>";
            if ($data['success']) {
                echo "<div class='success'>✅ API funcionando correctamente</div>";
                if (isset($data['facturas'])) {
                    echo "<div class='info'>📊 Facturas encontradas: " . count($data['facturas']) . "</div>";
                }
            } else {
                echo "<div class='warning'>⚠️ API reporta error: " . ($data['message'] ?? 'Sin mensaje') . "</div>";
            }
        } else {
            echo "<div class='warning'>⚠️ Campo 'success' no encontrado en respuesta</div>";
        }
    } else {
        echo "<div class='error'>❌ Respuesta no es JSON válido</div>";
    }
    
} else {
    echo "<div class='error'>❌ Archivo no existe</div>";
}

echo "</div>";

// =====================================================
// PRUEBA 2: VERIFICAR CONFIGURACIÓN
// =====================================================

echo "<div class='section'>
    <h2>⚙️ VERIFICACIÓN DE CONFIGURACIÓN</h2>";

if (file_exists('config.php')) {
    echo "<div class='success'>✅ Archivo config.php existe</div>";
    
    // Verificar que las constantes estén definidas
    ob_start();
    include 'config.php';
    ob_end_clean();
    
    $constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'];
    $missing = [];
    
    foreach ($constants as $constant) {
        if (defined($constant)) {
            echo "<div class='success'>✅ Constante $constant definida</div>";
        } else {
            echo "<div class='error'>❌ Constante $constant NO definida</div>";
            $missing[] = $constant;
        }
    }
    
    if (empty($missing)) {
        echo "<div class='success'>✅ Todas las constantes de configuración están definidas</div>";
    } else {
        echo "<div class='error'>❌ Faltan constantes: " . implode(', ', $missing) . "</div>";
    }
    
} else {
    echo "<div class='error'>❌ Archivo config.php no existe</div>";
}

echo "</div>";

// =====================================================
// PRUEBA 3: CONEXIÓN A BASE DE DATOS
// =====================================================

echo "<div class='section'>
    <h2>🗄️ PRUEBA DE CONEXIÓN A BASE DE DATOS</h2>";

if (file_exists('config.php')) {
    try {
        include 'config.php';
        
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        echo "<div class='success'>✅ Conexión a base de datos exitosa</div>";
        
        // Verificar que las tablas existen
        $tables = ['pagos', 'info_factura', 'info_tributaria', 'logs_actividad'];
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "<div class='success'>✅ Tabla $table existe ($count registros)</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ Tabla $table NO existe o no es accesible</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ No se puede probar conexión sin config.php</div>";
}

echo "</div>";

// =====================================================
// RESUMEN FINAL
// =====================================================

echo "<div class='section'>
    <h2>📋 RESUMEN DE PRUEBAS</h2>
    <p>Si todas las pruebas anteriores muestran ✅, entonces las APIs están funcionando correctamente.</p>
    <p>Si hay errores ❌, revisa la configuración y los permisos de archivos.</p>
</div>";

echo "</body></html>";
?> 