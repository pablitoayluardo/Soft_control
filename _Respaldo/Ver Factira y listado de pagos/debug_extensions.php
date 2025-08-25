<?php
echo "🔍 Debug de extensiones PHP...\n\n";

// Mostrar todas las extensiones cargadas
echo "Extensiones cargadas:\n";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (strpos($ext, 'pdo') !== false) {
        echo "✅ $ext\n";
    }
}

echo "\nBuscando extensiones PDO específicas:\n";
$pdo_extensions = [
    'pdo',
    'pdo_mysql',
    'pdo_sqlite',
    'pdo_pgsql'
];

foreach ($pdo_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext: CARGADA\n";
    } else {
        echo "❌ $ext: NO CARGADA\n";
    }
}

echo "\nVerificando archivo php.ini:\n";
$phpIniPath = 'c:\xampp\php\php.ini';
if (file_exists($phpIniPath)) {
    $content = file_get_contents($phpIniPath);
    
    // Buscar líneas relacionadas con PDO
    $lines = explode("\n", $content);
    $pdoLines = [];
    
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'pdo') !== false) {
            $pdoLines[] = ($lineNum + 1) . ": " . trim($line);
        }
    }
    
    if (empty($pdoLines)) {
        echo "❌ No se encontraron líneas relacionadas con PDO en php.ini\n";
    } else {
        echo "Líneas relacionadas con PDO en php.ini:\n";
        foreach ($pdoLines as $line) {
            echo "  $line\n";
        }
    }
} else {
    echo "❌ No se encontró php.ini en $phpIniPath\n";
}

echo "\nInformación del sistema:\n";
echo "PHP Version: " . phpversion() . "\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "Archivo php.ini cargado: " . php_ini_loaded_file() . "\n";

echo "\n🔍 Fin del debug.\n";
?> 