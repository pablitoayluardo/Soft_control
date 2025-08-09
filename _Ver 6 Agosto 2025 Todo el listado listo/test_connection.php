<?php
// =====================================================
// SCRIPT DE PRUEBA DE CONEXIÓN A LA BASE DE DATOS
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔍 Prueba de Conexión a la Base de Datos</h2>";

try {
    // Probar conexión
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
    
    // Obtener información de la base de datos
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $dbInfo = $stmt->fetch();
    echo "<p><strong>Base de datos:</strong> " . $dbInfo['db_name'] . "</p>";
    
    // Listar tablas existentes
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📋 Tablas encontradas:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>✅ $table</li>";
    }
    echo "</ul>";
    
    // Verificar datos en tablas principales
    echo "<h3>📊 Datos en tablas principales:</h3>";
    
    // Verificar usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $userCount = $stmt->fetch()['total'];
    echo "<p><strong>Usuarios:</strong> $userCount registros</p>";
    
    // Verificar productos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $productCount = $stmt->fetch()['total'];
    echo "<p><strong>Productos:</strong> $productCount registros</p>";
    
    // Verificar clientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes");
    $clientCount = $stmt->fetch()['total'];
    echo "<p><strong>Clientes:</strong> $clientCount registros</p>";
    
    // Verificar configuraciones
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM configuraciones");
    $configCount = $stmt->fetch()['total'];
    echo "<p><strong>Configuraciones:</strong> $configCount registros</p>";
    
    // Probar APIs
    echo "<h3>🔌 Prueba de APIs:</h3>";
    
    // Simular autenticación para probar las APIs
    session_start();
    $_SESSION['user_id'] = 1;
    $_SESSION['token'] = 'test_token';
    
    echo "<p><strong>Estado de autenticación:</strong> ";
    if (isAuthenticated()) {
        echo "✅ Autenticado</p>";
    } else {
        echo "❌ No autenticado</p>";
    }
    
    echo "<h3>🎯 Próximos pasos:</h3>";
    echo "<ol>";
    echo "<li>✅ Base de datos conectada</li>";
    echo "<li>✅ Tablas verificadas</li>";
    echo "<li>✅ Datos de ejemplo cargados</li>";
    echo "<li>🔄 <a href='index.html'>Probar el sistema</a></li>";
    echo "<li>🔄 <a href='dashboard.html'>Acceder al dashboard</a></li>";
    echo "</ol>";
    
    echo "<h3>🔐 Credenciales de prueba:</h3>";
    echo "<p><strong>Usuario:</strong> admin</p>";
    echo "<p><strong>Contraseña:</strong> password</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
    
    echo "<h3>🔧 Solución de problemas:</h3>";
    echo "<ul>";
    echo "<li>Verificar credenciales en config.php</li>";
    echo "<li>Confirmar que MySQL esté ejecutándose</li>";
    echo "<li>Verificar permisos del usuario de base de datos</li>";
    echo "<li>Ejecutar el script database_setup.sql</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><em>Script de prueba - Sistema de Control GloboCity</em></p>";
?> 