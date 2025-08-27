# 🔒 SEPARACIÓN COMPLETA DE SISTEMAS - FACTURAS Y PAGOS

## 📋 RESUMEN EJECUTIVO

Este documento confirma que los sistemas de **Facturas** y **Pagos** están **COMPLETAMENTE SEPARADOS** para evitar errores de columnas y conflictos de datos.

---

## 🏗️ ARQUITECTURA DE SEPARACIÓN

### 📄 SISTEMA DE FACTURAS (XML)
**Propósito:** Importar y gestionar facturas desde archivos XML del SRI

**Tablas:**
- `info_tributaria` - Información tributaria de la factura
- `info_factura` - Información general de la factura
- `detalle_factura_sri` - Detalles de productos/servicios
- `info_adicional_factura` - Información adicional
- `total_con_impuestos` - Impuestos totales
- `impuestos_detalle` - Impuestos por detalle

**Archivos:**
- `api/upload_factura_individual_clean.php`
- `api/upload_factura_individual.php`
- `debug_xml_extraction.php`

**Operaciones:**
- ✅ INSERT en tablas de facturas
- ✅ SELECT desde tablas de facturas
- ❌ **NO INSERT en tabla pagos**
- ❌ **NO MODIFICA tabla pagos**

---

### 💰 SISTEMA DE PAGOS (MANUAL)
**Propósito:** Registrar pagos manuales de facturas

**Tablas:**
- `pagos` - Registros de pagos manuales
- `logs_actividad` - Logs de actividades

**Archivos:**
- `Pago_fac.html` - Interfaz de usuario
- `api/registrar_pago.php` - API de registro de pagos
- `api/get_fact_pago.php` - API de consulta de facturas

**Operaciones:**
- ✅ INSERT en tabla pagos
- ✅ SELECT desde tabla pagos
- ✅ UPDATE en info_factura (valor_pagado, estatus)
- ❌ **NO INSERT en tablas de facturas XML**

---

## 🚨 PUNTOS CRÍTICOS DE SEPARACIÓN

### ❌ PROHIBIDO EN ARCHIVOS XML:
```sql
-- ESTO NO DEBE EXISTIR EN ARCHIVOS DE IMPORTACIÓN XML
INSERT INTO pagos (id_info_factura, forma_pago, monto) VALUES (?, ?, ?)
```

### ✅ PERMITIDO EN ARCHIVOS XML:
```sql
-- ESTO SÍ DEBE EXISTIR EN ARCHIVOS DE IMPORTACIÓN XML
INSERT INTO info_factura (id_info_tributaria, fecha_emision, ...) VALUES (?, ?, ...)
INSERT INTO detalle_factura_sri (id_info_factura, codigo_principal, ...) VALUES (?, ?, ...)
```

### ✅ PERMITIDO EN ARCHIVOS DE PAGOS:
```sql
-- ESTO SÍ DEBE EXISTIR EN ARCHIVOS DE PAGOS
INSERT INTO pagos (id_info_factura, forma_pago, monto, ...) VALUES (?, ?, ?, ...)
UPDATE info_factura SET valor_pagado = ?, estatus = ? WHERE id_info_factura = ?
```

---

## 🔍 VERIFICACIÓN DE SEGURIDAD

### Script de Verificación:
```bash
# Ejecutar en navegador:
http://tu-dominio.com/verificar_separacion_sistemas.php
```

### Qué Verifica:
1. ✅ Tablas separadas existen
2. ✅ Archivos XML NO tienen INSERT INTO pagos
3. ✅ Archivos de pagos usan tabla pagos correctamente
4. ✅ Estructura de tabla pagos es correcta
5. ✅ No hay interferencia entre sistemas

---

## 📊 FLUJO DE TRABAJO SEGURO

### 1. Importación de Facturas XML:
```
XML → upload_factura_individual.php → tablas_facturas
```

### 2. Visualización de Facturas:
```
tablas_facturas → get_fact_pago.php → Pago_fac.html
```

### 3. Registro de Pagos:
```
Pago_fac.html → registrar_pago.php → tabla_pagos + update_info_factura
```

### 4. Consulta de Pagos:
```
tabla_pagos → consultas → reportes
```

---

## 🛡️ MEDIDAS DE SEGURIDAD IMPLEMENTADAS

### 1. Comentado INSERT INTO pagos en archivos XML:
```php
// PASO 5: Insertar pagos (COMENTADO - La tabla pagos se usa para registros manuales)
// Los pagos del XML se manejan de forma diferente
/*
if (!empty($pagos)) {
    $stmt = $pdo->prepare("
        INSERT INTO pagos (
            id_info_factura, forma_pago, monto
        ) VALUES (?, ?, ?)
    ");
    // ... código comentado
}
*/
```

### 2. Separación de responsabilidades:
- **Archivos XML:** Solo manejan datos de facturas
- **Archivos Pagos:** Solo manejan registros de pagos

### 3. Verificación automática:
- Script que detecta INSERT INTO pagos en archivos XML
- Alerta si encuentra código no comentado

---

## 🚀 INSTRUCCIONES DE USO

### Para Importar Facturas:
1. Usar archivos de importación XML
2. Verificar que no aparezcan errores de columnas
3. Confirmar que las facturas se importen correctamente

### Para Registrar Pagos:
1. Usar Pago_fac.html
2. Seleccionar factura pendiente
3. Completar datos del pago
4. Confirmar registro exitoso

### Para Verificar Separación:
1. Ejecutar `verificar_separacion_sistemas.php`
2. Confirmar que todo esté en verde
3. Si hay alertas rojas, corregir inmediatamente

---

## 🔧 TROUBLESHOOTING

### Error: "Unknown column 'formaPago'"
**Causa:** Archivo XML intentando insertar en tabla pagos
**Solución:** Comentar INSERT INTO pagos en archivos XML

### Error: "Column not found in INSERT"
**Causa:** Discrepancia entre código y estructura de tabla
**Solución:** Verificar estructura de tabla pagos

### Error: "Factura no encontrada"
**Causa:** Problema en relación entre tablas
**Solución:** Verificar que info_factura tenga id_info_tributaria correcto

---

## 📞 SOPORTE

### Antes de Reportar un Error:
1. ✅ Ejecutar `verificar_separacion_sistemas.php`
2. ✅ Verificar que archivos XML estén comentados
3. ✅ Confirmar estructura de tabla pagos
4. ✅ Revisar logs de error

### Información Necesaria:
- Error exacto
- Archivo donde ocurre
- Resultado de verificación de separación
- Pasos para reproducir

---

## ✅ CONFIRMACIÓN DE SEGURIDAD

**FECHA:** [Fecha actual]
**VERIFICADO POR:** [Tu nombre]
**ESTADO:** ✅ SISTEMA SEGURO - SEPARACIÓN CONFIRMADA

**Puntos Verificados:**
- [x] Tablas de facturas y pagos separadas
- [x] Archivos XML NO tocan tabla pagos
- [x] Archivos de pagos usan tabla pagos correctamente
- [x] Estructura de tabla pagos es correcta
- [x] No hay interferencia entre sistemas

**Próxima Verificación:** [Fecha programada]
