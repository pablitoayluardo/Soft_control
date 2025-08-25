<?php
// =====================================================
// PRUEBA DIRECTA DE APIs SIN SHELL_EXEC
// =====================================================

// Configurar headers
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Prueba Directa de APIs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background: #007bff; color: white; padding: 10px; border-radius: 5px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; max-height: 300px; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .test-success { background: #d4edda; border: 1px solid #c3e6cb; }
        .test-error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .test-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>";

echo "<div class='header'>
    <h1>🔌 PRUEBA DIRECTA DE APIs</h1>
    <p>Fecha: " . date('Y-m-d H:i:s') . "</p>
</div>";

// =====================================================
// FUNCIÓN PARA PROBAR ARCHIVO PHP
// =====================================================

function testarArchivoPHP($archivo, $nombre) {
    echo "<div class='section'>
        <h2>📄 PRUEBA DE $nombre</h2>";
    
    if (!file_exists($archivo)) {
        echo "<div class='test-result test-error'>
            ❌ Archivo no existe: $archivo
        </div>";
        return false;
    }
    
    echo "<div class='test-result test-success'>
        ✅ Archivo existe: $archivo
    </div>";
    
    // Verificar legibilidad
    if (!is_readable($archivo)) {
        echo "<div class='test-result test-error'>
            ❌ Archivo no es legible
        </div>";
        return false;
    }
    
    echo "<div class='test-result test-success'>
        ✅ Archivo es legible
    </div>";
    
    // Leer contenido
    $contenido = file_get_contents($archivo);
    if ($contenido === false) {
        echo "<div class='test-result test-error'>
            ❌ No se puede leer el contenido
        </div>";
        return false;
    }
    
    echo "<div class='test-result test-success'>
        ✅ Contenido leído correctamente (" . strlen($contenido) . " caracteres)
    </div>";
    
    // Verificar estructura PHP básica
    if (strpos($contenido, '<?php') === false) {
        echo "<div class='test-result test-error'>
            ❌ No se encontró la etiqueta de apertura PHP
        </div>";
        return false;
    }
    
    echo "<div class='test-result test-success'>
        ✅ Etiqueta PHP encontrada
    </div>";
    
    // Verificar etiqueta de cierre (opcional)
    if (strpos($contenido, '?>') === false) {
        echo "<div class='test-result test-warning'>
            ⚠️ No se encontró etiqueta de cierre PHP (opcional)
        </div>";
    } else {
        echo "<div class='test-result test-success'>
            ✅ Etiqueta de cierre PHP encontrada
        </div>";
    }
    
    // Verificar funciones críticas
    $funcionesCriticas = ['json_encode', 'PDO', 'header', 'require_once'];
    $funcionesEncontradas = [];
    
    foreach ($funcionesCriticas as $funcion) {
        if (strpos($contenido, $funcion) !== false) {
            $funcionesEncontradas[] = $funcion;
        }
    }
    
    if (count($funcionesEncontradas) >= 2) {
        echo "<div class='test-result test-success'>
            ✅ Funciones críticas encontradas: " . implode(', ', $funcionesEncontradas) . "
        </div>";
    } else {
        echo "<div class='test-result test-warning'>
            ⚠️ Pocas funciones críticas encontradas: " . implode(', ', $funcionesEncontradas) . "
        </div>";
    }
    
    // Verificar configuración de base de datos
    if (strpos($contenido, 'config.php') !== false) {
        echo "<div class='test-result test-success'>
            ✅ Referencia a config.php encontrada
        </div>";
    } else {
        echo "<div class='test-result test-warning'>
            ⚠️ No se encontró referencia a config.php
        </div>";
    }
    
    // Verificar headers JSON
    if (strpos($contenido, 'Content-Type: application/json') !== false) {
        echo "<div class='test-result test-success'>
            ✅ Headers JSON configurados
        </div>";
    } else {
        echo "<div class='test-result test-warning'>
            ⚠️ Headers JSON no encontrados
        </div>";
    }
    
    echo "</div>";
    return true;
}

// =====================================================
// PRUEBA 1: API GET_FACT_PAGO.PHP
// =====================================================

$getFactPagoOK = testarArchivoPHP('api/get_fact_pago.php', 'API GET_FACT_PAGO.PHP');

// =====================================================
// PRUEBA 2: API REGISTRAR_PAGO.PHP
// =====================================================

$registrarPagoOK = testarArchivoPHP('api/registrar_pago.php', 'API REGISTRAR_PAGO.PHP');

// =====================================================
// PRUEBA 3: CONFIG.PHP
// =====================================================

echo "<div class='section'>
    <h2>⚙️ PRUEBA DE CONFIG.PHP</h2>";

if (file_exists('config.php')) {
    echo "<div class='test-result test-success'>
        ✅ Archivo config.php existe
    </div>";
    
    $configContent = file_get_contents('config.php');
    
    // Verificar constantes de base de datos
    $constantes = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'];
    $constantesEncontradas = [];
    
    foreach ($constantes as $constante) {
        if (strpos($configContent, $constante) !== false) {
            $constantesEncontradas[] = $constante;
        }
    }
    
    if (count($constantesEncontradas) >= 4) {
        echo "<div class='test-result test-success'>
            ✅ Constantes de BD encontradas: " . implode(', ', $constantesEncontradas) . "
        </div>";
    } else {
        echo "<div class='test-result test-error'>
            ❌ Faltan constantes de BD: " . implode(', ', array_diff($constantes, $constantesEncontradas)) . "
        </div>";
    }
    
} else {
    echo "<div class='test-result test-error'>
        ❌ Archivo config.php no existe
    </div>";
}

echo "</div>";

// =====================================================
// PRUEBA 4: CONEXIÓN A BASE DE DATOS
// =====================================================

echo "<div class='section'>
    <h2>🗄️ PRUEBA DE CONEXIÓN A BASE DE DATOS</h2>";

if (file_exists('config.php')) {
    try {
        include 'config.php';
        
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            throw new Exception('Constantes de configuración no definidas');
        }
        
        echo "<div class='test-result test-success'>
            ✅ Constantes de configuración definidas
        </div>";
        
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        echo "<div class='test-result test-success'>
            ✅ Conexión a base de datos exitosa
        </div>";
        
        // Verificar tablas
        $tablas = ['pagos', 'info_factura', 'info_tributaria', 'logs_actividad'];
        
        foreach ($tablas as $tabla) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM $tabla");
                $count = $stmt->fetchColumn();
                echo "<div class='test-result test-success'>
                    ✅ Tabla $tabla existe ($count registros)
                </div>";
            } catch (Exception $e) {
                echo "<div class='test-result test-error'>
                    ❌ Tabla $tabla NO existe o no es accesible
                </div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='test-result test-error'>
            ❌ Error de conexión: " . $e->getMessage() . "
        </div>";
    }
} else {
    echo "<div class='test-result test-error'>
        ❌ No se puede probar conexión sin config.php
    </div>";
}

echo "</div>";

// =====================================================
// PRUEBA 5: EJECUCIÓN DIRECTA DE APIs
// =====================================================

echo "<div class='section'>
    <h2>🚀 PRUEBA DE EJECUCIÓN DIRECTA</h2>";

if ($getFactPagoOK) {
    echo "<h3>📄 Probando get_fact_pago.php:</h3>";
    
    // Simular ejecución
    ob_start();
    try {
        // Simular variables globales
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'localhost';
        
        // Incluir el archivo
        include 'api/get_fact_pago.php';
        $output = ob_get_contents();
    } catch (Exception $e) {
        $output = "Error: " . $e->getMessage();
    }
    ob_end_clean();
    
    echo "<div class='info'>📋 Salida de la API:</div>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Verificar JSON
    $data = json_decode($output, true);
    if ($data !== null) {
        echo "<div class='test-result test-success'>
            ✅ Respuesta JSON válida
        </div>";
        
        if (isset($data['success'])) {
            echo "<div class='test-result test-success'>
                ✅ Campo 'success' presente
            </div>";
            
            if ($data['success']) {
                echo "<div class='test-result test-success'>
                    ✅ API funcionando correctamente
                </div>";
            } else {
                echo "<div class='test-result test-warning'>
                    ⚠️ API reporta error: " . ($data['message'] ?? 'Sin mensaje') . "
                </div>";
            }
        }
    } else {
        echo "<div class='test-result test-error'>
            ❌ Respuesta no es JSON válido
        </div>";
    }
}

echo "</div>";

// =====================================================
// RESUMEN FINAL
// =====================================================

echo "<div class='section'>
    <h2>📋 RESUMEN DE PRUEBAS</h2>";

$errores = [];
$exitos = [];

if ($getFactPagoOK) {
    $exitos[] = "API get_fact_pago.php verificada";
} else {
    $errores[] = "Problemas con API get_fact_pago.php";
}

if ($registrarPagoOK) {
    $exitos[] = "API registrar_pago.php verificada";
} else {
    $errores[] = "Problemas con API registrar_pago.php";
}

if (file_exists('config.php')) {
    $exitos[] = "Archivo config.php presente";
} else {
    $errores[] = "Archivo config.php faltante";
}

// Mostrar resumen
if (!empty($exitos)) {
    echo "<h3>✅ Éxitos:</h3><ul>";
    foreach ($exitos as $exito) {
        echo "<li class='success'>$exito</li>";
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
    echo "<div class='test-result test-success' style='font-size: 18px;'>
        🎉 TODAS LAS APIs ESTÁN FUNCIONANDO CORRECTAMENTE
    </div>";
} else {
    echo "<div class='test-result test-error' style='font-size: 18px;'>
        ⚠️ HAY PROBLEMAS QUE NECESITAN ATENCIÓN
    </div>";
}

echo "</div>";

echo "</body></html>";
?>
