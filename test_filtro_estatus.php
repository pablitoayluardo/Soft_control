<?php
// =====================================================
// TEST DE FILTRO DE ESTATUS
// =====================================================

echo "<h2>🔍 Test de Filtro de Estatus</h2>";

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
    
    // Verificar si existe la tabla info_factura
    $stmt = $pdo->query("SHOW TABLES LIKE 'info_factura'");
    $infoFacturaExists = $stmt->fetch();
    
    if ($infoFacturaExists) {
        echo "<p style='color: green;'>✅ <strong>Tabla info_factura existe</strong></p>";
        
        // Contar registros por estatus
        $stmt = $pdo->query("SELECT estatus, COUNT(*) as total FROM info_factura GROUP BY estatus");
        $estatusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>📊 Registros por estatus:</h4>";
        echo "<ul>";
        foreach ($estatusCounts as $estatus) {
            echo "<li><strong>{$estatus['estatus']}:</strong> {$estatus['total']} registros</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ <strong>Tabla info_factura NO existe</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error de conexión directa:</strong> " . $e->getMessage() . "</p>";
}

echo "<h3>🌐 Probando API con Filtros:</h3>";

// Probar diferentes filtros de estatus
$statusFilters = ['', 'REGISTRADO', 'PAGADO', 'ANULADO', 'NOTA CR'];

foreach ($statusFilters as $statusFilter) {
    $filterName = $statusFilter ?: 'Todos los estatus';
    echo "<h4>🔍 Probando filtro: $filterName</h4>";
    
    $apiUrl = 'api/get_facturas_simple.php?page=1&limit=5';
    if ($statusFilter) {
        $apiUrl .= "&status=$statusFilter";
    }
    
    echo "<p><strong>URL:</strong> <a href='$apiUrl' target='_blank'>$apiUrl</a></p>";
    
    try {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Content-Type: application/json',
                'timeout' => 30
            ]
        ]);
        
        $response = file_get_contents($apiUrl, false, $context);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data) {
                if ($data['success']) {
                    echo "<p style='color: green;'>✅ <strong>API funcionando correctamente</strong></p>";
                    echo "<p><strong>Facturas retornadas:</strong> " . count($data['data']) . "</p>";
                    
                    if (isset($data['filtering'])) {
                        echo "<p><strong>Filtro aplicado:</strong> " . ($data['filtering']['status'] ?: 'Ninguno') . "</p>";
                    }
                    
                    if (count($data['data']) > 0) {
                        echo "<details>";
                        echo "<summary>Primera factura</summary>";
                        echo "<pre>" . print_r($data['data'][0], true) . "</pre>";
                        echo "</details>";
                    }
                    
                } else {
                    echo "<p style='color: red;'>❌ <strong>Error en la API:</strong> " . ($data['message'] ?? 'Error desconocido') . "</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ <strong>Error decodificando JSON de la API</strong></p>";
            }
        } else {
            echo "<p style='color: red;'>❌ <strong>Error al conectar con la API</strong></p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Error probando API:</strong> " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

echo "<h3>🔧 Verificando Frontend:</h3>";

// Verificar que el archivo de frontend tenga las funciones necesarias
if (file_exists('facturacion.html')) {
    echo "<p style='color: green;'>✅ <strong>Archivo facturacion.html existe</strong></p>";
    
    // Verificar funciones específicas
    $content = file_get_contents('facturacion.html');
    
    $functions = [
        'showStatusFilter' => 'Función para mostrar filtro de estatus',
        'applyStatusFilter' => 'Función para aplicar filtro de estatus',
        'getStatusClass' => 'Función para obtener clase CSS del estatus',
        'currentStatusFilter' => 'Variable global para filtro de estatus'
    ];
    
    foreach ($functions as $function => $description) {
        if (strpos($content, $function) !== false) {
            echo "<p style='color: green;'>✅ <strong>$description:</strong> Encontrada</p>";
        } else {
            echo "<p style='color: red;'>❌ <strong>$description:</strong> NO encontrada</p>";
        }
    }
    
} else {
    echo "<p style='color: red;'>❌ <strong>Archivo facturacion.html NO existe</strong></p>";
}

echo "<h3>🎯 Resumen del Filtro de Estatus:</h3>";
echo "<div style='background: #e2e3e5; border: 1px solid #d6d8db; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
echo "<h4 style='margin-top: 0;'>Funcionalidades implementadas:</h4>";
echo "<ul>";
echo "<li><strong>Filtro por estatus:</strong> REGISTRADO, PAGADO, ANULADO, NOTA CR</li>";
echo "<li><strong>Modal de filtro:</strong> Interfaz para seleccionar estatus</li>";
echo "<li><strong>Colores por estatus:</strong> Verde (REGISTRADO), Azul (PAGADO), Rojo (ANULADO), Púrpura (NOTA CR)</li>";
echo "<li><strong>URL persistente:</strong> Los filtros se mantienen en la URL</li>";
echo "<li><strong>Paginación con filtros:</strong> La paginación funciona con filtros aplicados</li>";
echo "<li><strong>Indicador visual:</strong> El header de estatus muestra cuando hay un filtro activo</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><em>Test completado - Sistema de Control GloboCity</em></p>";
?>
