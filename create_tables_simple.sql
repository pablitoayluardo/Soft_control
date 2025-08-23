-- =====================================================
-- SCRIPT SIMPLE PARA CREAR TABLAS
-- =====================================================

-- Crear tabla facturas si no existe
CREATE TABLE IF NOT EXISTS facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_autorizacion VARCHAR(50),
    fecha_autorizacion DATE,
    cliente VARCHAR(200),
    ruc VARCHAR(20),
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    subtotal DECIMAL(10,2) DEFAULT 0,
    iva DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    moneda VARCHAR(10) DEFAULT 'USD',
    ambiente VARCHAR(20),
    tipo_emision VARCHAR(20),
    secuencial VARCHAR(20),
    contenido_xml LONGTEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla factura_detalles si no existe
CREATE TABLE IF NOT EXISTS factura_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_id INT NOT NULL,
    codigo_principal VARCHAR(50) NOT NULL,
    descripcion TEXT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL DEFAULT 0,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0,
    precio_total_sin_impuesto DECIMAL(10,2) NOT NULL DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE CASCADE,
    INDEX idx_factura_id (factura_id),
    INDEX idx_codigo_principal (codigo_principal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar que las tablas se crearon
SHOW TABLES;

-- Verificar estructura de facturas
DESCRIBE facturas;

-- Verificar estructura de factura_detalles
DESCRIBE factura_detalles; 