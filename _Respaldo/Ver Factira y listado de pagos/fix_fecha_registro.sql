-- =====================================================
-- SCRIPT PARA VERIFICAR Y CORREGIR fecha_registro
-- =====================================================

-- Verificar estructura actual de la tabla facturas
DESCRIBE facturas;

-- Agregar columna fecha_registro si no existe
ALTER TABLE facturas ADD COLUMN IF NOT EXISTS fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Verificar estructura después del cambio
DESCRIBE facturas;

-- Mostrar las columnas de la tabla
SHOW COLUMNS FROM facturas;

-- Verificar que la tabla tiene todas las columnas necesarias
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'globocit_soft_control' 
AND TABLE_NAME = 'facturas'
ORDER BY ORDINAL_POSITION; 