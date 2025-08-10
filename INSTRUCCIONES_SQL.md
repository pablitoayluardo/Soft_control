# 📋 Instrucciones para Ejecutar el Script SQL

## 🎯 Objetivo
Crear la tabla `factura_detalles` en tu base de datos MySQL para almacenar los detalles de las facturas.

## 📁 Archivo a Ejecutar
`create_factura_detalles_manual.sql`

## 🚀 Métodos para Ejecutar el Script

### Método 1: phpMyAdmin (Recomendado)

1. **Abre phpMyAdmin** en tu navegador
   - URL típica: `http://localhost/phpmyadmin`
   - O la URL que uses para acceder a phpMyAdmin

2. **Selecciona la base de datos**
   - En el panel izquierdo, haz clic en `globocit_soft_control`

3. **Ve a la pestaña SQL**
   - En la parte superior, haz clic en la pestaña "SQL"

4. **Copia y pega el contenido**
   - Abre el archivo `create_factura_detalles_manual.sql`
   - Copia todo el contenido
   - Pégalo en el área de texto de phpMyAdmin

5. **Ejecuta el script**
   - Haz clic en el botón "Continuar" o "Go"

### Método 2: Línea de Comandos (Si tienes MySQL instalado)

```bash
mysql -u globocit_globocit -p globocit_soft_control < create_factura_detalles_manual.sql
```

### Método 3: MySQL Workbench

1. Abre MySQL Workbench
2. Conéctate a tu servidor MySQL
3. Selecciona la base de datos `globocit_soft_control`
4. Abre el archivo `create_factura_detalles_manual.sql`
5. Ejecuta el script (Ctrl+Shift+Enter)

## ✅ Verificación

Después de ejecutar el script, deberías ver:

1. **Tabla creada**: `factura_detalles`
2. **Estructura correcta**: 8 columnas
3. **Índices creados**: 3 índices
4. **Relación establecida**: Foreign key a `facturas`

## 🔍 Verificación Manual

Puedes verificar que todo funcionó correctamente ejecutando estas consultas en phpMyAdmin:

```sql
-- Verificar que la tabla existe
SHOW TABLES LIKE 'factura_detalles';

-- Ver la estructura de la tabla
DESCRIBE factura_detalles;

-- Ver los índices
SHOW INDEX FROM factura_detalles;

-- Verificar la relación
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'globocit_soft_control' 
AND TABLE_NAME = 'factura_detalles';
```

## ⚠️ Posibles Errores

### Error 1: "Table 'facturas' doesn't exist"
**Solución**: Primero debes crear la tabla `facturas` antes de crear `factura_detalles`

### Error 2: "Access denied"
**Solución**: Verifica que el usuario `globocit_globocit` tenga permisos de CREATE TABLE

### Error 3: "Foreign key constraint fails"
**Solución**: Asegúrate de que la tabla `facturas` tenga una columna `id` como PRIMARY KEY

## 📞 Soporte

Si encuentras algún problema, verifica:
1. Que la base de datos `globocit_soft_control` existe
2. Que la tabla `facturas` existe y tiene la estructura correcta
3. Que el usuario tiene permisos suficientes

## 🎉 ¡Listo!

Una vez que hayas ejecutado el script exitosamente, el sistema de facturación estará completamente configurado para:
- ✅ Registrar facturas individuales
- ✅ Almacenar detalles de facturas
- ✅ Validar duplicados
- ✅ Mostrar información relevante al usuario 