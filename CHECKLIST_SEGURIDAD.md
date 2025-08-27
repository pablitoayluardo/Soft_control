# ✅ CHECKLIST DE SEGURIDAD - SEPARACIÓN DE SISTEMAS

## 📋 VERIFICACIÓN DIARIA (ANTES DE TRABAJAR)

### 🔍 PASO 1: Ejecutar Verificación Automática
```bash
# Abrir en navegador:
http://tu-dominio.com/verificar_separacion_sistemas.php
```

**Resultado Esperado:**
- [ ] ✅ Conexión a base de datos exitosa
- [ ] ✅ Todas las tablas existen
- [ ] ✅ Archivos XML NO tienen INSERT INTO pagos
- [ ] ✅ Archivos de pagos usan tabla pagos correctamente
- [ ] ✅ Estructura de tabla pagos es correcta
- [ ] ✅ SISTEMA SEGURO - SEPARACIÓN CONFIRMADA

---

## 📁 VERIFICACIÓN DE ARCHIVOS

### 📄 Archivos de Importación XML (NO deben tocar tabla pagos)
- [ ] `api/upload_factura_individual_clean.php`
- [ ] `api/upload_factura_individual.php`
- [ ] `debug_xml_extraction.php`

**Verificar que:**
- [ ] NO contengan `INSERT INTO pagos` sin comentar
- [ ] Solo inserten en tablas de facturas
- [ ] Estén comentados los INSERT INTO pagos

### 💰 Archivos de Pagos Manuales (SÍ deben usar tabla pagos)
- [ ] `Pago_fac.html`
- [ ] `api/registrar_pago.php`
- [ ] `api/get_fact_pago.php`

**Verificar que:**
- [ ] Usen tabla pagos correctamente
- [ ] Tengan todas las columnas necesarias
- [ ] Funcionen sin errores

---

## 🗄️ VERIFICACIÓN DE BASE DE DATOS

### 📋 Tablas del Sistema de Facturas
- [ ] `info_tributaria` - Existe y tiene datos
- [ ] `info_factura` - Existe y tiene datos
- [ ] `detalle_factura_sri` - Existe y tiene datos
- [ ] `info_adicional_factura` - Existe
- [ ] `total_con_impuestos` - Existe
- [ ] `impuestos_detalle` - Existe

### 💰 Tablas del Sistema de Pagos
- [ ] `pagos` - Existe y estructura correcta
- [ ] `logs_actividad` - Existe

### 🔍 Verificar Estructura de Tabla Pagos
- [ ] `id_info_factura` - Existe
- [ ] `forma_pago` - Existe
- [ ] `monto` - Existe
- [ ] `nombre_banco` - Existe
- [ ] `numero_documento` - Existe
- [ ] `referencia` - Existe
- [ ] `descripcion` - Existe
- [ ] `fecha_pago` - Existe

---

## 🚨 PUNTOS CRÍTICOS A VERIFICAR

### ❌ PROHIBIDO (Causa errores)
- [ ] INSERT INTO pagos en archivos XML
- [ ] Referencias a columnas inexistentes
- [ ] Interferencia entre sistemas
- [ ] Código no comentado en archivos XML

### ✅ PERMITIDO (Funciona correctamente)
- [ ] INSERT en tablas de facturas desde XML
- [ ] INSERT en tabla pagos desde archivos de pagos
- [ ] UPDATE en info_factura desde registrar_pago.php
- [ ] SELECT desde cualquier tabla

---

## 🧪 PRUEBAS FUNCIONALES

### 📄 Probar Importación XML
- [ ] Subir factura XML
- [ ] Verificar que NO aparezca error de columnas
- [ ] Confirmar que se importe correctamente
- [ ] Verificar que NO se inserte en tabla pagos

### 💰 Probar Registro de Pagos
- [ ] Abrir Pago_fac.html
- [ ] Seleccionar factura pendiente
- [ ] Completar datos del pago
- [ ] Confirmar registro exitoso
- [ ] Verificar que se inserte en tabla pagos

### 📊 Probar Consultas
- [ ] Ver facturas pendientes
- [ ] Ver pagos registrados
- [ ] Verificar saldos actualizados
- [ ] Confirmar estatus correctos

---

## 🔧 VERIFICACIÓN EN CASO DE ERROR

### Si Aparece Error de Columnas:
1. [ ] Ejecutar `verificar_separacion_sistemas.php`
2. [ ] Identificar archivo problemático
3. [ ] Verificar si tiene INSERT INTO pagos sin comentar
4. [ ] Comentar código problemático
5. [ ] Probar nuevamente

### Si Aparece Error de Estructura:
1. [ ] Verificar estructura de tabla pagos
2. [ ] Confirmar que todas las columnas existan
3. [ ] Verificar tipos de datos correctos
4. [ ] Corregir estructura si es necesario

### Si Aparece Error de Relación:
1. [ ] Verificar que info_factura tenga id_info_tributaria
2. [ ] Confirmar que pagos tenga id_info_factura correcto
3. [ ] Verificar integridad referencial
4. [ ] Corregir relaciones si es necesario

---

## 📝 REGISTRO DE VERIFICACIONES

### Fecha: _______________
### Verificado por: _______________

**Estado del Sistema:**
- [ ] ✅ SEGURO - Todo funciona correctamente
- [ ] ⚠️ ADVERTENCIA - Hay problemas menores
- [ ] 🚨 CRÍTICO - Hay problemas graves

**Problemas Encontrados:**
- [ ] Ninguno
- [ ] Problema 1: ________________
- [ ] Problema 2: ________________
- [ ] Problema 3: ________________

**Acciones Tomadas:**
- [ ] Ninguna necesaria
- [ ] Acción 1: ________________
- [ ] Acción 2: ________________
- [ ] Acción 3: ________________

**Próxima Verificación:**
- [ ] Mañana
- [ ] En 3 días
- [ ] En 1 semana
- [ ] Otro: ________________

---

## 🎯 OBJETIVO FINAL

**META:** Mantener los sistemas de Facturas y Pagos **COMPLETAMENTE SEPARADOS** para evitar errores de columnas y conflictos de datos.

**INDICADOR DE ÉXITO:** 
- ✅ No aparecen errores de columnas
- ✅ Importación XML funciona sin problemas
- ✅ Registro de pagos funciona sin problemas
- ✅ Consultas devuelven datos correctos

**RESULTADO ESPERADO:** Sistema estable y confiable para gestionar facturas y pagos sin interferencias.
