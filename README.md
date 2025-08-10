# Sistema de Control - GloboCity

Sistema de gestión empresarial completo para GloboCity, incluyendo módulos de facturación, inventarios, pagos, gastos y productos.

## 🚀 Características

- **Interfaz moderna y responsiva** con diseño glass morphism
- **Módulos integrados**: Facturación, Inventarios, Pagos, Gastos, Productos
- **Dashboard en tiempo real** con estadísticas actualizadas
- **Sistema de autenticación seguro** con JWT
- **Base de datos MySQL** optimizada con índices y vistas
- **APIs RESTful** para integración completa
- **Sistema de logs** para auditoría
- **Configuración flexible** para diferentes entornos

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Extensiones PHP: PDO, PDO_MySQL, JSON, mbstring

## 🛠️ Instalación

### 1. Clonar el repositorio
```bash
git clone [url-del-repositorio]
cd soft_control
```

### 2. Configurar la base de datos

#### Opción A: Usar el script SQL automático
```bash
mysql -u globocit_globocit -p globocit_soft_control < database_setup.sql
```

#### Opción B: Ejecutar manualmente
1. Crear la base de datos `globocit_soft_control`
2. Ejecutar el script `database_setup.sql`
3. Verificar que las tablas se crearon correctamente

### 3. Configurar el archivo config.php

El archivo `config.php` ya está configurado con las credenciales de GloboCity:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'globocit_soft_control');
define('DB_USER', 'globocit_globocit');
define('DB_PASS', 'Correo2026+@');
```

### 4. Configurar permisos de directorios
```bash
chmod 755 uploads/
chmod 755 logs/
chmod 644 config.php
```

### 5. Verificar la instalación
Acceder a `https://www.globocity.com.ec/soft_control/`

## 🔐 Credenciales por defecto

- **Usuario**: `admin`
- **Contraseña**: `password`

⚠️ **Importante**: Cambiar la contraseña del administrador después de la primera instalación.

## 📊 Estructura de la base de datos

### Tablas principales:
- `usuarios` - Gestión de usuarios del sistema
- `productos` - Catálogo de productos
- `clientes` - Base de datos de clientes
- `facturas` - Facturación y ventas
- `factura_detalles` - Detalles de facturas
- `pagos` - Registro de pagos
- `gastos` - Control de gastos
- `movimientos_inventario` - Trazabilidad de inventario
- `logs_actividad` - Auditoría del sistema
- `configuraciones` - Configuraciones del sistema

### Vistas útiles:
- `v_productos_stock_bajo` - Productos con stock mínimo
- `v_facturas_pendientes` - Facturas por cobrar
- `v_ventas_mensual` - Resumen de ventas

## 🔧 Configuración avanzada

### Variables de entorno
Las principales configuraciones están en `config.php`:

```php
// Base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'globocit_soft_control');
define('DB_USER', 'globocit_globocit');
define('DB_PASS', 'Correo2026+@');

// Seguridad
define('JWT_SECRET', 'soft_control_jwt_secret_2024');
define('SESSION_EXPIRATION', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);

// Aplicación
define('BASE_URL', 'https://www.globocity.com.ec/soft_control');
define('TIMEZONE', 'America/Guayaquil');
```

### Configuraciones del sistema
Las configuraciones del sistema se almacenan en la tabla `configuraciones`:

- `empresa_nombre` - Nombre de la empresa
- `empresa_ruc` - RUC de la empresa
- `iva_porcentaje` - Porcentaje de IVA
- `moneda` - Moneda del sistema
- `stock_minimo_global` - Stock mínimo global

## 📱 Módulos disponibles

### 1. Facturación
- Crear y gestionar facturas
- Generar números de factura automáticos
- Calcular IVA automáticamente
- Estados: pendiente, pagada, anulada

### 2. Inventarios
- Control de stock en tiempo real
- Alertas de stock bajo
- Movimientos de inventario
- Categorización de productos

### 3. Pagos
- Registro de pagos por factura
- Múltiples métodos de pago
- Estados de confirmación
- Trazabilidad completa

### 4. Gastos
- Control de gastos empresariales
- Categorización de gastos
- Estados de aprobación
- Comprobantes adjuntos

### 5. Productos
- Catálogo completo de productos
- Precios de costo y venta
- Control de stock
- Categorías y marcas

## 🔌 APIs disponibles

### Autenticación
- `POST /api/login.php` - Iniciar sesión
- `POST /api/logout.php` - Cerrar sesión

### Dashboard
- `GET /api/dashboard_stats.php` - Estadísticas del dashboard
- `GET /api/recent_activity.php` - Actividad reciente

### Módulos (en desarrollo)
- `GET /api/facturas/` - Listar facturas
- `POST /api/facturas/` - Crear factura
- `GET /api/productos/` - Listar productos
- `POST /api/productos/` - Crear producto
- `GET /api/clientes/` - Listar clientes
- `POST /api/clientes/` - Crear cliente

## 🛡️ Seguridad

- **Autenticación JWT** con tokens seguros
- **Contraseñas hasheadas** con bcrypt
- **Headers de seguridad** configurados
- **Sesiones seguras** con httponly cookies
- **Rate limiting** para APIs
- **Logs de auditoría** completos
- **Sanitización de inputs** automática

## 📈 Monitoreo y logs

### Archivos de log
- `logs/YYYY-MM-DD.log` - Logs diarios del sistema
- Logs de errores en el servidor web

### Actividades registradas
- Inicios de sesión
- Creación/modificación de registros
- Errores del sistema
- Actividad de usuarios

## 🔄 Mantenimiento

### Backup de base de datos
```bash
mysqldump -u globocit_globocit -p globocit_soft_control > backup_$(date +%Y%m%d).sql
```

### Limpieza de logs
Los logs se rotan automáticamente por fecha. Para limpiar logs antiguos:
```bash
find logs/ -name "*.log" -mtime +30 -delete
```

### Actualización de estadísticas
Las estadísticas se actualizan automáticamente cada vez que se accede al dashboard.

## 🐛 Solución de problemas

### Error de conexión a la base de datos
1. Verificar credenciales en `config.php`
2. Confirmar que MySQL esté ejecutándose
3. Verificar permisos del usuario de base de datos

### Error de permisos
```bash
chmod 755 uploads/
chmod 755 logs/
chmod 644 config.php
```

### Problemas de sesión
1. Verificar configuración de cookies
2. Limpiar caché del navegador
3. Verificar configuración de SSL

## 📞 Soporte

Para soporte técnico:
- **Email**: admin@globocity.com.ec
- **Teléfono**: +593 4 1234567

## 📄 Licencia

Este proyecto es propiedad de GloboCity. Todos los derechos reservados.

---

**Versión**: 1.0.0  
**Última actualización**: Enero 2024  
**Desarrollado por**: GloboCity Team 