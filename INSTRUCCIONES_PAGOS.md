# 📋 Instrucciones para el Módulo de Pagos - GloboCity

## ✅ **Lo que ya tienes implementado:**

### **Tabla `pagos` existente con campos:**
- `id` (auto increment)
- `factura_id` (relacionado con facturas)
- `clave_acceso` 
- `monto`
- `metodo_pago` (efectivo, tarjeta, transferencia, cheque, deposito, pago_movil, otro)
- `institucion`
- `documento`
- `referencia`
- `observacion`
- `estado` (pendiente, confirmado, rechazado)
- `fecha_pago`
- `usuario_id`

### **Formulario simplificado con:**
- ✅ **Información visible**: Número de factura, cliente, dirección, saldo
- ✅ **Monto**: Con validación que no supere el saldo
- ✅ **Método de Pago**: Select con opciones predefinidas
- ✅ **Institución**: Campo de texto libre
- ✅ **Documento**: Campo para número de documento
- ✅ **Referencia**: Campo para número de referencia
- ✅ **Observaciones**: Campo de texto libre

## 🚀 **Pasos para usar el módulo:**

### **1. Acceder al módulo**
- Abrir: `https://www.globocity.com.ec/soft_control/Pago_fac.html`
- Se mostrarán las facturas con saldo pendiente

### **2. Registrar un pago**
1. En la tabla de facturas, buscar la factura a pagar
2. En la columna "ACCIONES", seleccionar "Pagar Factura"
3. Se abrirá el modal con:
   - **Información de la factura** (solo lectura)
   - **Formulario de pago** (campos a completar)

 ### **3. Completar el formulario**
 - **Monto**: Ingresar cantidad (no mayor al saldo)
 - **Método de Pago**: Seleccionar de la lista
 - **Institución**: Opcional (requerido para transferencia, cheque, depósito)
 - **Documento**: Número de documento (opcional)
 - **Referencia**: Número de referencia (opcional)
 - **Fecha de Pago**: Seleccionar fecha (obligatorio)
 - **Usuario**: Nombre del usuario que registra el pago (solo lectura)
 - **Observaciones**: Texto adicional (opcional)

### **4. Confirmar pago**
- Hacer clic en "Confirmar Pago"
- Se validará que el monto no exceda el saldo
- Se registrará el pago en la base de datos
- Se actualizará el saldo de la factura

## 🔧 **Validaciones implementadas:**

 - ✅ Monto debe ser mayor a 0
 - ✅ Monto no puede superar el saldo pendiente
 - ✅ Método de pago es obligatorio
 - ✅ Fecha de pago es obligatoria
 - ✅ Institución es obligatoria para transferencia, cheque, depósito
 - ✅ Validación en tiempo real de campos requeridos

## 📊 **APIs disponibles:**

### **Registrar Pago**
 ```
 POST api/registrar_pago.php
 {
     "id_info_factura": "number",
     "monto": "number",
     "metodo_pago": "string",
     "institucion": "string (opcional)",
     "documento": "string (opcional)",
     "referencia": "string (opcional)",
     "fecha_pago": "date (obligatorio)",
     "usuario": "string (opcional)",
     "observacion": "string (opcional)"
 }
 ```

### **Obtener Facturas**
```
GET api/get_fact_pago.php
```

### **Obtener Historial de Pagos**
```
GET api/get_pagos_factura.php?clave_acceso=XXX
```

## 🎯 **Métodos de Pago disponibles:**
- efectivo
- tarjeta
- transferencia
- cheque
- deposito
- pago_movil
- otro

## 📝 **Notas importantes:**
- El sistema usa la tabla `pagos` existente
- No se requieren cambios en la base de datos
- Los pagos se registran con estado "confirmado" por defecto
- Se actualiza automáticamente el saldo de la factura
- Se registran logs de actividad para auditoría

## 🆘 **Solución de problemas:**
- Si no aparecen facturas: Verificar que hay facturas con saldo > 0
- Si hay errores de validación: Revisar que los campos obligatorios estén completos
- Si hay errores de conexión: Verificar la configuración de la base de datos

---

**¡El módulo está listo para usar!** 🎉
