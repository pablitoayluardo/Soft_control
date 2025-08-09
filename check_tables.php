<?php
// =====================================================
// VERIFICACIÓN Y CREACIÓN DE TABLAS
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔍 Verificación de Tablas</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
    
    // Obtener tablas existentes
    $stmt = $pdo->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📋 Tablas existentes (" . count($existingTables) . "):</h3>";
    echo "<ul>";
    foreach ($existingTables as $table) {
        echo "<li>✅ $table</li>";
    }
    echo "</ul>";
    
    // Definir tablas requeridas
    $requiredTables = [
        'usuarios' => 'Tabla de usuarios del sistema',
        'productos' => 'Tabla de productos/inventario',
        'clientes' => 'Tabla de clientes',
        'facturas' => 'Tabla de facturas',
        'detalle_facturas' => 'Tabla de detalles de facturas',
        'pagos' => 'Tabla de pagos',
        'gastos' => 'Tabla de gastos',
        'movimientos_inventario' => 'Tabla de movimientos de inventario',
        'configuraciones' => 'Tabla de configuraciones del sistema',
        'actividad_log' => 'Tabla de logs de actividad'
    ];
    
    echo "<h3>🔍 Verificando tablas requeridas:</h3>";
    
    $missingTables = [];
    foreach ($requiredTables as $table => $description) {
        if (in_array($table, $existingTables)) {
            echo "<p style='color: green;'>✅ $table - $description</p>";
        } else {
            echo "<p style='color: red;'>❌ $table - $description (FALTA)</p>";
            $missingTables[] = $table;
        }
    }
    
    // Crear tablas faltantes
    if (!empty($missingTables)) {
        echo "<h3>🔧 Creando tablas faltantes:</h3>";
        
        foreach ($missingTables as $table) {
            echo "<p>Creando tabla: <strong>$table</strong></p>";
            
            switch ($table) {
                case 'productos':
                    $sql = "CREATE TABLE productos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        codigo VARCHAR(50) UNIQUE NOT NULL,
                        nombre VARCHAR(200) NOT NULL,
                        descripcion TEXT,
                        precio_compra DECIMAL(10,2) DEFAULT 0.00,
                        precio_venta DECIMAL(10,2) DEFAULT 0.00,
                        stock_actual INT DEFAULT 0,
                        stock_minimo INT DEFAULT 5,
                        categoria VARCHAR(100),
                        proveedor VARCHAR(100),
                        activo BOOLEAN DEFAULT TRUE,
                        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )";
                    break;
                    
                case 'clientes':
                    $sql = "CREATE TABLE clientes (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        cedula VARCHAR(20) UNIQUE,
                        nombre VARCHAR(200) NOT NULL,
                        email VARCHAR(100),
                        telefono VARCHAR(20),
                        direccion TEXT,
                        activo BOOLEAN DEFAULT TRUE,
                        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )";
                    break;
                    
                case 'facturas':
                    $sql = "CREATE TABLE facturas (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        numero_factura VARCHAR(50) UNIQUE NOT NULL,
                        cliente_id INT,
                        fecha_emision DATE NOT NULL,
                        fecha_vencimiento DATE,
                        subtotal DECIMAL(10,2) DEFAULT 0.00,
                        iva DECIMAL(10,2) DEFAULT 0.00,
                        total DECIMAL(10,2) DEFAULT 0.00,
                        estado ENUM('pendiente', 'pagada', 'anulada') DEFAULT 'pendiente',
                        observaciones TEXT,
                        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (cliente_id) REFERENCES clientes(id)
                    )";
                    break;
                    
                case 'detalle_facturas':
                    $sql = "CREATE TABLE detalle_facturas (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        factura_id INT NOT NULL,
                        producto_id INT NOT NULL,
                        cantidad INT NOT NULL,
                        precio_unitario DECIMAL(10,2) NOT NULL,
                        subtotal DECIMAL(10,2) NOT NULL,
                        FOREIGN KEY (factura_id) REFERENCES facturas(id),
                        FOREIGN KEY (producto_id) REFERENCES productos(id)
                    )";
                    break;
                    
                case 'pagos':
                    $sql = "CREATE TABLE pagos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        factura_id INT,
                        monto DECIMAL(10,2) NOT NULL,
                        metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque') NOT NULL,
                        fecha_pago DATE NOT NULL,
                        referencia VARCHAR(100),
                        observaciones TEXT,
                        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (factura_id) REFERENCES facturas(id)
                    )";
                    break;
                    
                case 'gastos':
                    $sql = "CREATE TABLE gastos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        concepto VARCHAR(200) NOT NULL,
                        monto DECIMAL(10,2) NOT NULL,
                        categoria VARCHAR(100),
                        fecha_gasto DATE NOT NULL,
                        metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque'),
                        referencia VARCHAR(100),
                        observaciones TEXT,
                        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )";
                    break;
                    
                case 'movimientos_inventario':
                    $sql = "CREATE TABLE movimientos_inventario (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        producto_id INT NOT NULL,
                        tipo ENUM('entrada', 'salida', 'ajuste') NOT NULL,
                        cantidad INT NOT NULL,
                        stock_anterior INT NOT NULL,
                        stock_nuevo INT NOT NULL,
                        motivo VARCHAR(200),
                        referencia VARCHAR(100),
                        fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (producto_id) REFERENCES productos(id)
                    )";
                    break;
                    
                case 'configuraciones':
                    $sql = "CREATE TABLE configuraciones (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        clave VARCHAR(100) UNIQUE NOT NULL,
                        valor TEXT,
                        descripcion VARCHAR(200),
                        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )";
                    break;
                    
                case 'actividad_log':
                    $sql = "CREATE TABLE actividad_log (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        usuario_id INT,
                        accion VARCHAR(100) NOT NULL,
                        descripcion TEXT,
                        ip_address VARCHAR(45),
                        user_agent TEXT,
                        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
                    )";
                    break;
                    
                default:
                    echo "<p style='color: red;'>❌ Tabla '$table' no definida</p>";
                    continue;
            }
            
            try {
                $pdo->exec($sql);
                echo "<p style='color: green;'>✅ Tabla '$table' creada exitosamente</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Error creando tabla '$table': " . $e->getMessage() . "</p>";
            }
        }
        
        // Insertar datos de ejemplo
        echo "<h3>📊 Insertando datos de ejemplo:</h3>";
        
        // Productos de ejemplo
        $productos = [
            ['Laptop HP Pavilion', 'LAP001', 'Laptop HP Pavilion 15.6" Intel i5', 800.00, 1200.00, 10, 'Electrónicos'],
            ['Mouse Inalámbrico', 'MOU001', 'Mouse inalámbrico Logitech', 15.00, 25.00, 50, 'Periféricos'],
            ['Teclado Mecánico', 'TEC001', 'Teclado mecánico RGB', 80.00, 120.00, 15, 'Periféricos'],
            ['Monitor 24"', 'MON001', 'Monitor LED 24" Full HD', 150.00, 220.00, 8, 'Monitores'],
            ['Impresora HP', 'IMP001', 'Impresora multifuncional HP', 200.00, 300.00, 5, 'Impresoras']
        ];
        
        foreach ($productos as $producto) {
            $sql = "INSERT INTO productos (nombre, codigo, descripcion, precio_compra, precio_venta, stock_actual, categoria) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($producto);
        }
        echo "<p style='color: green;'>✅ Productos de ejemplo insertados</p>";
        
        // Clientes de ejemplo
        $clientes = [
            ['Juan Pérez', 'juan@email.com', '0991234567', 'Av. Principal 123'],
            ['María García', 'maria@email.com', '0992345678', 'Calle Secundaria 456'],
            ['Carlos López', 'carlos@email.com', '0993456789', 'Plaza Central 789']
        ];
        
        foreach ($clientes as $cliente) {
            $sql = "INSERT INTO clientes (nombre, email, telefono, direccion) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($cliente);
        }
        echo "<p style='color: green;'>✅ Clientes de ejemplo insertados</p>";
        
        // Configuraciones de ejemplo
        $configuraciones = [
            ['empresa_nombre', 'GloboCity Soft Control', 'Nombre de la empresa'],
            ['empresa_ruc', '1234567890001', 'RUC de la empresa'],
            ['empresa_direccion', 'Guayaquil, Ecuador', 'Dirección de la empresa'],
            ['empresa_telefono', '+593 4 1234567', 'Teléfono de la empresa'],
            ['iva_porcentaje', '12', 'Porcentaje de IVA'],
            ['moneda', 'USD', 'Moneda del sistema']
        ];
        
        foreach ($configuraciones as $config) {
            $sql = "INSERT INTO configuraciones (clave, valor, descripcion) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($config);
        }
        echo "<p style='color: green;'>✅ Configuraciones de ejemplo insertadas</p>";
        
    } else {
        echo "<p style='color: green;'>✅ Todas las tablas requeridas existen</p>";
    }
    
    // Verificar datos finales
    echo "<h3>📊 Datos finales en tablas principales:</h3>";
    $dataChecks = [
        'usuarios' => 'SELECT COUNT(*) as total FROM usuarios',
        'productos' => 'SELECT COUNT(*) as total FROM productos',
        'clientes' => 'SELECT COUNT(*) as total FROM clientes',
        'configuraciones' => 'SELECT COUNT(*) as total FROM configuraciones'
    ];
    
    foreach ($dataChecks as $table => $query) {
        try {
            $stmt = $pdo->query($query);
            $count = $stmt->fetch()['total'];
            echo "<p><strong>$table:</strong> $count registros</p>";
        } catch (PDOException $e) {
            echo "<p><strong>$table:</strong> Error - " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>🎯 Sistema listo para usar:</h3>";
    echo "<p><a href='index.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Probar Login</a></p>";
    echo "<p><a href='dashboard.html' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 Acceder al Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Verificación completada - Sistema de Control GloboCity</em></p>";
?> 