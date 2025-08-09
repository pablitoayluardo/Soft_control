<?php
/**
 * PHP_CodeSniffer Simple - Análisis básico de código PHP
 */

function analyzeFile($file) {
    if (!file_exists($file)) {
        echo "Error: Archivo no encontrado: $file\n";
        return false;
    }
    
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    $issues = [];
    
    foreach ($lines as $lineNum => $line) {
        $lineNum++; // 1-based line numbers
        
        // Verificar espacios al final
        if (preg_match('/\s+$/', $line)) {
            $issues[] = "Línea $lineNum: Espacios en blanco al final de la línea";
        }
        
        // Verificar tabs vs espacios
        if (strpos($line, "\t") !== false) {
            $issues[] = "Línea $lineNum: Uso de tabs en lugar de espacios";
        }
        
        // Verificar líneas muy largas
        if (strlen($line) > 120) {
            $issues[] = "Línea $lineNum: Línea muy larga (" . strlen($line) . " caracteres)";
        }
        
        // Verificar PHP syntax básica
        if (strpos($line, "<?") !== false && strpos($line, "?>") !== false) {
            $issues[] = "Línea $lineNum: Evitar abrir y cerrar PHP en la misma línea";
        }
    }
    
    return $issues;
}

function showHelp() {
    echo "PHP_CodeSniffer Simple v1.0\n";
    echo "Uso: php phpcs.php [archivo]\n";
    echo "\n";
    echo "Opciones:\n";
    echo "  --help, -h    Mostrar esta ayuda\n";
    echo "  --version     Mostrar versión\n";
    echo "\n";
    echo "Ejemplos:\n";
    echo "  php phpcs.php mi_archivo.php\n";
    echo "  php phpcs.php *.php\n";
}

// Procesar argumentos
$args = $argv;
array_shift($args); // Remover nombre del script

if (empty($args) || in_array('--help', $args) || in_array('-h', $args)) {
    showHelp();
    exit(0);
}

if (in_array('--version', $args)) {
    echo "PHP_CodeSniffer Simple v1.0\n";
    exit(0);
}

$totalIssues = 0;
$totalFiles = 0;

foreach ($args as $pattern) {
    $files = glob($pattern);
    if (empty($files)) {
        $files = [$pattern]; // Tratar como archivo único
    }
    
    foreach ($files as $file) {
        if (!is_file($file) || !preg_match('/\.php$/i', $file)) {
            continue;
        }
        
        $totalFiles++;
        echo "\nAnalizando: $file\n";
        echo str_repeat('-', 50) . "\n";
        
        $issues = analyzeFile($file);
        
        if (empty($issues)) {
            echo "✓ No se encontraron problemas\n";
        } else {
            foreach ($issues as $issue) {
                echo "⚠ $issue\n";
                $totalIssues++;
            }
        }
    }
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "Resumen: $totalIssues problemas encontrados en $totalFiles archivos\n";

if ($totalIssues > 0) {
    exit(1);
} else {
    echo "✓ Todos los archivos analizados están correctos\n";
    exit(0);
}
?>