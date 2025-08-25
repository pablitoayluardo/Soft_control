<?php
// test_pdf.php - Archivo de prueba para verificar la generación de PDF

// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

echo "<h1>Test de Generación de PDF</h1>";

try {
    // 1. Probar conexión a la base de datos
    echo "<h2>1. Probando conexión a la base de datos...</h2>";
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ Conexión a la base de datos exitosa</p>";
    
    // 2. Verificar que existe la librería FPDF
    echo "<h2>2. Verificando librería FPDF...</h2>";
    
    if (file_exists('lib/fpdf/fpdf.php')) {
        echo "<p style='color: green;'>✅ Librería FPDF encontrada</p>";
    } else {
        echo "<p style='color: red;'>❌ Librería FPDF no encontrada</p>";
    }
    
    // 3. Verificar estructura de tablas
    echo "<h2>3. Verificando estructura de tablas...</h2>";
    
    // Verificar tabla info_tributaria
    $stmt = $pdo->query("DESCRIBE info_tributaria");
    $columns_tributaria = $stmt->fetchAll();
    echo "<p>Columnas en info_tributaria:</p>";
    echo "<ul>";
    foreach ($columns_tributaria as $col) {
        echo "<li>{$col['Field']} - {$col['Type']}</li>";
    }
    echo "</ul>";
    
    // Verificar tabla info_factura
    $stmt = $pdo->query("DESCRIBE info_factura");
    $columns_factura = $stmt->fetchAll();
    echo "<p>Columnas en info_factura:</p>";
    echo "<ul>";
    foreach ($columns_factura as $col) {
        echo "<li>{$col['Field']} - {$col['Type']}</li>";
    }
    echo "</ul>";
    
    // Verificar tabla detalle_factura_sri
    $stmt = $pdo->query("DESCRIBE detalle_factura_sri");
    $columns_detalle = $stmt->fetchAll();
    echo "<p>Columnas en detalle_factura_sri:</p>";
    echo "<ul>";
    foreach ($columns_detalle as $col) {
        echo "<li>{$col['Field']} - {$col['Type']}</li>";
    }
    echo "</ul>";
    
    // 4. Verificar si hay facturas en la base de datos
    echo "<h2>4. Verificando facturas en la base de datos...</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_tributaria");
    $total_facturas = $stmt->fetch()['total'];
    echo "<p>Total de facturas en info_tributaria: <strong>{$total_facturas}</strong></p>";
    
    if ($total_facturas > 0) {
        // Mostrar una factura de ejemplo
        $stmt = $pdo->query("SELECT clave_acceso, razon_social_comprador FROM info_tributaria it JOIN info_factura f ON it.id_info_tributaria = f.id_info_tributaria LIMIT 1");
        $factura_ejemplo = $stmt->fetch();
        
        if ($factura_ejemplo) {
            echo "<p>Factura de ejemplo:</p>";
            echo "<ul>";
            echo "<li><strong>Clave de acceso:</strong> {$factura_ejemplo['clave_acceso']}</li>";
            echo "<li><strong>Cliente:</strong> {$factura_ejemplo['razon_social_comprador']}</li>";
            echo "</ul>";
            
            // 5. Probar la consulta principal del PDF
            echo "<h2>5. Probando consulta principal del PDF...</h2>";
            
            $clave_acceso = $factura_ejemplo['clave_acceso'];
                         $sql = "SELECT 
                         it.*, 
                         f.*,
                         f.id_info_factura as info_factura_id
                     FROM info_tributaria it
                     JOIN info_factura f ON it.id_info_tributaria = f.id_info_tributaria
                     WHERE it.clave_acceso = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$clave_acceso]);
            $factura = $stmt->fetch();
            
            if ($factura) {
                echo "<p style='color: green;'>✅ Consulta principal exitosa</p>";
                echo "<p>Datos encontrados:</p>";
                echo "<ul>";
                echo "<li><strong>ID Factura:</strong> {$factura['info_factura_id']}</li>";
                echo "<li><strong>Cliente:</strong> {$factura['razon_social_comprador']}</li>";
                echo "<li><strong>Total:</strong> {$factura['importe_total']}</li>";
                echo "</ul>";
                
                // 6. Probar consulta de detalles
                echo "<h2>6. Probando consulta de detalles...</h2>";
                
                                 $sql_detalles = "SELECT * FROM detalle_factura_sri WHERE id_info_factura = ?";
                $stmt_detalles = $pdo->prepare($sql_detalles);
                $stmt_detalles->execute([$factura['info_factura_id']]);
                $detalles = $stmt_detalles->fetchAll();
                
                echo "<p>Total de detalles encontrados: <strong>" . count($detalles) . "</strong></p>";
                
                if (count($detalles) > 0) {
                    echo "<p style='color: green;'>✅ Consulta de detalles exitosa</p>";
                } else {
                    echo "<p style='color: orange;'>⚠️ No se encontraron detalles</p>";
                }
                
                // 7. Probar generación de PDF
                echo "<h2>7. Probando generación de PDF...</h2>";
                echo "<p><a href='api/generar_pdf.php?clave_acceso={$clave_acceso}' target='_blank'>Generar PDF de prueba</a></p>";
                
            } else {
                echo "<p style='color: red;'>❌ No se encontró la factura con la clave de acceso: {$clave_acceso}</p>";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No hay facturas en la base de datos</p>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error durante la prueba</h2>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}
?>
