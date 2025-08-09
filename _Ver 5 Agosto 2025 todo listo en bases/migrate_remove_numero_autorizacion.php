<?php
// =====================================================
// MIGRATE REMOVE NUMERO_AUTORIZACION
// =====================================================

// Incluir configuración
require_once 'config.php';

echo "<h2>🔧 Migración: Remover campo redundante numero_autorizacion</h2>";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // Verificar si existe el campo numero_autorizacion
    $sql = "SHOW COLUMNS FROM info_tributaria LIKE 'numero_autorizacion'";
    $stmt = $pdo->query($sql);
    $existe = $stmt->fetch();
    
    if ($existe) {
        echo "<p>🔍 Campo numero_autorizacion encontrado. Procediendo a eliminarlo...</p>";
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Remover el campo numero_autorizacion
        $sql = "ALTER TABLE info_tributaria DROP COLUMN numero_autorizacion";
        $pdo->exec($sql);
        
        // Confirmar transacción
        $pdo->commit();
        
        echo "<p style='color: green;'>✅ Campo numero_autorizacion eliminado exitosamente</p>";
        echo "<p>📝 <strong>Nota:</strong> El campo clave_acceso ya contiene la misma información (número de autorización)</p>";
        
    } else {
        echo "<p style='color: blue;'>ℹ️ El campo numero_autorizacion no existe. No es necesario migrar.</p>";
    }
    
    // Mostrar estructura actual
    echo "<h3>📋 Estructura actual de info_tributaria:</h3>";
    $sql = "DESCRIBE info_tributaria";
    $stmt = $pdo->query($sql);
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Campo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Null</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Key</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Default</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Extra</th>";
    echo "</tr>";
    
    foreach ($columnas as $columna) {
        $bgColor = ($columna['Key'] === 'PRI') ? 'background: #28a745; color: white;' : '';
        $bgColor = ($columna['Field'] === 'clave_acceso') ? 'background: #ffc107; color: black;' : $bgColor;
        echo "<tr style='$bgColor'>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Default'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $columna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Migración completada</h3>";
    echo "<p>El campo <code>numero_autorizacion</code> ha sido eliminado porque era redundante con <code>clave_acceso</code>.</p>";
    echo "<p>Ambos campos contenían exactamente la misma información: el número de autorización de la factura.</p>";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 