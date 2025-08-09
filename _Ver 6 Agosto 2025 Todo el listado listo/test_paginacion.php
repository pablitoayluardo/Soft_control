<?php
// Script de prueba para verificar la paginación
echo "<h2>🧪 Test de Paginación de Facturas</h2>";

// Incluir configuración
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Contar total de facturas
    $sql = "SELECT COUNT(*) as total FROM info_factura";
    $stmt = $pdo->query($sql);
    $total = $stmt->fetch()['total'];
    
    echo "<h3>📊 Información de la Base de Datos</h3>";
    echo "<ul>";
    echo "<li><strong>Total de facturas:</strong> $total</li>";
    echo "<li><strong>Facturas por página:</strong> 20</li>";
    echo "<li><strong>Total de páginas:</strong> " . ceil($total / 20) . "</li>";
    echo "</ul>";
    
    // Probar diferentes páginas
    $pages_to_test = [1, 2, 3];
    $limit = 20;
    
    echo "<h3>🔍 Pruebas de Páginas</h3>";
    
    foreach ($pages_to_test as $page) {
        $offset = ($page - 1) * $limit;
        
        echo "<h4>Página $page (offset: $offset)</h4>";
        
        // Hacer la consulta
        $sql = "SELECT 
            it.estab,
            it.pto_emi,
            it.secuencial,
            f.created_at as fecha_creacion,
            f.razon_social_comprador as cliente
        FROM info_factura f 
        JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
        ORDER BY f.created_at DESC
        LIMIT ? OFFSET ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit, $offset]);
        $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Facturas encontradas:</strong> " . count($facturas) . "</p>";
        
        if (count($facturas) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
            echo "<tr style='background: #f0f0f0;'>";
            echo "<th>Estab</th>";
            echo "<th>Pto Emi</th>";
            echo "<th>Secuencial</th>";
            echo "<th>Fecha</th>";
            echo "<th>Cliente</th>";
            echo "</tr>";
            
            foreach ($facturas as $factura) {
                echo "<tr>";
                echo "<td>" . ($factura['estab'] ?: 'N/A') . "</td>";
                echo "<td>" . ($factura['pto_emi'] ?: 'N/A') . "</td>";
                echo "<td>" . ($factura['secuencial'] ?: 'N/A') . "</td>";
                echo "<td>" . ($factura['fecha_creacion'] ?: 'N/A') . "</td>";
                echo "<td>" . ($factura['cliente'] ?: 'N/A') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No hay facturas en esta página</p>";
        }
    }
    
    // Probar la API directamente
    echo "<h3>🌐 Prueba de la API</h3>";
    
    $api_url = "api/get_facturas_simple.php?page=1&limit=20";
    echo "<p><strong>URL de prueba:</strong> <a href='$api_url' target='_blank'>$api_url</a></p>";
    
    // Hacer la llamada a la API
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = file_get_contents($api_url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if ($data && $data['success']) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h4>✅ API funcionando correctamente</h4>";
            echo "<ul>";
            echo "<li><strong>Facturas retornadas:</strong> " . count($data['data']) . "</li>";
            echo "<li><strong>Página actual:</strong> " . $data['pagination']['page'] . "</li>";
            echo "<li><strong>Total de páginas:</strong> " . $data['pagination']['pages'] . "</li>";
            echo "<li><strong>Total de facturas:</strong> " . $data['pagination']['total'] . "</li>";
            echo "<li><strong>Mostrando:</strong> " . $data['pagination']['start'] . " a " . $data['pagination']['end'] . "</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h4>❌ Error en la API</h4>";
            echo "<p>" . ($data['message'] ?? 'Error desconocido') . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4>❌ Error al conectar con la API</h4>";
        echo "<p>No se pudo obtener respuesta de la API</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h4>❌ Error de Base de Datos</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🎯 Próximos Pasos</h3>";
echo "<ul>";
echo "<li><a href='facturacion.html' target='_blank'>📊 Ir a Facturación (NUEVA VENTANA)</a></li>";
echo "<li><a href='api/get_facturas_simple.php?page=1&limit=20' target='_blank'>🔍 Ver API Página 1</a></li>";
echo "<li><a href='api/get_facturas_simple.php?page=2&limit=20' target='_blank'>🔍 Ver API Página 2</a></li>";
echo "</ul>";
?> 