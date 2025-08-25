<?php
/**
 * Script para habilitar específicamente pdo_mysql
 */

echo "🔧 Habilitando pdo_mysql en php.ini...\n\n";

$phpIniPath = 'c:\xampp\php\php.ini';

// Verificar si el archivo existe
if (!file_exists($phpIniPath)) {
    echo "❌ Error: No se encontró el archivo php.ini en $phpIniPath\n";
    exit;
}

// Leer el archivo
$content = file_get_contents($phpIniPath);

// Buscar la línea de pdo_mysql
$lines = explode("\n", $content);
$modified = false;

for ($i = 0; $i < count($lines); $i++) {
    $line = trim($lines[$i]);
    
    // Buscar la línea comentada de pdo_mysql
    if ($line === ';extension=pdo_mysql') {
        echo "✅ Encontrada línea comentada de pdo_mysql en línea " . ($i + 1) . "\n";
        $lines[$i] = 'extension=pdo_mysql';
        $modified = true;
        break;
    }
}

if (!$modified) {
    echo "⚠️ No se encontró la línea comentada de pdo_mysql\n";
    echo "Buscando otras variaciones...\n";
    
    // Buscar otras posibles variaciones
    $patterns = [
        ';extension=pdo_mysql',
        '; extension=pdo_mysql',
        ';extension = pdo_mysql',
        '; extension = pdo_mysql'
    ];
    
    foreach ($patterns as $pattern) {
        if (strpos($content, $pattern) !== false) {
            $content = str_replace($pattern, 'extension=pdo_mysql', $content);
            $modified = true;
            echo "✅ Reemplazada variación: $pattern\n";
            break;
        }
    }
}

if ($modified) {
    // Guardar el archivo
    if (file_put_contents($phpIniPath, implode("\n", $lines))) {
        echo "✅ Archivo php.ini modificado exitosamente\n";
    } else {
        echo "❌ Error: No se pudo escribir en el archivo php.ini\n";
        echo "Por favor, ejecuta este script como administrador\n";
        exit;
    }
} else {
    echo "❌ No se encontró la línea de pdo_mysql para modificar\n";
    echo "Verificando si ya está habilitada...\n";
    
    if (strpos($content, 'extension=pdo_mysql') !== false) {
        echo "✅ pdo_mysql ya está habilitado en el archivo\n";
    } else {
        echo "❌ No se encontró pdo_mysql en el archivo\n";
        echo "Por favor, verifica manualmente el archivo php.ini\n";
    }
}

echo "\n⚠️ IMPORTANTE: Reinicia Apache en XAMPP Control Panel\n";
echo "Luego ejecuta: php test_simple.php\n";
?> 