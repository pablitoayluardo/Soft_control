<?php
/**
 * Script para habilitar pdo_mysql en el php.ini correcto
 */

echo "🔧 Buscando y configurando php.ini para CLI...\n\n";

// Obtener información del php.ini actual
$currentIni = php_ini_loaded_file();
echo "Archivo php.ini actual: " . ($currentIni ?: 'No encontrado') . "\n";

// Posibles ubicaciones de php.ini
$possiblePaths = [
    'c:\xampp\php\php.ini',
    'C:\xampp\php\php.ini',
    dirname(PHP_BINARY) . '\php.ini',
    dirname(PHP_BINARY) . '\php.ini-development',
    dirname(PHP_BINARY) . '\php.ini-production'
];

echo "\nBuscando archivos php.ini:\n";
$foundIni = null;

foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        echo "✅ Encontrado: $path\n";
        if (!$foundIni) {
            $foundIni = $path;
        }
    } else {
        echo "❌ No existe: $path\n";
    }
}

if (!$foundIni) {
    echo "\n❌ No se encontró ningún archivo php.ini\n";
    exit;
}

echo "\n🔧 Modificando: $foundIni\n";

// Leer el archivo
$content = file_get_contents($foundIni);

// Buscar y reemplazar líneas de pdo_mysql
$patterns = [
    ';extension=pdo_mysql' => 'extension=pdo_mysql',
    '; extension=pdo_mysql' => 'extension=pdo_mysql',
    ';extension = pdo_mysql' => 'extension=pdo_mysql',
    '; extension = pdo_mysql' => 'extension=pdo_mysql'
];

$modified = false;
foreach ($patterns as $search => $replace) {
    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
        echo "✅ Reemplazado: $search -> $replace\n";
        $modified = true;
    }
}

if (!$modified) {
    // Verificar si ya está habilitado
    if (strpos($content, 'extension=pdo_mysql') !== false) {
        echo "✅ pdo_mysql ya está habilitado en el archivo\n";
    } else {
        echo "⚠️ No se encontró pdo_mysql en el archivo\n";
        echo "Agregando línea de pdo_mysql...\n";
        
        // Buscar la sección de extensiones
        $lines = explode("\n", $content);
        $extensionSection = false;
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            
            // Buscar el inicio de la sección de extensiones
            if (strpos($line, '; Dynamic Extensions') !== false) {
                $extensionSection = true;
            }
            
            // Si estamos en la sección de extensiones, agregar pdo_mysql
            if ($extensionSection && strpos($line, 'extension=') !== false) {
                // Insertar después de la primera extensión encontrada
                array_splice($lines, $i + 1, 0, 'extension=pdo_mysql');
                $modified = true;
                echo "✅ Agregada línea extension=pdo_mysql\n";
                break;
            }
        }
        
        if (!$modified) {
            // Si no se encontró la sección, agregar al final
            $lines[] = '';
            $lines[] = '; PDO Extensions';
            $lines[] = 'extension=pdo_mysql';
            $modified = true;
            echo "✅ Agregada sección PDO al final del archivo\n";
        }
        
        $content = implode("\n", $lines);
    }
}

if ($modified) {
    // Guardar el archivo
    if (file_put_contents($foundIni, $content)) {
        echo "✅ Archivo php.ini modificado exitosamente\n";
    } else {
        echo "❌ Error: No se pudo escribir en el archivo php.ini\n";
        echo "Por favor, ejecuta este script como administrador\n";
        exit;
    }
}

echo "\n⚠️ IMPORTANTE: Reinicia Apache en XAMPP Control Panel\n";
echo "Luego ejecuta: php test_simple.php\n";

echo "\nPara verificar inmediatamente, ejecuta:\n";
echo "php -m | findstr pdo\n";
?> 