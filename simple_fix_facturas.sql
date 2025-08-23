-- =====================================================
-- SCRIPT SIMPLE PARA ARREGLAR TABLA FACTURAS
-- =====================================================

-- Verificar estructura actual
DESCRIBE facturas;

-- Agregar columnas básicas sin especificar posición
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS numero_autorizacion VARCHAR(50);
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS fecha_autorizacion DATE;
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS cliente VARCHAR(200);
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS ruc VARCHAR(20);
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS direccion TEXT;
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS telefono VARCHAR(20);
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS email VARCHAR(100);
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) DEFAULT 0;
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS iva DECIMAL(10,2) DEFAULT 0;
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS total DECIMAL(10,2) DEFAULT 0;
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS moneda VARCHAR(10) DEFAULT 'USD';
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS ambiente VARCHAR(20);
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS tipo_emision VARCHAR(20);
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS secuencial VARCHAR(20);
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS contenido_xml LONGTEXT;

-- Verificar estructura final
DESCRIBE facturas;

-- Crear tabla de detalles
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

-- Verificar tabla de detalles
DESCRIBE factura_detalles;

-- Contar facturas
SELECT COUNT(*) as total_facturas FROM facturas; 