<?php
// =====================================================
// VISUALIZADOR DE FACTURACIÓN SRI
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Facturación SRI - GloboCity</title>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".header { text-align: center; margin-bottom: 30px; }";
echo ".stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }";
echo ".stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; }";
echo ".stat-card h3 { margin: 0 0 10px 0; color: #333; }";
echo ".stat-card .value { font-size: 24px; font-weight: bold; color: #007bff; }";
echo ".table-container { overflow-x: auto; }";
echo "table { width: 100%; border-collapse: collapse; margin: 20px 0; }";
echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #f8f9fa; font-weight: bold; }";
echo "tr:hover { background: #f5f5f5; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".section { margin: 30px 0; }";
echo ".factura-details { background: #e9ecef; padding: 20px; border-radius: 8px; margin: 20px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1><i class='fas fa-file-invoice'></i> Sistema de Facturación SRI</h1>";
echo "<p>Visualizador de datos de facturación electrónica</p>";
echo "</div>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // =====================================================
    // ESTADÍSTICAS GENERALES
    // =====================================================
    
    echo "<div class='stats-grid'>";
    
    // Total de facturas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_factura");
    $total_facturas = $stmt->fetch()['total'];
    
    echo "<div class='stat-card'>";
    echo "<h3><i class='fas fa-file-invoice'></i> Total Facturas</h3>";
    echo "<div class='value'>$total_facturas</div>";
    echo "</div>";
    
    // Total facturado
    $stmt = $pdo->query("SELECT COALESCE(SUM(importe_total), 0) as total FROM info_factura");
    $total_facturado = $stmt->fetch()['total'];
    
    echo "<div class='stat-card'>";
    echo "<h3><i class='fas fa-dollar-sign'></i> Total Facturado</h3>";
    echo "<div class='value'>$" . number_format($total_facturado, 2) . "</div>";
    echo "</div>";
    
    // Facturas de hoy
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM info_factura WHERE DATE(fecha_emision) = CURDATE()");
    $facturas_hoy = $stmt->fetch()['total'];
    
    echo "<div class='stat-card'>";
    echo "<h3><i class='fas fa-calendar-day'></i> Facturas Hoy</h3>";
    echo "<div class='value'>$facturas_hoy</div>";
    echo "</div>";
    
    // Total de clientes
    $stmt = $pdo->query("SELECT COUNT(DISTINCT identificacion_comprador) as total FROM info_factura");
    $total_clientes = $stmt->fetch()['total'];
    
    echo "<div class='stat-card'>";
    echo "<h3><i class='fas fa-users'></i> Clientes Únicos</h3>";
    echo "<div class='value'>$total_clientes</div>";
    echo "</div>";
    
    echo "</div>";
    
    // =====================================================
    // DETALLES DE LA FACTURA CARGADA
    // =====================================================
    
    echo "<div class='section'>";
    echo "<h2><i class='fas fa-info-circle'></i> Detalles de la Factura Cargada</h2>";
    
    $sql = "SELECT 
        it.razon_social,
        it.nombre_comercial,
        it.ruc,
        it.clave_acceso,
        it.secuencial,
        inf_factura.fecha_emision,
        inf_factura.razon_social_comprador,
        inf_factura.identificacion_comprador,
        inf_factura.direccion_comprador,
        inf_factura.total_sin_impuestos,
        inf_factura.total_descuento,
        inf_factura.importe_total,
        inf_factura.moneda,
        inf_factura.forma_pago
    FROM info_tributaria it
    JOIN info_factura inf_factura ON it.id = inf_factura.info_tributaria_id
    ORDER BY inf_factura.fecha_emision DESC
    LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $factura = $stmt->fetch();
    
    if ($factura) {
        echo "<div class='factura-details'>";
        echo "<h3>Información del Emisor</h3>";
        echo "<p><strong>Razón Social:</strong> " . $factura['razon_social'] . "</p>";
        echo "<p><strong>Nombre Comercial:</strong> " . $factura['nombre_comercial'] . "</p>";
        echo "<p><strong>RUC:</strong> " . $factura['ruc'] . "</p>";
        echo "<p><strong>Clave de Acceso:</strong> " . $factura['clave_acceso'] . "</p>";
        echo "<p><strong>Secuencial:</strong> " . $factura['secuencial'] . "</p>";
        
        echo "<h3>Información del Comprador</h3>";
        echo "<p><strong>Razón Social:</strong> " . $factura['razon_social_comprador'] . "</p>";
        echo "<p><strong>Identificación:</strong> " . $factura['identificacion_comprador'] . "</p>";
        echo "<p><strong>Dirección:</strong> " . $factura['direccion_comprador'] . "</p>";
        
        echo "<h3>Información de la Factura</h3>";
        echo "<p><strong>Fecha de Emisión:</strong> " . $factura['fecha_emision'] . "</p>";
        echo "<p><strong>Subtotal:</strong> $" . number_format($factura['total_sin_impuestos'], 2) . "</p>";
        echo "<p><strong>Descuento:</strong> $" . number_format($factura['total_descuento'], 2) . "</p>";
        echo "<p><strong>Total:</strong> $" . number_format($factura['importe_total'], 2) . " " . $factura['moneda'] . "</p>";
        echo "<p><strong>Forma de Pago:</strong> " . $factura['forma_pago'] . "</p>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // =====================================================
    // DETALLES DE PRODUCTOS
    // =====================================================
    
    echo "<div class='section'>";
    echo "<h2><i class='fas fa-boxes'></i> Detalles de Productos</h2>";
    
    $sql = "SELECT 
        codigo_principal,
        descripcion,
        cantidad,
        precio_unitario,
        descuento,
        precio_total_sin_impuesto,
        codigo_impuesto,
        codigo_porcentaje,
        tarifa,
        base_imponible,
        valor_impuesto,
        informacion_adicional
    FROM detalle_factura_sri
    ORDER BY id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $detalles = $stmt->fetchAll();
    
    if ($detalles) {
        echo "<div class='table-container'>";
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Código</th>";
        echo "<th>Descripción</th>";
        echo "<th>Cantidad</th>";
        echo "<th>Precio Unit.</th>";
        echo "<th>Descuento</th>";
        echo "<th>Subtotal</th>";
        echo "<th>IVA</th>";
        echo "<th>Total</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        $total_general = 0;
        foreach ($detalles as $detalle) {
            $subtotal = $detalle['precio_total_sin_impuesto'];
            $iva = $detalle['valor_impuesto'];
            $total_linea = $subtotal + $iva;
            $total_general += $total_linea;
            
            echo "<tr>";
            echo "<td>" . $detalle['codigo_principal'] . "</td>";
            echo "<td>" . $detalle['descripcion'] . "</td>";
            echo "<td>" . $detalle['cantidad'] . "</td>";
            echo "<td>$" . number_format($detalle['precio_unitario'], 2) . "</td>";
            echo "<td>$" . number_format($detalle['descuento'], 2) . "</td>";
            echo "<td>$" . number_format($subtotal, 2) . "</td>";
            echo "<td>$" . number_format($iva, 2) . "</td>";
            echo "<td>$" . number_format($total_linea, 2) . "</td>";
            echo "</tr>";
        }
        
        echo "<tr style='font-weight: bold; background: #f8f9fa;'>";
        echo "<td colspan='7' style='text-align: right;'>Total General:</td>";
        echo "<td>$" . number_format($total_general, 2) . "</td>";
        echo "</tr>";
        
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // =====================================================
    // PRODUCTOS MÁS VENDIDOS
    // =====================================================
    
    echo "<div class='section'>";
    echo "<h2><i class='fas fa-chart-line'></i> Productos Más Vendidos</h2>";
    
    $sql = "SELECT 
        codigo_principal,
        descripcion,
        SUM(cantidad) as total_vendido,
        SUM(precio_total_sin_impuesto) as total_facturado,
        AVG(precio_unitario) as precio_promedio
    FROM detalle_factura_sri 
    GROUP BY codigo_principal, descripcion 
    ORDER BY total_vendido DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $productos_vendidos = $stmt->fetchAll();
    
    if ($productos_vendidos) {
        echo "<div class='table-container'>";
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Código</th>";
        echo "<th>Descripción</th>";
        echo "<th>Cantidad Total</th>";
        echo "<th>Total Facturado</th>";
        echo "<th>Precio Promedio</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($productos_vendidos as $producto) {
            echo "<tr>";
            echo "<td>" . $producto['codigo_principal'] . "</td>";
            echo "<td>" . $producto['descripcion'] . "</td>";
            echo "<td>" . $producto['total_vendido'] . "</td>";
            echo "<td>$" . number_format($producto['total_facturado'], 2) . "</td>";
            echo "<td>$" . number_format($producto['precio_promedio'], 2) . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // =====================================================
    // ENLACES DE NAVEGACIÓN
    // =====================================================
    
    echo "<div class='section'>";
    echo "<h2><i class='fas fa-link'></i> Enlaces de Navegación</h2>";
    echo "<a href='index.html' class='btn'><i class='fas fa-sign-in-alt'></i> Login</a>";
    echo "<a href='dashboard.html' class='btn'><i class='fas fa-tachometer-alt'></i> Dashboard</a>";
    echo "<a href='check_tables.php' class='btn'><i class='fas fa-database'></i> Verificar Tablas</a>";
    echo "<a href='create_facturacion_tables.php' class='btn'><i class='fas fa-plus'></i> Crear Tablas Facturación</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div>";
echo "</body>";
echo "</html>";
?> 