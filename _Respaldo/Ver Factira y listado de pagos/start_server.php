<?php
// =====================================================
// INICIAR SERVIDOR WEB SIMPLE
// =====================================================

echo "🚀 Iniciando servidor web...\n";
echo "📁 Directorio: " . __DIR__ . "\n";
echo "🌐 URL: http://localhost:8080\n";
echo "⏹️  Para detener: Ctrl+C\n\n";

echo "📋 Archivos disponibles:\n";
echo "- http://localhost:8080/facturacion.html\n";
echo "- http://localhost:8080/debug_facturas.php\n";
echo "- http://localhost:8080/test_connection_simple.php\n";
echo "- http://localhost:8080/test_api_direct.php\n\n";

echo "🔧 Iniciando servidor...\n";

// Ejecutar servidor PHP
$command = "php -S localhost:8080";
system($command);
?> 