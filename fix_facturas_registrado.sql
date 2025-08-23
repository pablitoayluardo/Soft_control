-- =====================================================
-- SCRIPT PARA CORREGIR FACTURAS CON ESTATUS REGISTRADO
-- =====================================================
-- Este script corrige las facturas que tienen estatus 'REGISTRADO' 
-- pero no tienen 0 en retención y valor pagado

-- Verificar facturas que necesitan corrección
SELECT 
    f.id_info_factura,
    f.estatus,
    f.retencion,
    f.valor_pagado,
    f.razon_social_comprador,
    it.secuencial
FROM info_factura f 
JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
WHERE f.estatus = 'REGISTRADO' 
AND (f.retencion != 0.00 OR f.valor_pagado != 0.00);

-- Corregir las facturas con estatus REGISTRADO
-- Establecer retención y valor pagado en 0
UPDATE info_factura 
SET retencion = 0.00, 
    valor_pagado = 0.00 
WHERE estatus = 'REGISTRADO' 
AND (retencion != 0.00 OR valor_pagado != 0.00);

-- Verificar que la corrección fue exitosa
SELECT 
    f.id_info_factura,
    f.estatus,
    f.retencion,
    f.valor_pagado,
    f.razon_social_comprador,
    it.secuencial
FROM info_factura f 
JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
WHERE f.estatus = 'REGISTRADO'
ORDER BY f.id_info_factura DESC
LIMIT 10;

-- Resumen de la corrección
SELECT 
    'REGISTRADO' as estatus,
    COUNT(*) as total_facturas,
    SUM(CASE WHEN retencion = 0.00 AND valor_pagado = 0.00 THEN 1 ELSE 0 END) as con_ceros,
    SUM(CASE WHEN retencion != 0.00 OR valor_pagado != 0.00 THEN 1 ELSE 0 END) as sin_ceros
FROM info_factura 
WHERE estatus = 'REGISTRADO';
