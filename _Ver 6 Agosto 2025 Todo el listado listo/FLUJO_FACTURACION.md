# 🔄 Flujo de Facturación - Sistema SRI

## 📋 Resumen del Flujo

El sistema de facturación sigue un flujo específico para garantizar la integridad de los datos y las relaciones correctas entre tablas.

## 🎯 Orden de Inserción

### 1. **info_tributaria** (PRIMERO - Tabla Base)
- **Clave Primaria:** `id_info_tributaria` (INT AUTO_INCREMENT)
- **Clave Única:** `clave_acceso` (VARCHAR(50) UNIQUE NOT NULL) ⭐ **IMPORTANTE**
- **Propósito:** Información tributaria del emisor (RUC, razón social, etc.)
- **Dependencias:** Ninguna
- **Nota:** `clave_acceso` es el identificador único de la factura (equivalente al número de autorización)

### 2. **info_factura** (SEGUNDO - Depende de info_tributaria)
- **Clave Primaria:** `id_info_factura` (INT AUTO_INCREMENT)
- **Clave Foránea:** `id_info_tributaria` → `info_tributaria.id_info_tributaria`
- **Propósito:** Información general de la factura (fecha, cliente, totales, etc.)
- **Dependencias:** `info_tributaria`

### 3. **detalle_factura_sri** (TERCERO - Depende de info_factura)
- **Clave Primaria:** `id_detalle` (INT AUTO_INCREMENT)
- **Clave Foránea:** `id_info_factura` → `info_factura.id_info_factura`
- **Propósito:** Líneas de detalle de la factura (productos/servicios)
- **Dependencias:** `info_factura`

### 4. **info_adicional_factura** (CUARTO - Depende de info_factura)
- **Clave Primaria:** `id_info_adicional` (INT AUTO_INCREMENT)
- **Clave Foránea:** `id_info_factura` → `info_factura.id_info_factura`
- **Propósito:** Información adicional de la factura
- **Dependencias:** `info_factura`

### 5. **pagos** (QUINTO - Depende de info_factura)
- **Clave Primaria:** `id_pago` (INT AUTO_INCREMENT)
- **Clave Foránea:** `id_info_factura` → `info_factura.id_info_factura`
- **Propósito:** Formas de pago de la factura
- **Dependencias:** `info_factura`

### 6. **total_con_impuestos** (SEXTO - Depende de info_factura)
- **Clave Primaria:** `id_total_impuesto` (INT AUTO_INCREMENT)
- **Clave Foránea:** `id_info_factura` → `info_factura.id_info_factura`
- **Propósito:** Impuestos totales aplicados a la factura
- **Dependencias:** `info_factura`

### 7. **impuestos_detalle** (SÉPTIMO - Depende de detalle_factura_sri)
- **Clave Primaria:** `id_impuesto_detalle` (INT AUTO_INCREMENT)
- **Clave Foránea:** `id_detalle` → `detalle_factura_sri.id_detalle`
- **Propósito:** Impuestos aplicados a cada línea de detalle
- **Dependencias:** `detalle_factura_sri`

## 🔑 Validaciones Importantes

### 1. **Clave de Acceso Única**
```sql
-- Verificar si ya existe una factura con la misma clave_acceso
SELECT f.id_info_factura, f.razon_social_comprador, f.importe_total, it.clave_acceso
FROM info_factura f
JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
WHERE it.clave_acceso = ?
```

### 2. **Secuencial y Fecha Únicos**
```sql
-- Verificar si existe una factura con el mismo secuencial y fecha
SELECT f.id_info_factura, f.razon_social_comprador, f.importe_total, it.clave_acceso
FROM info_factura f
JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
WHERE it.secuencial = ? AND f.fecha_emision = ?
```

## 📊 Estructura de Respuesta

```json
{
    "success": true,
    "message": "Factura registrada exitosamente",
    "data": {
        "clave_acceso": "1107202501172164244300120021000000018281413174415",
        "secuencial": "000000001",
        "cliente": "EMPRESA EJEMPLO S.A.",
        "total": 100.00,
        "info_tributaria_id": 1,
        "info_factura_id": 1,
        "resumen": {
            "detalles_insertados": 2,
            "adicionales_insertados": 1,
            "pagos_insertados": 1,
            "impuestos_totales_insertados": 1,
            "impuestos_detalle_insertados": 2
        }
    }
}
```

## 🚀 Proceso de Inserción

1. **Validación:** Verificar que no exista la factura por `clave_acceso` o `secuencial + fecha`
2. **Transacción:** Iniciar transacción para garantizar integridad
3. **Inserción Ordenada:** Seguir el orden establecido (1-7)
4. **Confirmación:** Commit de la transacción
5. **Resumen:** Devolver resumen completo de la inserción

## ⚠️ Consideraciones Importantes

- **`clave_acceso`** es la clave única más importante para identificar facturas
- Todas las inserciones se realizan dentro de una transacción
- Si falla cualquier paso, se hace rollback completo
- Los IDs se generan automáticamente y se usan para las relaciones
- El sistema maneja casos donde algunos datos pueden estar vacíos

## 🔧 Archivos Principales

- `fix_table_structure.php` - Crear/actualizar estructura de tablas
- `api/upload_factura_individual.php` - API principal de inserción
- `debug_xml_extraction.php` - Debug de extracción de XML
- `test_new_structure.php` - Verificación de estructura
- `resumen_estructura_completa.php` - Resumen completo del sistema 