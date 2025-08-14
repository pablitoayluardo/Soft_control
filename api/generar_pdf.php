<?php
// api/generar_pdf.php

require_once '../lib/fpdf/fpdf.php';
require_once '../config.php';

// Obtener la clave de acceso de la URL
$claveAcceso = $_GET['clave_acceso'] ?? null;

if (!$claveAcceso) {
    header("HTTP/1.0 400 Bad Request");
    die('Error: Clave de acceso no proporcionada.');
}

try {
    // Conexión a la base de datos
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Configuración de base de datos incompleta.');
    }
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // 1. Obtener toda la información de la factura
    $sql = "SELECT 
                it.*, 
                f.*,
                f.id_info_factura as info_factura_id
            FROM info_tributaria it
            JOIN info_factura f ON it.id_info_tributaria = f.id_info_tributaria
            WHERE it.clave_acceso = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$claveAcceso]);
    $factura = $stmt->fetch();

    if (!$factura) {
        throw new Exception('Factura no encontrada.');
    }

    // 2. Obtener los detalles de la factura
    $sql_detalles = "SELECT * FROM detalle_factura_sri WHERE id_info_factura = ?";
    $stmt_detalles = $pdo->prepare($sql_detalles);
    $stmt_detalles->execute([$factura['info_factura_id']]);
    $detalles = $stmt_detalles->fetchAll();

    // =====================================================
    // INICIO DE LA GENERACIÓN DEL PDF
    // =====================================================

    class PDF extends FPDF
    {
        // Cabecera de página
        function Header()
        {
            global $factura;

            // Logo (opcional, si tienes uno)
            // $this->Image('logo.png', 10, 6, 30);

            $this->SetFont('Arial', 'B', 12);
            $this->Cell(80);
            $this->Cell(30, 10, utf8_decode($factura['razon_social']), 0, 0, 'C');
            $this->Ln(5);
            $this->SetFont('Arial', '', 10);
            $this->Cell(80);
            $this->Cell(30, 10, utf8_decode($factura['nombre_comercial']), 0, 0, 'C');
            $this->Ln(5);
            $this->Cell(80);
            $this->Cell(30, 10, utf8_decode('RUC: ' . $factura['ruc']), 0, 0, 'C');
            $this->Ln(20);
        }

        // Pie de página
        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }

    // Crear instancia de PDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 10);

    // Bloque de Información de la Factura
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(0, 8, 'RIDE - REPRESENTACION IMPRESA DE DOCUMENTO ELECTRONICO', 1, 1, 'C', true);

    $pdf->Cell(95, 8, 'FACTURA', 1, 0, 'C');
    $pdf->Cell(95, 8, 'No. ' . $factura['estab'] . '-' . $factura['pto_emi'] . '-' . $factura['secuencial'], 1, 1, 'C');

    $pdf->Cell(95, 8, 'Fecha y Hora de Autorizacion:', 1, 0);
    $pdf->Cell(95, 8, $factura['fecha_autorizacion'] ?? date('Y-m-d H:i:s'), 1, 1);
    
    $pdf->Cell(95, 8, 'Ambiente:', 1, 0);
    $pdf->Cell(95, 8, ($factura['ambiente'] == '2' ? 'PRODUCCION' : 'PRUEBAS'), 1, 1);
    
    $pdf->Cell(95, 8, 'Emision:', 1, 0);
    $pdf->Cell(95, 8, ($factura['tipo_emision'] == '1' ? 'NORMAL' : 'INDISPONIBILIDAD'), 1, 1);
    
    $pdf->MultiCell(190, 5, 'Clave de Acceso:' . "\n" . $factura['clave_acceso'], 1, 'L');
    
    $pdf->Ln(5);

    // Bloque de Información del Cliente
    $pdf->Cell(0, 8, 'INFORMACION DEL CLIENTE', 1, 1, 'C', true);
    $pdf->Cell(95, 8, 'Razon Social / Nombres:', 1, 0);
    $pdf->Cell(95, 8, utf8_decode($factura['razon_social_comprador']), 1, 1);
    $pdf->Cell(95, 8, 'Identificacion:', 1, 0);
    $pdf->Cell(95, 8, $factura['identificacion_comprador'], 1, 1);
    $pdf->Cell(95, 8, 'Fecha Emision:', 1, 0);
    $pdf->Cell(95, 8, date('d/m/Y', strtotime($factura['fecha_emision'])), 1, 1);
    $pdf->Cell(95, 8, 'Direccion:', 1, 0);
    $pdf->Cell(95, 8, utf8_decode($factura['direccion_comprador']), 1, 1);
    
    $pdf->Ln(5);

    // Tabla de Detalles de la Factura
    $pdf->Cell(0, 8, 'DETALLES DE LA FACTURA', 1, 1, 'C', true);
    $pdf->SetFillColor(200, 220, 255);
    $pdf->Cell(20, 7, 'Codigo', 1, 0, 'C', true);
    $pdf->Cell(85, 7, 'Descripcion', 1, 0, 'C', true);
    $pdf->Cell(20, 7, 'Cantidad', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'P. Unitario', 1, 0, 'C', true);
    $pdf->Cell(20, 7, 'Descuento', 1, 0, 'C', true);
    $pdf->Cell(20, 7, 'Total', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 9);
    foreach ($detalles as $detalle) {
        $pdf->Cell(20, 6, $detalle['codigo_principal'], 1);
        $pdf->Cell(85, 6, utf8_decode($detalle['descripcion']), 1);
        $pdf->Cell(20, 6, number_format($detalle['cantidad'], 2), 1, 0, 'R');
        $pdf->Cell(25, 6, number_format($detalle['precio_unitario'], 2), 1, 0, 'R');
        $pdf->Cell(20, 6, number_format($detalle['descuento'], 2), 1, 0, 'R');
        $pdf->Cell(20, 6, number_format($detalle['precio_total_sin_impuesto'], 2), 1, 1, 'R');
    }

    $pdf->Ln(5);

    // Bloque de Información Adicional (Estado, Pagos, etc.)
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, 'INFORMACION ADICIONAL', 1, 1, 'C', true);
    $pdf->SetFont('Arial', '', 9);

    $pdf->Cell(40, 6, 'Estado de la Factura:', 1, 0);
    $pdf->Cell(150, 6, $factura['estatus'], 1, 1);

    $pdf->Cell(40, 6, 'Valor Retenido:', 1, 0);
    $pdf->Cell(150, 6, '$' . number_format($factura['retencion'] ?? 0, 2), 1, 1);

    $pdf->Cell(40, 6, 'Valor Pagado:', 1, 0);
    $pdf->Cell(150, 6, '$' . number_format($factura['valor_pagado'] ?? 0, 2), 1, 1);
    
    $pdf->Cell(40, 6, 'Observaciones:', 1, 0);
    $pdf->MultiCell(150, 6, utf8_decode($factura['observacion'] ?? 'Ninguna'), 1, 'L');

    // Totales
    $pdf->Ln(5);
    $pdf->Cell(130);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 6, 'Subtotal Sin Imp.', 1, 0, 'R');
    $pdf->Cell(30, 6, number_format($factura['total_sin_impuestos'], 2), 1, 1, 'R');
    
    $pdf->Cell(130);
    $pdf->Cell(30, 6, 'Descuento', 1, 0, 'R');
    $pdf->Cell(30, 6, number_format($factura['total_descuento'], 2), 1, 1, 'R');

    // Calculamos el total de impuestos al vuelo
    $totalImpuestos = ($factura['importe_total'] - $factura['total_sin_impuestos']) + $factura['total_descuento'];
    $pdf->Cell(130);
    $pdf->Cell(30, 6, 'IVA 15%', 1, 0, 'R'); // Restaurado a petición del usuario
    $pdf->Cell(30, 6, number_format($totalImpuestos, 2), 1, 1, 'R');

    $pdf->Cell(130);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 6, 'VALOR TOTAL', 1, 0, 'R');
    $pdf->Cell(30, 6, number_format($factura['importe_total'], 2), 1, 1, 'R');


    // Enviar el PDF al navegador
    $pdf->Output('I', 'RIDE_' . $factura['clave_acceso'] . '.pdf');


} catch (Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    die('Error generando el PDF: ' . $e->getMessage());
}
