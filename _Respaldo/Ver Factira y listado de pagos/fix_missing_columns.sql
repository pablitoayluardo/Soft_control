-- =====================================================
-- SCRIPT PARA AGREGAR COLUMNAS FALTANTES
-- =====================================================

-- Verificar estructura actual
DESCRIBE facturas;

-- Agregar todas las columnas que podrían faltar
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS numero_factura VARCHAR(50);
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
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Verificar estructura final
DESCRIBE facturas;

-- Mostrar todas las columnas
SHOW COLUMNS FROM facturas; 