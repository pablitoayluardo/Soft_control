<?php
/**
 * PHP Code Beautifier and Fixer Simple
 */

function fixFile($file) {
    if (!file_exists($file)) {
        echo "Error: Archivo no encontrado: $file\n";
        return false;
    }
    
    $content = file_get_contents($file);
    $original = $content;
    
    // Remover espacios al final de las líneas
    $content = preg_replace('/\s+$/m', '', $content);
    
    // Reemplazar tabs con espacios
    $content = str_replace("\t", "    ", $content);
    
    // Asegurar una línea vacía al final del archivo
    if (!empty($content) && substr($content, -1) !== "\n") {
        $content .= "\n";
    }
    
    // Verificar si hubo cambios
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "✓ Archivo corregido: $file\n";
        return true;
    } else {
        echo "✓ Sin cambios necesarios: $file\n";
        return false;
    }
}

function showHelp() {
    echo "PHP Code Beautifier and Fixer Simple v1.0\n";
    echo "Uso: php phpcbf.php [archivo]\n";
    echo "\n";
    echo "Opciones:\n";
    echo "  --help, -h    Mostrar esta ayuda\n";
    echo "  --version     Mostrar versión\n";
    echo "\n";
    echo "Ejemplos:\n";
    echo "  php phpcbf.php mi_archivo.php\n";
    echo "  php phpcbf.php *.php\n";
}

// Procesar argumentos
$args = $argv;
array_shift($args);

if (empty($args) || in_array('--help', $args) || in_array('-h', $args)) {
    showHelp();
    exit(0);
}

if (in_array('--version', $args)) {
    echo "PHP Code Beautifier and Fixer Simple v1.0\n";
    exit(0);
}

$totalFixed = 0;
$totalFiles = 0;

foreach ($args as $pattern) {
    $files = glob($pattern);
    if (empty($files)) {
        $files = [$pattern];
    }
    
    foreach ($files as $file) {
        if (!is_file($file) || !preg_match('/\.php$/i', $file)) {
            continue;
        }
        
        $totalFiles++;
        if (fixFile($file)) {
            $totalFixed++;
        }
    }
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "Resumen: $totalFixed archivos corregidos de $totalFiles analizados\n";
?>