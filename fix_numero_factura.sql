-- =====================================================
-- SCRIPT PARA CORREGIR numero_factura
-- =====================================================

-- Verificar estructura actual
DESCRIBE facturas;

-- Modificar la columna numero_factura para permitir NULL o agregar valor por defecto
ALTER TABLE facturas MODIFY COLUMN numero_factura VARCHAR(50) NULL;

-- O si prefieres un valor por defecto:
-- ALTER TABLE facturas MODIFY COLUMN numero_factura VARCHAR(50) DEFAULT 'N/A';

-- Verificar estructura después del cambio
DESCRIBE facturas;

-- Mostrar todas las columnas
SHOW COLUMNS FROM facturas; 