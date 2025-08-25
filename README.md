# 💰 Sistema de Control de Pagos - GloboCity

## 📋 Descripción

Sistema completo de gestión de pagos de facturas electrónicas desarrollado en PHP, MySQL y JavaScript. Permite registrar, consultar y gestionar pagos de facturas con validaciones completas y reportes detallados.

## 🚀 Características Principales

### ✅ Funcionalidades Implementadas
- **Registro de Pagos**: Formulario completo con validaciones
- **Consulta de Facturas**: Listado con filtros y paginación
- **Validaciones**: Monto, saldo pendiente, métodos de pago
- **Estados de Factura**: REGISTRADO, PENDIENTE, PAGADA
- **Logs de Actividad**: Registro completo de transacciones
- **Interfaz Responsiva**: Diseño moderno y funcional

### 🔧 Tecnologías Utilizadas
- **Backend**: PHP 7.4+, MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Base de Datos**: MySQL con PDO
- **APIs**: RESTful con JSON
- **Validaciones**: Cliente y servidor

## 📁 Estructura del Proyecto

```
📁 Sistema-Pagos/
├── 📄 Pago_fac.html              # Módulo principal de pagos
├── 📄 config.php                 # Configuración de base de datos
├── 📄 verificar_sistema_pagos.php # Script de verificación
├── 📄 test_apis.php              # Prueba de APIs
├── 📄 probar_apis_directo.php    # Prueba directa de APIs
├── 📄 limpiar_todo_completamente.sql # Script de limpieza total
├── 📄 INSTRUCCIONES_LIMPIEZA_COMPLETA_TOTAL.md # Instrucciones
└── 📁 api/
    ├── 📄 get_fact_pago.php      # API de consulta de facturas
    └── 📄 registrar_pago.php     # API de registro de pagos
```

## 🗄️ Estructura de Base de Datos

### Tabla `pagos`
```sql
CREATE TABLE pagos (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_info_factura INT NOT NULL,
    estab VARCHAR(3) NOT NULL,
    pto_emi VARCHAR(3) NOT NULL,
    secuencial VARCHAR(9) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque', 'deposito', 'pago_movil', 'otro') NOT NULL,
    nombre_banco VARCHAR(100) NULL,
    numero_documento VARCHAR(50) NULL,
    referencia VARCHAR(50) NULL,
    descripcion TEXT NULL,
    fecha_pago DATE NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Tabla `info_factura`
```sql
-- Campos principales:
- id_info_factura (PK)
- id_info_tributaria (FK)
- valor_pagado
- estatus (ENUM: 'REGISTRADO', 'PENDIENTE', 'PAGADA')
```

## 🛠️ Instalación

### 1. Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Extensión PDO habilitada

### 2. Configuración de Base de Datos
1. Crear base de datos MySQL
2. Ejecutar scripts de creación de tablas
3. Configurar `config.php` con credenciales

### 3. Configuración del Sistema
```php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tu_base_datos');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_password');
define('DB_CHARSET', 'utf8mb4');
```

## 📖 Uso del Sistema

### Acceso al Módulo
```
http://tu-dominio.com/Pago_fac.html
```

### Verificación del Sistema
```
http://tu-dominio.com/verificar_sistema_pagos.php
```

### Prueba de APIs
```
http://tu-dominio.com/probar_apis_directo.php
```

## 🔍 Verificación y Diagnóstico

### Scripts de Verificación
- `verificar_sistema_pagos.php`: Verificación completa del sistema
- `test_apis.php`: Prueba básica de APIs
- `probar_apis_directo.php`: Prueba avanzada de APIs

### Limpieza del Sistema
- `limpiar_todo_completamente.sql`: Limpieza total de datos
- `INSTRUCCIONES_LIMPIEZA_COMPLETA_TOTAL.md`: Instrucciones detalladas

## 🔧 APIs Disponibles

### GET `/api/get_fact_pago.php`
**Obtiene facturas con saldo pendiente**
```json
{
    "success": true,
    "facturas": [...],
    "pagination": {...}
}
```

### POST `/api/registrar_pago.php`
**Registra un nuevo pago**
```json
{
    "id_info_factura": 123,
    "monto": 100.50,
    "metodo_pago": "transferencia",
    "fecha_pago": "2024-01-15",
    "institucion": "Banco XYZ",
    "documento": "REF123",
    "referencia": "TRX456",
    "observacion": "Pago parcial"
}
```

## 🎯 Funcionalidades del Frontend

### Formulario de Pago
- **3 columnas** para mejor visualización
- **Validaciones en tiempo real**
- **Cálculo automático de saldo**
- **Métodos de pago dinámicos**

### Listado de Facturas
- **Filtros por cliente y secuencial**
- **Ordenamiento por múltiples campos**
- **Paginación**
- **Estados con colores diferenciados**

## 🔒 Seguridad

### Validaciones Implementadas
- **Monto no excede saldo pendiente**
- **Formato de fecha válido**
- **Métodos de pago permitidos**
- **Campos requeridos**
- **Sanitización de datos**

### Logs de Actividad
- **Registro de todas las transacciones**
- **Información detallada de cambios**
- **Auditoría completa**

## 📊 Reportes y Estadísticas

### Información Disponible
- Total de facturas
- Saldo total pendiente
- Facturas pagadas
- Distribución por estatus
- Historial de pagos

## 🚀 Despliegue

### Hosting Compartido
1. Subir archivos via FTP
2. Configurar base de datos
3. Ejecutar scripts de verificación
4. Probar funcionalidades

### Servidor Dedicado
1. Configurar servidor web
2. Instalar dependencias
3. Configurar base de datos
4. Desplegar aplicación

## 🐛 Solución de Problemas

### Problemas Comunes
1. **Error de conexión a BD**: Verificar config.php
2. **APIs no responden**: Verificar permisos de archivos
3. **Validaciones fallan**: Revisar estructura de tablas
4. **Frontend no carga**: Verificar rutas y archivos

### Scripts de Diagnóstico
- `verificar_sistema_pagos.php`: Diagnóstico completo
- `probar_apis_directo.php`: Prueba de APIs
- `limpiar_todo_completamente.sql`: Reset del sistema

## 📝 Changelog

### Versión 1.0.0 (2024-01-15)
- ✅ Sistema completo de pagos
- ✅ Validaciones implementadas
- ✅ APIs funcionales
- ✅ Interfaz responsiva
- ✅ Scripts de verificación
- ✅ Documentación completa

## 👥 Contribución

1. Fork el proyecto
2. Crear rama para nueva funcionalidad
3. Commit cambios
4. Push a la rama
5. Crear Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver `LICENSE` para más detalles.

## 📞 Soporte

Para soporte técnico o consultas:
- Revisar documentación
- Ejecutar scripts de diagnóstico
- Verificar logs de error
- Contactar al equipo de desarrollo

---

**Desarrollado con ❤️ para GloboCity** 