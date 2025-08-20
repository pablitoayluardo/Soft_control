-- Renombrar la tabla de pagos existente como respaldo
ALTER TABLE pagos RENAME TO pagos_legacy;

-- Crear la nueva tabla de pagos con la estructura correcta para facturación electrónica
CREATE TABLE pagos (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_info_factura INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    forma_pago VARCHAR(50) NOT NULL,
    nombre_banco VARCHAR(100),
    numero_documento VARCHAR(100),
    fecha_pago DATE NOT NULL,
    descripcion TEXT,
    usuario_registro VARCHAR(100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_info_factura) REFERENCES info_factura(id_info_factura) ON DELETE CASCADE
);
