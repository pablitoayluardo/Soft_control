-- =====================================================
-- SCRIPT PARA ARREGLAR TABLA FACTURAS
-- =====================================================

-- Primero, verificar qué columnas existen realmente
DESCRIBE facturas;

-- Agregar columnas una por una para evitar errores
-- Empezar con las columnas básicas

-- Agregar numero_autorizacion si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS numero_autorizacion VARCHAR(50) AFTER id;

-- Agregar fecha_autorizacion si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS fecha_autorizacion DATE AFTER numero_autorizacion;

-- Agregar cliente si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS cliente VARCHAR(200) AFTER fecha_autorizacion;

-- Agregar ruc si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS ruc VARCHAR(20) AFTER cliente;

-- Agregar direccion si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS direccion TEXT AFTER ruc;

-- Agregar telefono si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) AFTER direccion;

-- Agregar email si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS email VARCHAR(100) AFTER telefono;

-- Agregar subtotal si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) DEFAULT 0 AFTER email;

-- Agregar iva si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS iva DECIMAL(10,2) DEFAULT 0 AFTER subtotal;

-- Agregar total si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS total DECIMAL(10,2) DEFAULT 0 AFTER iva;

-- Agregar moneda si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS moneda VARCHAR(10) DEFAULT 'USD' AFTER total;

-- Agregar ambiente si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS ambiente VARCHAR(20) AFTER moneda;

-- Agregar tipo_emision si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS tipo_emision VARCHAR(20) AFTER ambiente;

-- Agregar secuencial si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS secuencial VARCHAR(20) AFTER tipo_emision;

-- Agregar contenido_xml si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS contenido_xml LONGTEXT AFTER secuencial;

-- Verificar la estructura final
DESCRIBE facturas;

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

-- Verificar los índices
SHOW INDEX FROM factura_detalles;

-- Contar registros en facturas
SELECT COUNT(*) as total_facturas FROM facturas;

-- Mostrar las últimas 5 facturas
SELECT 
    id,
    numero_autorizacion,
    cliente,
    total,
    fecha_registro
FROM facturas 
ORDER BY fecha_registro DESC 
LIMIT 5; 