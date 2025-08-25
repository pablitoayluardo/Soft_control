# 📋 Instrucciones de Actualización - Módulo de Pagos

## 🔧 Cambios Necesarios

### 1. **Actualizar Base de Datos**
Ejecutar el script SQL para agregar campos a la tabla `pagos`:

```sql
-- Archivo: actualizar_tabla_pagos_v2.sql
ALTER TABLE pagos 
ADD COLUMN estab VARCHAR(3) AFTER id_info_factura,
ADD COLUMN pto_emi VARCHAR(3) AFTER estab,
ADD COLUMN secuencial VARCHAR(9) AFTER pto_emi;

CREATE INDEX idx_pagos_factura ON pagos(id_info_factura, estab, pto_emi, secuencial);
```

### 2. **Archivos a Subir al Hosting**

#### **Backend:**
- ✅ `api/registrar_pago.php` (corregido para buscar por `id_info_tributaria`)

#### **Frontend:**
- ✅ `Pago_fac.html` (con debug mejorado)

#### **Base de Datos:**
- ✅ `actualizar_tabla_pagos_v2.sql` (ejecutar en el servidor)

## 🎯 Problemas Solucionados

### **1. Error "Factura no encontrada":**
- **Causa**: El frontend enviaba `id_info_tributaria` pero el backend buscaba por `id_info_factura`
- **Solución**: Cambié la consulta para buscar por `id_info_tributaria`

### **2. Estructura de Datos Mejorada:**
- **Antes**: Solo `id_info_factura` en tabla `pagos`
- **Ahora**: `id_info_factura`, `estab`, `pto_emi`, `secuencial` para identificación completa

### **3. Relaciones Correctas:**
```
info_tributaria (estab, pto_emi, secuencial)
    ↓ (id_info_tributaria)
info_factura (id_info_factura, valor_pagado)
    ↓ (id_info_factura)
pagos (id_info_factura, estab, pto_emi, secuencial, monto, ...)
```

## 🔍 Debug Mejorado

El sistema ahora muestra:
- ID Info Tributaria recibido
- ID Info Factura encontrado
- Número de factura (estab-pto_emi-secuencial)
- Valores de cálculo de saldo

## 📊 Flujo de Datos Corregido

1. **Frontend**: Envía `id_info_tributaria`
2. **Backend**: Busca factura por `id_info_tributaria`
3. **Backend**: Obtiene `id_info_factura` de la consulta
4. **Backend**: Guarda pago con todos los campos de identificación
5. **Backend**: Actualiza `valor_pagado` en `info_factura`

## ✅ Resultado Esperado

- ✅ Factura encontrada correctamente
- ✅ Saldo calculado de forma consistente
- ✅ Pagos registrados con identificación completa
- ✅ No más errores de "Factura no encontrada"
