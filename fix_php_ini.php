<?php
/**
 * Script para habilitar PDO en php.ini de XAMPP
 */

echo "🔧 Configurando php.ini para habilitar PDO...\n\n";

$phpIniPath = 'c:\xampp\php\php.ini';

// Verificar si el archivo existe
if (!file_exists($phpIniPath)) {
    echo "❌ Error: No se encontró el archivo php.ini en $phpIniPath\n";
    echo "Por favor, verifica la ruta de tu instalación de XAMPP\n";
    exit;
}

echo "✅ Archivo php.ini encontrado en: $phpIniPath\n\n";

// Leer el archivo
$content = file_get_contents($phpIniPath);

// Verificar si PDO ya está habilitado
if (strpos($content, 'extension=pdo_mysql') !== false && strpos($content, ';extension=pdo_mysql') === false) {
    echo "✅ PDO MySQL ya está habilitado\n";
} else {
    echo "⚠️ PDO MySQL no está habilitado, procediendo a habilitarlo...\n";
    
    // Reemplazar las líneas comentadas
    $content = str_replace(';extension=pdo_mysql', 'extension=pdo_mysql', $content);
    $content = str_replace(';extension=pdo', 'extension=pdo', $content);
    
    // Guardar el archivo
    if (file_put_contents($phpIniPath, $content)) {
        echo "✅ Archivo php.ini modificado exitosamente\n";
    } else {
        echo "❌ Error: No se pudo escribir en el archivo php.ini\n";
        echo "Por favor, ejecuta este script como administrador\n";
        exit;
    }
}

echo "\n📋 Resumen de cambios:\n";
echo "- PDO MySQL habilitado\n";
echo "- PDO habilitado\n";

echo "\n⚠️ IMPORTANTE: Ahora necesitas:\n";
echo "1. Reiniciar Apache en XAMPP Control Panel\n";
echo "2. Ejecutar: php test_connection_simple.php\n";
echo "3. Si funciona, ejecutar: php execute_complete_fix.php\n";

echo "\n¿Quieres que reinicie Apache automáticamente? (s/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) === 's' || trim(strtolower($line)) === 'si' || trim(strtolower($line)) === 'y' || trim(strtolower($line)) === 'yes') {
    echo "\n🔄 Reiniciando Apache...\n";
    
    // Comando para reiniciar Apache (Windows)
    $command = 'net stop Apache2.4 && net start Apache2.4';
    if (strpos(shell_exec('sc query Apache2.4'), 'RUNNING') !== false) {
        echo "✅ Apache reiniciado exitosamente\n";
    } else {
        echo "⚠️ No se pudo reiniciar Apache automáticamente\n";
        echo "Por favor, reinicia Apache manualmente desde XAMPP Control Panel\n";
    }
}

echo "\n✅ Configuración completada. Prueba la conexión con:\n";
echo "php test_connection_simple.php\n";
?> 