<?php
// fix_fonts.php - Script para copiar las fuentes a la ubicación correcta

echo "<h1>Arreglando Fuentes de FPDF</h1>";

// Verificar si existe la carpeta de destino
$dest_dir = 'lib/fpdf/font';
if (!is_dir($dest_dir)) {
    echo "<p>Creando directorio: $dest_dir</p>";
    mkdir($dest_dir, 0755, true);
}

// Verificar si existe la carpeta de origen
$source_dir = 'lib/font';
if (!is_dir($source_dir)) {
    echo "<p style='color: red;'>❌ No se encontró el directorio de fuentes: $source_dir</p>";
    exit;
}

// Copiar archivos de fuentes
$font_files = [
    'courier.php',
    'courierb.php', 
    'courierbi.php',
    'courieri.php',
    'helvetica.php',
    'helveticab.php',
    'helveticabi.php',
    'helveticai.php',
    'symbol.php',
    'times.php',
    'timesb.php',
    'timesbi.php',
    'timesi.php',
    'zapfdingbats.php'
];

$copied = 0;
foreach ($font_files as $file) {
    $source = $source_dir . '/' . $file;
    $dest = $dest_dir . '/' . $file;
    
    if (file_exists($source)) {
        if (copy($source, $dest)) {
            echo "<p style='color: green;'>✅ Copiado: $file</p>";
            $copied++;
        } else {
            echo "<p style='color: red;'>❌ Error copiando: $file</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No encontrado: $file</p>";
    }
}

echo "<h2>Resumen</h2>";
echo "<p>Archivos copiados: <strong>$copied</strong> de " . count($font_files) . "</p>";

if ($copied == count($font_files)) {
    echo "<p style='color: green; font-weight: bold;'>✅ Todas las fuentes han sido copiadas correctamente</p>";
    echo "<p>Ahora puedes probar la generación del PDF nuevamente.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Algunas fuentes no se pudieron copiar</p>";
}
?>
