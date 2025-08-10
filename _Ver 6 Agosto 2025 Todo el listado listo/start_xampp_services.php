<?php
/**
 * Script para verificar y iniciar servicios de XAMPP
 */

echo "🔧 Verificando servicios de XAMPP...\n\n";

// Verificar si Apache está ejecutándose
echo "Verificando Apache...\n";
$apacheRunning = false;
$output = shell_exec('sc query Apache2.4 2>&1');
if (strpos($output, 'RUNNING') !== false) {
    echo "✅ Apache está ejecutándose\n";
    $apacheRunning = true;
} else {
    echo "❌ Apache no está ejecutándose\n";
}

// Verificar si MySQL está ejecutándose
echo "\nVerificando MySQL...\n";
$mysqlRunning = false;
$output = shell_exec('sc query MySQL 2>&1');
if (strpos($output, 'RUNNING') !== false) {
    echo "✅ MySQL está ejecutándose\n";
    $mysqlRunning = true;
} else {
    echo "❌ MySQL no está ejecutándose\n";
}

// Verificar si los servicios están ejecutándose con nombres alternativos
if (!$apacheRunning) {
    $output = shell_exec('sc query Apache2.2 2>&1');
    if (strpos($output, 'RUNNING') !== false) {
        echo "✅ Apache (2.2) está ejecutándose\n";
        $apacheRunning = true;
    }
}

if (!$mysqlRunning) {
    $output = shell_exec('sc query MySQL80 2>&1');
    if (strpos($output, 'RUNNING') !== false) {
        echo "✅ MySQL (8.0) está ejecutándose\n";
        $mysqlRunning = true;
    }
}

echo "\n📋 Resumen:\n";
echo "Apache: " . ($apacheRunning ? "✅ Ejecutándose" : "❌ No ejecutándose") . "\n";
echo "MySQL: " . ($mysqlRunning ? "✅ Ejecutándose" : "❌ No ejecutándose") . "\n";

if (!$apacheRunning || !$mysqlRunning) {
    echo "\n⚠️ Para iniciar los servicios:\n";
    echo "1. Abre XAMPP Control Panel\n";
    echo "2. Haz clic en 'Start' para Apache y MySQL\n";
    echo "3. O ejecuta desde la línea de comandos:\n";
    echo "   C:\\xampp\\xampp_start.exe\n";
    
    echo "\n¿Quieres intentar iniciar los servicios automáticamente? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) === 's' || trim(strtolower($line)) === 'si' || trim(strtolower($line)) === 'y' || trim(strtolower($line)) === 'yes') {
        echo "\n🔄 Intentando iniciar servicios...\n";
        
        if (!$apacheRunning) {
            echo "Iniciando Apache...\n";
            shell_exec('net start Apache2.4 2>&1');
        }
        
        if (!$mysqlRunning) {
            echo "Iniciando MySQL...\n";
            shell_exec('net start MySQL 2>&1');
        }
        
        echo "✅ Comando de inicio enviado\n";
    }
}

echo "\nPara probar la conexión después de iniciar los servicios:\n";
echo "C:\\xampp\\php\\php.exe test_simple.php\n";
?> 