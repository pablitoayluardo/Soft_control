<?php
// =====================================================
// TEST SIMPLE DE API PARA HOSTING
// =====================================================

echo "<h2>🔍 Test Simple de API - Hosting</h2>";

// Incluir configuración
require_once 'config.php';

echo "<h3>📋 Información de Configuración:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . (defined('DB_HOST') ? DB_HOST : 'NO_DEFINIDO') . "</li>";
echo "<li><strong>Base de datos:</strong> " . (defined('DB_NAME') ? DB_NAME : 'NO_DEFINIDO') . "</li>";
echo "<li><strong>Usuario:</strong> " . (defined('DB_USER') ? DB_USER : 'NO_DEFINIDO') . "</li>";
echo "<li><strong>Charset:</strong> " . (defined('DB_CHARSET') ? DB_CHARSET : 'NO_DEFINIDO') . "</li>";
echo "</ul>";

echo "<h3>🔌 Probando Conexión Directa:</h3>";

try {
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Configuración de base de datos incompleta');
    }
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ <strong>Conexión directa exitosa</strong></p>";
    
    // Probar consulta simple
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
    $result = $stmt->fetch();
    echo "<p><strong>Tablas en la base de datos:</strong> " . $result['total'] . "</p>";
    
    // Verificar si existe la tabla info_factura
    $stmt = $pdo->query("SHOW TABLES LIKE 'info_factura'");
    $infoFacturaExists = $stmt->fetch();
    
    if ($infoFacturaExists) {
        echo "<p style='color: green;'>✅ <strong>Tabla info_factura existe</strong></p>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_factura");
        $total = $stmt->fetch()['total'];
        echo "<p><strong>Total de facturas:</strong> $total</p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ <strong>Tabla info_factura NO existe</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error de conexión directa:</strong> " . $e->getMessage() . "</p>";
}

echo "<h3>🌐 Probando API:</h3>";

// Probar la API directamente
$apiUrl = 'api/get_facturas_simple.php?page=1&limit=5';
echo "<p><strong>URL de la API:</strong> <a href='$apiUrl' target='_blank'>$apiUrl</a></p>";

try {
    // Usar cURL para probar la API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<p style='color: red;'>❌ <strong>Error cURL:</strong> $error</p>";
    } else {
        echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
        
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data) {
                if ($data['success']) {
                    echo "<p style='color: green;'>✅ <strong>API funcionando correctamente</strong></p>";
                    echo "<p><strong>Facturas retornadas:</strong> " . count($data['data']) . "</p>";
                    
                    if (isset($data['debug'])) {
                        echo "<details>";
                        echo "<summary>Información de debug</summary>";
                        echo "<pre>" . print_r($data['debug'], true) . "</pre>";
                        echo "</details>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ <strong>Error en la API:</strong> " . ($data['message'] ?? 'Error desconocido') . "</p>";
                    if (isset($data['debug'])) {
                        echo "<details>";
                        echo "<summary>Información de debug</summary>";
                        echo "<pre>" . print_r($data['debug'], true) . "</pre>";
                        echo "</details>";
                    }
                }
            } else {
                echo "<p style='color: red;'>❌ <strong>Error decodificando JSON de la API</strong></p>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
            }
        } else {
            echo "<p style='color: red;'>❌ <strong>Error al conectar con la API</strong></p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error probando API:</strong> " . $e->getMessage() . "</p>";
}

echo "<h3>🔧 Verificando Archivos:</h3>";

// Verificar si existe el archivo de la API
if (file_exists('api/get_facturas_simple.php')) {
    echo "<p style='color: green;'>✅ <strong>Archivo api/get_facturas_simple.php existe</strong></p>";
} else {
    echo "<p style='color: red;'>❌ <strong>Archivo api/get_facturas_simple.php NO existe</strong></p>";
}

// Verificar si existe el archivo de configuración
if (file_exists('config.php')) {
    echo "<p style='color: green;'>✅ <strong>Archivo config.php existe</strong></p>";
} else {
    echo "<p style='color: red;'>❌ <strong>Archivo config.php NO existe</strong></p>";
}

echo "<h3>🔧 Soluciones Recomendadas:</h3>";
echo "<div style='background: #e2e3e5; border: 1px solid #d6d8db; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
echo "<h4 style='margin-top: 0;'>Si hay problemas de conexión:</h4>";
echo "<ol>";
echo "<li><strong>Verificar configuración:</strong> Asegúrate de que las credenciales en config.php sean correctas</li>";
echo "<li><strong>Verificar host:</strong> En algunos hostings el host puede ser diferente (ej: localhost, 127.0.0.1, o un host específico)</li>";
echo "<li><strong>Verificar base de datos:</strong> Asegúrate de que la base de datos exista y tenga las tablas necesarias</li>";
echo "<li><strong>Verificar permisos:</strong> El usuario debe tener permisos para acceder a la base de datos</li>";
echo "<li><strong>Verificar PHP:</strong> Asegúrate de que PDO esté habilitado en tu hosting</li>";
echo "<li><strong>Contactar hosting:</strong> Si los problemas persisten, contacta al soporte de tu hosting</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><em>Test completado - Sistema de Control GloboCity</em></p>";
?>
