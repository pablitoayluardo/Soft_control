-- =====================================================
-- SCRIPT SIMPLIFICADO PARA CREAR TABLA DE DETALLES
-- =====================================================
-- Este script evita el acceso a information_schema que causa errores de permisos

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

-- Verificar que la tabla se creó correctamente
DESCRIBE factura_detalles;

-- Verificar los índices
SHOW INDEX FROM factura_detalles;

-- Verificar que la tabla facturas existe y tiene registros
SELECT COUNT(*) as total_facturas FROM facturas;

-- Mostrar las últimas 5 facturas registradas
SELECT 
    id,
    numero_autorizacion,
    cliente,
    total,
    fecha_registro
FROM facturas 
ORDER BY fecha_registro DESC 
LIMIT 5;

-- =====================================================
-- INSTRUCCIONES:
-- =====================================================
-- 1. Abre phpMyAdmin
-- 2. Selecciona la base de datos 'globocit_soft_control'
-- 3. Ve a la pestaña 'SQL'
-- 4. Copia y pega este contenido
-- 5. Haz clic en 'Continuar'
-- ===================================================== 