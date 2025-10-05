CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    correo VARCHAR(100) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apaterno VARCHAR(50) NOT NULL,
    amaterno VARCHAR(50) NOT NULL,
    fecha_registro DATETIME NOT NULL,
    activo TINYINT(1) DEFAULT 1
);

-- Crear índice único para el correo
CREATE UNIQUE INDEX idx_correo ON usuarios(correo);