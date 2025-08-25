-- =====================================================
-- SCRIPT DE CONFIGURACIÓN DE BASE DE DATOS
-- Sistema de Control - globocit_soft_control
-- =====================================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS globocit_soft_control
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE globocit_soft_control;

-- =====================================================
-- TABLA DE USUARIOS
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    rol ENUM('admin', 'moderador', 'usuario') DEFAULT 'usuario',
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    token_recuperacion VARCHAR(255) NULL,
    token_expiracion TIMESTAMP NULL
);

-- =====================================================
-- TABLA DE PRODUCTOS
-- =====================================================
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    categoria VARCHAR(100),
    marca VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA DE CLIENTES
-- =====================================================
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    direccion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA DE FACTURAS
-- =====================================================
CREATE TABLE IF NOT EXISTS facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_factura VARCHAR(50) UNIQUE NOT NULL,
    cliente_id INT,
    subtotal DECIMAL(10,2) NOT NULL,
    iva DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'pagada', 'anulada') DEFAULT 'pendiente',
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'efectivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_pago TIMESTAMP NULL,
    usuario_id INT,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- =====================================================
-- TABLA DE DETALLES DE FACTURA
-- =====================================================
CREATE TABLE IF NOT EXISTS factura_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (factura_id) REFERENCES facturas(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- =====================================================
-- TABLA DE PAGOS
-- =====================================================
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_id INT,
    clave_acceso VARCHAR(100) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque', 'deposito', 'pago_movil', 'otro') NOT NULL,
    institucion VARCHAR(100),
    documento VARCHAR(100),
    referencia VARCHAR(100),
    observacion TEXT,
    estado ENUM('pendiente', 'confirmado', 'rechazado') DEFAULT 'pendiente',
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT DEFAULT 1,
    FOREIGN KEY (factura_id) REFERENCES facturas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_clave_acceso (clave_acceso),
    INDEX idx_fecha_pago (fecha_pago),
    INDEX idx_metodo_pago (metodo_pago)
);

-- =====================================================
-- TABLA DE PAGOS DE FACTURAS (MEJORADA)
-- =====================================================
CREATE TABLE IF NOT EXISTS pagos_facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_id INT NOT NULL,
    clave_acceso VARCHAR(100) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    forma_pago ENUM('EFECTIVO', 'TRANSFERENCIA', 'CHEQUE', 'TARJETA_CREDITO', 'TARJETA_DEBITO', 'DEPOSITO', 'PAGO_MOVIL', 'OTRO') NOT NULL,
    institucion VARCHAR(100),
    documento VARCHAR(100),
    observacion TEXT,
    fecha_pago DATE NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT DEFAULT 1,
    estado ENUM('ACTIVO', 'ANULADO') DEFAULT 'ACTIVO',
    FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE CASCADE,
    INDEX idx_clave_acceso (clave_acceso),
    INDEX idx_fecha_pago (fecha_pago),
    INDEX idx_forma_pago (forma_pago)
);

-- =====================================================
-- TABLA DE GASTOS
-- =====================================================
CREATE TABLE IF NOT EXISTS gastos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(200) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(100),
    fecha_gasto DATE NOT NULL,
    comprobante VARCHAR(255),
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    usuario_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- =====================================================
-- TABLA DE MOVIMIENTOS DE INVENTARIO
-- =====================================================
CREATE TABLE IF NOT EXISTS movimientos_inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    tipo ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    motivo VARCHAR(200),
    usuario_id INT,
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- =====================================================
-- TABLA DE LOGS DE ACTIVIDAD
-- =====================================================
CREATE TABLE IF NOT EXISTS logs_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- =====================================================
-- TABLA DE CONFIGURACIONES
-- =====================================================
CREATE TABLE IF NOT EXISTS configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion VARCHAR(200),
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- INSERTAR DATOS INICIALES
-- =====================================================

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (username, password, nombre_completo, email, rol) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin@globocity.com.ec', 'admin');

-- Insertar configuraciones por defecto
INSERT INTO configuraciones (clave, valor, descripcion, tipo) VALUES
('empresa_nombre', 'GloboCity', 'Nombre de la empresa', 'string'),
('empresa_ruc', '1234567890001', 'RUC de la empresa', 'string'),
('empresa_direccion', 'Guayaquil, Ecuador', 'Dirección de la empresa', 'string'),
('empresa_telefono', '+593 4 1234567', 'Teléfono de la empresa', 'string'),
('empresa_email', 'info@globocity.com.ec', 'Email de la empresa', 'string'),
('iva_porcentaje', '12', 'Porcentaje de IVA', 'number'),
('moneda', 'USD', 'Moneda del sistema', 'string'),
('stock_minimo_global', '5', 'Stock mínimo global', 'number');

-- Insertar algunos productos de ejemplo
INSERT INTO productos (codigo, nombre, descripcion, precio, precio_venta, stock, categoria, marca) VALUES
('PROD001', 'Laptop HP Pavilion', 'Laptop HP Pavilion 15.6 pulgadas', 800.00, 950.00, 10, 'Electrónicos', 'HP'),
('PROD002', 'Mouse Inalámbrico', 'Mouse inalámbrico ergonómico', 25.00, 35.00, 50, 'Accesorios', 'Logitech'),
('PROD003', 'Teclado Mecánico', 'Teclado mecánico RGB', 120.00, 150.00, 15, 'Accesorios', 'Razer'),
('PROD004', 'Monitor 24"', 'Monitor LED 24 pulgadas Full HD', 180.00, 220.00, 8, 'Monitores', 'Samsung'),
('PROD005', 'Auriculares Gaming', 'Auriculares gaming con micrófono', 80.00, 100.00, 20, 'Audio', 'HyperX');

-- Insertar algunos clientes de ejemplo
INSERT INTO clientes (cedula, nombre, apellido, email, telefono) VALUES
('1234567890', 'Juan', 'Pérez', 'juan.perez@email.com', '0987654321'),
('0987654321', 'María', 'González', 'maria.gonzalez@email.com', '1234567890'),
('1122334455', 'Carlos', 'Rodríguez', 'carlos.rodriguez@email.com', '0998877665');

-- =====================================================
-- CREAR ÍNDICES PARA OPTIMIZAR RENDIMIENTO
-- =====================================================

-- Índices para productos
CREATE INDEX idx_productos_codigo ON productos(codigo);
CREATE INDEX idx_productos_categoria ON productos(categoria);
CREATE INDEX idx_productos_activo ON productos(activo);

-- Índices para facturas
CREATE INDEX idx_facturas_numero ON facturas(numero_factura);
CREATE INDEX idx_facturas_cliente ON facturas(cliente_id);
CREATE INDEX idx_facturas_fecha ON facturas(fecha_creacion);
CREATE INDEX idx_facturas_estado ON facturas(estado);

-- Índices para pagos
CREATE INDEX idx_pagos_factura ON pagos(factura_id);
CREATE INDEX idx_pagos_fecha ON pagos(fecha_pago);
CREATE INDEX idx_pagos_estado ON pagos(estado);

-- Índices para gastos
CREATE INDEX idx_gastos_fecha ON gastos(fecha_gasto);
CREATE INDEX idx_gastos_categoria ON gastos(categoria);
CREATE INDEX idx_gastos_estado ON gastos(estado);

-- Índices para movimientos de inventario
CREATE INDEX idx_movimientos_producto ON movimientos_inventario(producto_id);
CREATE INDEX idx_movimientos_fecha ON movimientos_inventario(fecha_movimiento);
CREATE INDEX idx_movimientos_tipo ON movimientos_inventario(tipo);

-- Índices para logs
CREATE INDEX idx_logs_usuario ON logs_actividad(usuario_id);
CREATE INDEX idx_logs_fecha ON logs_actividad(fecha_creacion);
CREATE INDEX idx_logs_accion ON logs_actividad(accion);

-- =====================================================
-- CREAR VISTAS ÚTILES
-- =====================================================

-- Vista para productos con stock bajo
CREATE VIEW v_productos_stock_bajo AS
SELECT id, codigo, nombre, stock, stock_minimo, categoria
FROM productos 
WHERE stock <= stock_minimo AND activo = TRUE;

-- Vista para facturas pendientes de pago
CREATE VIEW v_facturas_pendientes AS
SELECT f.id, f.numero_factura, f.total, f.fecha_creacion,
       CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
       c.telefono
FROM facturas f
LEFT JOIN clientes c ON f.cliente_id = c.id
WHERE f.estado = 'pendiente';

-- Vista para resumen de ventas por mes
CREATE VIEW v_ventas_mensual AS
SELECT 
    YEAR(fecha_creacion) as año,
    MONTH(fecha_creacion) as mes,
    COUNT(*) as total_facturas,
    SUM(total) as total_ventas
FROM facturas 
WHERE estado = 'pagada'
GROUP BY YEAR(fecha_creacion), MONTH(fecha_creacion);

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================

-- Mostrar mensaje de confirmación
SELECT 'Base de datos configurada exitosamente' as mensaje; 