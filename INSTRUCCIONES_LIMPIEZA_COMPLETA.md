# 🧹 INSTRUCCIONES COMPLETAS: LIMPIEZA Y REINSTALACIÓN DEL SISTEMA DE PAGOS

## 📋 **PASOS A SEGUIR**

### **⚠️ ADVERTENCIA IMPORTANTE**
**Este proceso eliminará TODOS los datos de pagos existentes. Solo procede si estás seguro de querer empezar desde cero.**

---

## **🔧 PASO 1: PREPARACIÓN**

### **1.1 Archivos Necesarios**
Asegúrate de tener estos archivos en tu hosting:
- ✅ `limpiar_y_reinstalar_pagos.sql` - Script de limpieza
- ✅ `verificar_sistema_pagos.php` - Script de verificación
- ✅ `Pago_fac.html` - Módulo de pagos
- ✅ `api/registrar_pago.php` - API de registro
- ✅ `api/get_fact_pago.php` - API de consulta

### **1.2 Acceso a Base de Datos**
- 🔑 Acceso a phpMyAdmin o cliente MySQL
- 📊 Permisos de administrador en la base de datos

---

## **🗑️ PASO 2: LIMPIEZA DE DATOS**

### **2.1 Ejecutar Script de Limpieza**
1. **Abrir phpMyAdmin**
2. **Seleccionar tu base de datos**
3. **Ir a la pestaña "SQL"**
4. **Copiar y pegar el contenido de `limpiar_y_reinstalar_pagos.sql`**
5. **Hacer clic en "Continuar"**

### **2.2 Verificar Ejecución**
El script realizará:
- ✅ Eliminar todos los registros de `pagos`
- ✅ Resetear `valor_pagado = 0` en `info_factura`
- ✅ Cambiar `estatus = 'REGISTRADO'` en facturas pagadas
- ✅ Eliminar logs relacionados con pagos
- ✅ Recrear estructura de tabla `pagos`

### **2.3 Resultado Esperado**
```
✅ SISTEMA DE PAGOS LIMPIO Y LISTO
✅ Todas las tablas han sido limpiadas y la estructura recreada
✅ Puedes proceder a usar el módulo de pagos desde cero
```

---

## **🔍 PASO 3: VERIFICACIÓN DEL SISTEMA**

### **3.1 Ejecutar Script de Verificación**
1. **Abrir en navegador:** `http://tu-dominio.com/verificar_sistema_pagos.php`
2. **Revisar todos los resultados**

### **3.2 Verificaciones que Realiza:**

#### **📋 Estructura de Tablas**
- ✅ Tabla `pagos` con todas las columnas requeridas
- ✅ Tabla `info_factura` con columnas críticas
- ✅ Tipos de datos correctos

#### **📄 Datos de Facturas**
- ✅ Total de facturas disponibles
- ✅ Distribución por estatus (REGISTRADO/PENDIENTE)
- ✅ Facturas con saldo pendiente

#### **💰 Tabla de Pagos**
- ✅ Confirmar que está vacía (0 registros)
- ✅ Estructura correcta

#### **🔌 APIs**
- ✅ Archivos de API presentes
- ✅ API `get_fact_pago.php` responde correctamente
- ✅ API `registrar_pago.php` existe

### **3.3 Resultado Esperado**
```
🎉 SISTEMA DE PAGOS LISTO PARA USAR
✅ Estructura de tabla pagos correcta
✅ Estructura de tabla info_factura correcta
✅ Hay facturas disponibles para pagos
✅ Sistema limpio para empezar desde cero
✅ Archivos de API presentes
```

---

## **🚀 PASO 4: PRUEBA DEL SISTEMA**

### **4.1 Acceder al Módulo**
1. **Abrir:** `http://tu-dominio.com/Pago_fac.html`
2. **Verificar que carga sin errores**

### **4.2 Verificar Funcionalidades**
- ✅ **Listado de facturas:** Debe mostrar facturas con saldo pendiente
- ✅ **Colores de estatus:** 
  - 🟢 Verde para "REGISTRADO"
  - 🟡 Amarillo para "PENDIENTE"
- ✅ **Estadísticas:** Total facturas, saldo total, facturas pagadas
- ✅ **Filtros:** Búsqueda por cliente y secuencial

### **4.3 Probar Registro de Pago**
1. **Seleccionar una factura**
2. **Hacer clic en "Pagar Factura"**
3. **Completar formulario:**
   - Monto (menor al saldo)
   - Método de pago
   - Fecha de pago
   - Institución (si aplica)
   - Documento/Referencia
4. **Confirmar pago**

### **4.4 Verificar Resultado**
- ✅ **Modal se cierra**
- ✅ **Mensaje de éxito**
- ✅ **Listado se actualiza**
- ✅ **Estatus cambia a "PENDIENTE"**
- ✅ **Saldo se reduce**

---

## **📊 PASO 5: VERIFICACIÓN FINAL**

### **5.1 Ejecutar Verificación Nuevamente**
1. **Abrir:** `http://tu-dominio.com/verificar_sistema_pagos.php`
2. **Verificar que aparece el pago registrado**

### **5.2 Comprobar en Base de Datos**
```sql
-- Verificar pago registrado
SELECT * FROM pagos ORDER BY fecha_registro DESC LIMIT 1;

-- Verificar actualización de factura
SELECT 
    f.estatus,
    f.valor_pagado,
    (f.importe_total - f.valor_pagado) as saldo
FROM info_factura f 
JOIN info_tributaria it ON f.id_info_tributaria = it.id_info_tributaria
WHERE it.estab = 'XXX' AND it.pto_emi = 'XXX' AND it.secuencial = 'XXXXXX';
```

---

## **✅ CRITERIOS DE ÉXITO**

### **Sistema Funcionando Correctamente Si:**
- ✅ **Verificación muestra:** "SISTEMA DE PAGOS LISTO PARA USAR"
- ✅ **Módulo carga:** Sin errores JavaScript
- ✅ **APIs responden:** JSON válido
- ✅ **Pagos se registran:** Sin errores SQL
- ✅ **Estatus cambia:** REGISTRADO → PENDIENTE → PAGADA
- ✅ **Colores funcionan:** Verde/Amarillo según estatus

### **Si Hay Problemas:**
- 🔍 **Revisar logs:** Error logs del servidor
- 🔍 **Verificar permisos:** Archivos y carpetas
- 🔍 **Comprobar configuración:** `config.php`
- 🔍 **Revisar estructura:** Tablas de base de datos

---

## **📞 SOPORTE**

### **Archivos de Diagnóstico:**
- `verificar_sistema_pagos.php` - Diagnóstico completo
- `limpiar_y_reinstalar_pagos.sql` - Limpieza automática

### **Logs Importantes:**
- Error logs del servidor web
- Logs de PHP
- Logs de MySQL

---

## **🎯 RESUMEN**

**Proceso Completo:**
1. 🗑️ **Limpiar** con `limpiar_y_reinstalar_pagos.sql`
2. 🔍 **Verificar** con `verificar_sistema_pagos.php`
3. 🚀 **Probar** con `Pago_fac.html`
4. ✅ **Confirmar** funcionamiento

**¡El sistema estará completamente limpio y listo para usar desde cero!** 🎉
