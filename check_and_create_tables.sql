-- =====================================================
-- SCRIPT PARA VERIFICAR Y CREAR TABLAS
-- =====================================================

-- Primero, verificar la estructura de la tabla facturas
DESCRIBE facturas;

-- Verificar qué columnas existen en facturas
SHOW COLUMNS FROM facturas;

-- Crear tabla de detalles de facturas
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

-- Verificar que la tabla de detalles se creó correctamente
DESCRIBE factura_detalles;

-- Verificar los índices de la tabla de detalles
SHOW INDEX FROM factura_detalles;

-- Contar registros en facturas (usando columnas que sabemos que existen)
SELECT COUNT(*) as total_facturas FROM facturas;

-- Mostrar las últimas 5 facturas (adaptado a la estructura real)
SELECT 
    id,
    cliente,
    total,
    fecha_registro
FROM facturas 
ORDER BY fecha_registro DESC 
LIMIT 5; 