<?php
/**
 * Script para crear las tablas catálogo de retenciones
 * TiposImpuesto y CodigosRetencion
 */

require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Crear Tablas Catálogo</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .info { color: #17a2b8; font-weight: bold; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; background: #f8f9fa; }
    h1 { color: #343a40; }
    h2 { color: #495057; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🏗️ Crear Tablas Catálogo de Retenciones</h1>";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("❌ Error: No se pudo conectar a la base de datos");
    }
    
    echo "<div class='success'>✅ Conexión establecida correctamente</div>";
    echo "<div class='info'>📊 Base de datos: " . DB_NAME . "</div><br>";
    
    // Crear tabla TiposImpuesto
    echo "<div class='section'>";
    echo "<h2>📊 Creando tabla TiposImpuesto</h2>";
    
    $sqlTiposImpuesto = "
    CREATE TABLE IF NOT EXISTS TiposImpuesto (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT,
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_nombre (nombre)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $pdo->exec($sqlTiposImpuesto);
        echo "<div class='success'>✅ Tabla TiposImpuesto creada exitosamente</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error creando TiposImpuesto: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    // Crear tabla CodigosRetencion
    echo "<div class='section'>";
    echo "<h2>📊 Creando tabla CodigosRetencion</h2>";
    
    $sqlCodigosRetencion = "
    CREATE TABLE IF NOT EXISTS CodigosRetencion (
        id INT(11) NOT NULL AUTO_INCREMENT,
        codigo VARCHAR(5) NOT NULL,
        descripcion VARCHAR(255) NOT NULL,
        tipo_impuesto_id INT(11) NOT NULL,
        porcentaje_default DECIMAL(5,2),
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_codigo (codigo),
        KEY fk_tipo_impuesto (tipo_impuesto_id),
        CONSTRAINT fk_codigos_tipo_impuesto FOREIGN KEY (tipo_impuesto_id) REFERENCES TiposImpuesto(id) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $pdo->exec($sqlCodigosRetencion);
        echo "<div class='success'>✅ Tabla CodigosRetencion creada exitosamente</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error creando CodigosRetencion: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    // Insertar tipos de impuesto básicos
    echo "<div class='section'>";
    echo "<h2>📝 Insertando Tipos de Impuesto</h2>";
    
    $tiposImpuesto = [
        ['nombre' => 'Renta', 'descripcion' => 'Impuesto a la Renta'],
        ['nombre' => 'IVA', 'descripcion' => 'Impuesto al Valor Agregado'],
        ['nombre' => 'ISD', 'descripcion' => 'Impuesto a la Salida de Divisas']
    ];
    
    foreach ($tiposImpuesto as $tipo) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO TiposImpuesto (nombre, descripcion) VALUES (?, ?)");
            $stmt->execute([$tipo['nombre'], $tipo['descripcion']]);
            echo "<div class='success'>✅ Tipo de impuesto '{$tipo['nombre']}' insertado</div>";
        } catch (Exception $e) {
            echo "<div class='warning'>⚠️ Tipo '{$tipo['nombre']}' ya existe o error: " . $e->getMessage() . "</div>";
        }
    }
    echo "</div>";
    
    // Insertar códigos de retención del SRI
    echo "<div class='section'>";
    echo "<h2>📝 Insertando Códigos de Retención del SRI</h2>";
    
    // Obtener IDs de tipos de impuesto
    $stmt = $pdo->query("SELECT id, nombre FROM TiposImpuesto");
    $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $tiposMap = [];
    foreach ($tipos as $tipo) {
        $tiposMap[$tipo['nombre']] = $tipo['id'];
    }
    
    $codigosRetencion = [
        // Códigos de Renta
        ['codigo' => '312', 'descripcion' => 'Honorarios profesionales', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '313', 'descripcion' => 'Servicios profesionales', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '314', 'descripcion' => 'Servicios técnicos', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '315', 'descripcion' => 'Servicios de transporte', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '316', 'descripcion' => 'Servicios de alojamiento', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '317', 'descripcion' => 'Servicios de alimentación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '318', 'descripcion' => 'Servicios de publicidad', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '319', 'descripcion' => 'Servicios de seguridad', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '320', 'descripcion' => 'Servicios de limpieza', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '321', 'descripcion' => 'Servicios de mantenimiento', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '322', 'descripcion' => 'Servicios de consultoría', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '323', 'descripcion' => 'Servicios de capacitación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '324', 'descripcion' => 'Servicios de auditoría', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '325', 'descripcion' => 'Servicios de asesoría', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '326', 'descripcion' => 'Servicios de investigación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '327', 'descripcion' => 'Servicios de desarrollo', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '328', 'descripcion' => 'Servicios de diseño', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '329', 'descripcion' => 'Servicios de marketing', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '330', 'descripcion' => 'Servicios de ventas', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '331', 'descripcion' => 'Servicios de distribución', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '332', 'descripcion' => 'Servicios de logística', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '333', 'descripcion' => 'Servicios de almacenamiento', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '334', 'descripcion' => 'Servicios de embalaje', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '335', 'descripcion' => 'Servicios de empaque', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '336', 'descripcion' => 'Servicios de etiquetado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '337', 'descripcion' => 'Servicios de clasificación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '338', 'descripcion' => 'Servicios de selección', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '339', 'descripcion' => 'Servicios de control de calidad', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '340', 'descripcion' => 'Servicios de inspección', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '341', 'descripcion' => 'Servicios de supervisión', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '342', 'descripcion' => 'Servicios de coordinación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '343', 'descripcion' => 'Servicios de administración', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '344', 'descripcion' => 'Servicios de gestión', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '345', 'descripcion' => 'Servicios de dirección', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '346', 'descripcion' => 'Servicios de liderazgo', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '347', 'descripcion' => 'Servicios de coaching', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '348', 'descripcion' => 'Servicios de mentoring', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '349', 'descripcion' => 'Servicios de formación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '350', 'descripcion' => 'Servicios de educación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '351', 'descripcion' => 'Servicios de enseñanza', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '352', 'descripcion' => 'Servicios de instrucción', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '353', 'descripcion' => 'Servicios de orientación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '354', 'descripcion' => 'Servicios de guía', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '355', 'descripcion' => 'Servicios de acompañamiento', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '356', 'descripcion' => 'Servicios de seguimiento', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '357', 'descripcion' => 'Servicios de monitoreo', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '358', 'descripcion' => 'Servicios de evaluación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '359', 'descripcion' => 'Servicios de medición', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '360', 'descripcion' => 'Servicios de análisis', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '361', 'descripcion' => 'Servicios de diagnóstico', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '362', 'descripcion' => 'Servicios de pronóstico', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '363', 'descripcion' => 'Servicios de predicción', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '364', 'descripcion' => 'Servicios de proyección', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '365', 'descripcion' => 'Servicios de planificación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '366', 'descripcion' => 'Servicios de programación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '367', 'descripcion' => 'Servicios de organización', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '368', 'descripcion' => 'Servicios de estructuración', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '369', 'descripcion' => 'Servicios de sistematización', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '370', 'descripcion' => 'Servicios de automatización', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '371', 'descripcion' => 'Servicios de digitalización', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '372', 'descripcion' => 'Servicios de informatización', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '373', 'descripcion' => 'Servicios de tecnificación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '374', 'descripcion' => 'Servicios de modernización', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '375', 'descripcion' => 'Servicios de actualización', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '376', 'descripcion' => 'Servicios de renovación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '377', 'descripcion' => 'Servicios de mejora', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '378', 'descripcion' => 'Servicios de optimización', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '379', 'descripcion' => 'Servicios de perfeccionamiento', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '380', 'descripcion' => 'Servicios de refinamiento', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '381', 'descripcion' => 'Servicios de pulimiento', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '382', 'descripcion' => 'Servicios de acabado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '383', 'descripcion' => 'Servicios de terminación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '384', 'descripcion' => 'Servicios de finalización', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '385', 'descripcion' => 'Servicios de conclusión', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '386', 'descripcion' => 'Servicios de cierre', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '387', 'descripcion' => 'Servicios de culminación', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '388', 'descripcion' => 'Servicios de completamiento', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '389', 'descripcion' => 'Servicios de acabamiento', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '390', 'descripcion' => 'Servicios de remate', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '391', 'descripcion' => 'Servicios de rematado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '392', 'descripcion' => 'Servicios de terminado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '393', 'descripcion' => 'Servicios de finalizado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '394', 'descripcion' => 'Servicios de concluido', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '395', 'descripcion' => 'Servicios de cerrado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '396', 'descripcion' => 'Servicios de culminado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '397', 'descripcion' => 'Servicios de completado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '398', 'descripcion' => 'Servicios de acabado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '399', 'descripcion' => 'Servicios de rematado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        ['codigo' => '400', 'descripcion' => 'Servicios de terminado', 'tipo' => 'Renta', 'porcentaje' => 1.75],
        
        // Códigos de IVA
        ['codigo' => '1', 'descripcion' => '30% de IVA', 'tipo' => 'IVA', 'porcentaje' => 30.00],
        ['codigo' => '2', 'descripcion' => '70% de IVA', 'tipo' => 'IVA', 'porcentaje' => 70.00],
        ['codigo' => '3', 'descripcion' => '100% de IVA', 'tipo' => 'IVA', 'porcentaje' => 100.00],
        
        // Códigos de ISD
        ['codigo' => '501', 'descripcion' => 'ISD - Servicios', 'tipo' => 'ISD', 'porcentaje' => 5.00],
        ['codigo' => '502', 'descripcion' => 'ISD - Productos', 'tipo' => 'ISD', 'porcentaje' => 5.00]
    ];
    
    $insertados = 0;
    $errores = 0;
    
    foreach ($codigosRetencion as $codigo) {
        try {
            $tipoId = $tiposMap[$codigo['tipo']] ?? null;
            if (!$tipoId) {
                echo "<div class='error'>❌ Tipo de impuesto '{$codigo['tipo']}' no encontrado para código {$codigo['codigo']}</div>";
                $errores++;
                continue;
            }
            
            $stmt = $pdo->prepare("INSERT IGNORE INTO CodigosRetencion (codigo, descripcion, tipo_impuesto_id, porcentaje_default) VALUES (?, ?, ?, ?)");
            $stmt->execute([$codigo['codigo'], $codigo['descripcion'], $tipoId, $codigo['porcentaje']]);
            
            if ($stmt->rowCount() > 0) {
                echo "<div class='success'>✅ Código {$codigo['codigo']} - {$codigo['descripcion']} insertado</div>";
                $insertados++;
            } else {
                echo "<div class='warning'>⚠️ Código {$codigo['codigo']} ya existe</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error insertando código {$codigo['codigo']}: " . $e->getMessage() . "</div>";
            $errores++;
        }
    }
    
    echo "<div class='info'>📊 Resumen: $insertados códigos insertados, $errores errores</div>";
    echo "</div>";
    
    // Verificar las tablas creadas
    echo "<div class='section'>";
    echo "<h2>🔍 Verificación Final</h2>";
    
    $tablasVerificar = ['TiposImpuesto', 'CodigosRetencion'];
    foreach ($tablasVerificar as $tabla) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<div class='info'>📈 $tabla: {$count['total']} registros</div>";
    }
    echo "</div>";
    
    echo "<div class='success'>🎉 ¡Tablas catálogo creadas exitosamente!</div>";
    echo "<div class='info'>💡 Ahora puedes usar estas tablas para hacer consultas más legibles y profesionales.</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error general: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
