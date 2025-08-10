# 🚀 Guía de Instalación - Sistema de Control

## 📋 Información del Proyecto

- **Base de Datos**: `globocit_soft_control`
- **Usuario BD**: `globocit_globocit`
- **Contraseña BD**: `Correo2026+@`
- **URL del Sistema**: `https://www.globocity.com.ec/soft_control`
- **Entorno**: Producción

## 🔧 Pasos de Instalación

### 1. Subir Archivos al Servidor

Sube todos los archivos del proyecto a tu servidor web en la carpeta:
```
/soft_control/
```

### 2. Configurar Base de Datos

#### Opción A: Instalación Automática (Recomendada)

1. Accede a tu navegador y ve a:
   ```
   https://www.globocity.com.ec/soft_control/install.php
   ```

2. El script automáticamente:
   - ✅ Creará todas las tablas necesarias
   - ✅ Insertará datos de ejemplo
   - ✅ Configurará procedimientos almacenados
   - ✅ Creará vistas y triggers
   - ✅ Establecerá las credenciales de prueba

#### Opción B: Instalación Manual

Si prefieres hacerlo manualmente:

1. **Conectar a MySQL**:
   ```sql
   mysql -h localhost -u globocit_globocit -p
   ```

2. **Ejecutar el script SQL**:
   ```sql
   USE globocit_soft_control;
   SOURCE database.sql;
   ```

### 3. Verificar Instalación

#### Credenciales de Prueba
- **Usuario**: `admin`
- **Contraseña**: `123456`

#### URLs de Acceso
- **Login**: `https://www.globocity.com.ec/soft_control/`
- **Dashboard**: `https://www.globocity.com.ec/soft_control/dashboard.html`

## 📊 Estructura de la Base de Datos

### Tablas Creadas

#### 1. `usuarios`
- Almacena información de usuarios del sistema
- Campos: id, nombre_usuario, email, contraseña_hash, nombre_completo, fecha_registro, ultimo_login, activo, rol
- Índices optimizados para búsquedas rápidas

#### 2. `sesiones`
- Gestiona sesiones activas de usuarios
- Campos: id, usuario_id, token, ip_address, user_agent, fecha_creacion, fecha_expiracion, activa
- Limpieza automática de sesiones expiradas

#### 3. `logs_actividad`
- **Registra todo el historial de ingresos y actividades**
- Campos: id, usuario_id, accion, descripcion, ip_address, user_agent, fecha
- Cada login exitoso se registra automáticamente

#### 4. `configuraciones`
- Configuraciones dinámicas del sistema
- Campos: id, clave, valor, descripcion, tipo, fecha_creacion, fecha_actualizacion

### Procedimientos Almacenados

#### `RegistrarLogin(usuario_id, ip_address, user_agent)`
- Actualiza el último login del usuario
- Registra la actividad en logs_actividad
- Limpia sesiones expiradas automáticamente

#### `VerificarCredenciales(nombre_usuario, email, OUT usuario_id, OUT contraseña_hash, OUT activo, OUT rol)`
- Verifica credenciales de login
- Retorna información del usuario si es válido

### Vistas Útiles

#### `v_usuarios_activos`
- Lista de usuarios activos del sistema
- Incluye información de último login

#### `v_actividad_reciente`
- **Historial de ingresos y actividades recientes**
- Últimas 100 actividades del sistema
- Incluye IP y navegador del usuario

### Triggers Automáticos

#### `limpiar_sesiones_expiradas`
- Limpia automáticamente sesiones expiradas
- Se ejecuta antes de crear nuevas sesiones

#### `registrar_cambio_usuario`
- Registra automáticamente cambios en usuarios
- Se ejecuta cuando se activa/desactiva un usuario o cambia su rol

## 🔐 Seguridad Implementada

### Validación de Login
- ✅ Verificación de credenciales segura
- ✅ Hashing de contraseñas con bcrypt
- ✅ Límite de intentos de login (5 intentos)
- ✅ Bloqueo temporal por intentos fallidos
- ✅ Registro de IP y User Agent

### Historial de Ingresos
- ✅ **Cada login exitoso se registra automáticamente**
- ✅ Se guarda IP del usuario
- ✅ Se guarda navegador/dispositivo
- ✅ Se registra fecha y hora exacta
- ✅ Se actualiza último login del usuario

### Protección de Sesiones
- ✅ Tokens únicos por sesión
- ✅ Expiración automática de sesiones
- ✅ Limpieza automática de sesiones expiradas
- ✅ Protección contra sesiones duplicadas

## 📈 Monitoreo y Logs

### Actividades Registradas Automáticamente
- 🔐 **LOGIN**: Cada inicio de sesión exitoso
- 🚪 **LOGOUT**: Cada cierre de sesión
- 🔄 **CAMBIO_ESTADO**: Activación/desactivación de usuarios
- 👥 **CAMBIO_ROL**: Cambios de rol de usuario
- ❌ **LOGIN_FAILED**: Intentos fallidos de login

### Consultas Útiles para Monitoreo

#### Ver últimos logins
```sql
SELECT 
    u.nombre_usuario,
    u.ultimo_login,
    la.ip_address,
    la.user_agent
FROM usuarios u
LEFT JOIN logs_actividad la ON u.id = la.usuario_id
WHERE la.accion = 'LOGIN'
ORDER BY la.fecha DESC
LIMIT 10;
```

#### Ver actividad reciente
```sql
SELECT * FROM v_actividad_reciente LIMIT 20;
```

#### Ver sesiones activas
```sql
SELECT 
    u.nombre_usuario,
    s.ip_address,
    s.fecha_creacion,
    s.fecha_expiracion
FROM sesiones s
JOIN usuarios u ON s.usuario_id = u.id
WHERE s.activa = TRUE 
AND s.fecha_expiracion > CURRENT_TIMESTAMP;
```

## 🚀 Próximos Pasos

### 1. Probar el Sistema
1. Accede a `https://www.globocity.com.ec/soft_control/`
2. Usa las credenciales de prueba: `admin` / `123456`
3. Verifica que puedas acceder al dashboard
4. Prueba la funcionalidad de logout

### 2. Crear Usuarios Reales
Una vez que el sistema esté funcionando, puedes:
- Crear usuarios reales desde el panel de administración
- Configurar roles y permisos específicos
- Personalizar configuraciones del sistema

### 3. Configurar Notificaciones
- Configurar email SMTP para notificaciones
- Activar alertas de seguridad
- Configurar reportes automáticos

## 🔧 Solución de Problemas

### Error de Conexión a Base de Datos
- Verificar que la base de datos `globocit_soft_control` existe
- Confirmar credenciales de usuario `globocit_globocit`
- Verificar que el host `localhost` es correcto

### Error de Permisos
- Verificar que el usuario tiene permisos de CREATE, INSERT, UPDATE, DELETE
- Confirmar que puede crear procedimientos almacenados y triggers

### Problemas de Login
- Verificar que las tablas se crearon correctamente
- Confirmar que los usuarios de ejemplo se insertaron
- Revisar logs de error en el servidor

## 📞 Soporte

Si encuentras problemas durante la instalación:

1. **Revisar logs del servidor web**
2. **Verificar conectividad a la base de datos**
3. **Confirmar que PHP tiene extensión PDO habilitada**
4. **Verificar permisos de archivos en el servidor**

---

**✅ Sistema listo para producción con historial completo de ingresos** 