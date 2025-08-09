<?php
echo "Iniciando prueba de conexión...\n";

// Verificar PDO
if (extension_loaded('pdo')) {
    echo "PDO: OK\n";
} else {
    echo "PDO: ERROR\n";
}

// Verificar PDO MySQL
if (extension_loaded('pdo_mysql')) {
    echo "PDO MySQL: OK\n";
} else {
    echo "PDO MySQL: ERROR\n";
}

// Intentar conexión
try {
    $host = 'localhost';
    $dbname = 'globocit_soft_control';
    $username = 'globocit_globocit';
    $password = 'Correo2026+@';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    
    echo "Conexión: EXITOSA\n";
    
    // Probar consulta
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "MySQL Version: " . $result['version'] . "\n";
    
} catch (Exception $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}

echo "Prueba completada.\n";
?> 