<?php
/**
 * Script para ejecutar SQL usando MySQL CLI directamente
 */

echo "🔧 Ejecutando SQL usando MySQL CLI...\n\n";

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'globocit_soft_control';
$username = 'globocit_globocit';
$password = 'Correo2026+@';

// Crear el comando MySQL
$mysqlCmd = "C:\\xampp\\mysql\\bin\\mysql.exe -h$host -u$username -p$password $dbname";

echo "Comando MySQL: $mysqlCmd\n\n";

// Leer el archivo SQL
$sqlFile = 'complete_fix_tables.sql';
if (!file_exists($sqlFile)) {
    echo "❌ Error: No se encontró el archivo $sqlFile\n";
    exit;
}

echo "📋 Leyendo archivo SQL: $sqlFile\n";
$sqlContent = file_get_contents($sqlFile);

// Crear archivo temporal con el SQL
$tempFile = 'temp_sql_commands.sql';
file_put_contents($tempFile, $sqlContent);

echo "📝 Archivo temporal creado: $tempFile\n";

// Ejecutar el comando MySQL
echo "🔄 Ejecutando comandos SQL...\n";
$command = "$mysqlCmd < $tempFile 2>&1";
$output = shell_exec($command);

if ($output === null) {
    echo "✅ Comandos SQL ejecutados exitosamente\n";
} else {
    echo "⚠️ Salida del comando MySQL:\n";
    echo $output . "\n";
}

// Limpiar archivo temporal
unlink($tempFile);

echo "\n📊 Verificando estructura de la tabla facturas...\n";

// Verificar la estructura de la tabla
$verifyCmd = "$mysqlCmd -e \"DESCRIBE facturas;\" 2>&1";
$verifyOutput = shell_exec($verifyCmd);

if ($verifyOutput) {
    echo "Estructura de la tabla facturas:\n";
    echo $verifyOutput . "\n";
} else {
    echo "❌ No se pudo verificar la estructura de la tabla\n";
}

echo "✅ Proceso completado.\n";
echo "\nAhora puedes probar registrar una factura nuevamente.\n";
?> 