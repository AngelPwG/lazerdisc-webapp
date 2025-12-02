-- =======================================================
-- PROYECTO FINAL CORTE III: TIENDA DE DISCOS "LAZER DISC"
-- Autor: Mercedes Del Carmen Ramos Vega
-- =======================================================

DROP DATABASE IF EXISTS lazerdisc_BD;
CREATE DATABASE lazerdisc_BD CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lazerdisc_BD;

-- =======================================================
-- 1. USUARIOS Y ACCESO
-- =======================================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL, -- Contraseña visible
    nombre_completo VARCHAR(100),
    rol ENUM('admin', 'operador') NOT NULL,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- =======================================================
-- 2. CATÁLOGOS BASE (Artistas, Disqueras, Géneros)
-- =======================================================

CREATE TABLE artistas (
    id_artista INT AUTO_INCREMENT PRIMARY KEY,
    nombre_artista VARCHAR(100) NOT NULL,
    nacionalidad VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE disqueras (
    id_disquera INT AUTO_INCREMENT PRIMARY KEY,
    nombre_disquera VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE generos (
    id_genero INT AUTO_INCREMENT PRIMARY KEY,
    nombre_genero VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- =======================================================
-- 3. TABLA PRINCIPAL: DISCOS (Inventario)
-- =======================================================
CREATE TABLE discos (
    id_disco INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    
    -- Relaciones con catálogos
    id_artista INT NOT NULL,
    id_disquera INT,
    
    -- Detalles técnicos
    tipo ENUM('CD', 'Vinilo') NOT NULL,
    codigo_barras VARCHAR(50) UNIQUE,
    anio_lanzamiento YEAR,
    control_parental TINYINT(1) DEFAULT 0,
    
    -- Datos financieros
    precio_venta DECIMAL(10, 2) NOT NULL,
    costo_promedio DECIMAL(10, 2) DEFAULT 0.00, 
    
    activo TINYINT(1) DEFAULT 1,
    
    FOREIGN KEY (id_artista) REFERENCES artistas(id_artista),
    FOREIGN KEY (id_disquera) REFERENCES disqueras(id_disquera)
) ENGINE=InnoDB;

-- =======================================================
-- 3.1 IMÁGENES DE DISCOS (Requerimiento BLOB separado)
-- =======================================================
CREATE TABLE imagenes_discos (
    id_imagen INT AUTO_INCREMENT PRIMARY KEY,
    id_disco INT NOT NULL,
    contenido_imagen LONGBLOB NOT NULL, -- Aquí se guardan los bytes de la foto
    tipo_mime VARCHAR(50) DEFAULT 'image/jpeg', -- ej: image/png, image/jpeg
    es_principal TINYINT(1) DEFAULT 1, -- Para marcar cuál es la portada principal
    FOREIGN KEY (id_disco) REFERENCES discos(id_disco) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =======================================================
-- 4. DETALLES DEL DISCO (Canciones y Géneros)
-- =======================================================

-- Tabla de Canciones (Tracklist).
CREATE TABLE canciones (
    id_cancion INT AUTO_INCREMENT PRIMARY KEY,
    id_disco INT NOT NULL,
    numero_pista INT NOT NULL,
    titulo_cancion VARCHAR(150) NOT NULL,
    duracion TIME,
    FOREIGN KEY (id_disco) REFERENCES discos(id_disco) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla Intermedia de Géneros.
CREATE TABLE discos_generos (
    id_disco INT,
    id_genero INT,
    PRIMARY KEY (id_disco, id_genero),
    FOREIGN KEY (id_disco) REFERENCES discos(id_disco) ON DELETE CASCADE,
    FOREIGN KEY (id_genero) REFERENCES generos(id_genero)
) ENGINE=InnoDB;

-- =======================================================
-- 5. CONTROL DE STOCK Y PROVEEDORES
-- =======================================================

CREATE TABLE existencias (
    id_disco INT PRIMARY KEY,
    cantidad_actual INT NOT NULL DEFAULT 0,
    ultima_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_disco) REFERENCES discos(id_disco) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE proveedores (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    contacto VARCHAR(100)
) ENGINE=InnoDB;

-- =======================================================
-- 6. TRANSACCIONES: COMPRAS
-- =======================================================

CREATE TABLE compras (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_compra DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

CREATE TABLE compras_det (
    id_compra INT,
    id_disco INT,
    cantidad INT NOT NULL,
    costo_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL, 
    PRIMARY KEY (id_compra, id_disco),
    FOREIGN KEY (id_compra) REFERENCES compras(id_compra) ON DELETE CASCADE,
    FOREIGN KEY (id_disco) REFERENCES discos(id_disco)
) ENGINE=InnoDB;

-- =======================================================
-- 7. TRANSACCIONES: VENTAS
-- =======================================================

CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    folio_venta VARCHAR(20) UNIQUE,
    id_usuario_cajero INT NOT NULL,
    fecha_venta DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_venta DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    estado ENUM('completada', 'cancelada') DEFAULT 'completada',
    FOREIGN KEY (id_usuario_cajero) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

CREATE TABLE ventas_det (
    id_venta INT,
    id_disco INT,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    PRIMARY KEY (id_venta, id_disco),
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
    FOREIGN KEY (id_disco) REFERENCES discos(id_disco)
) ENGINE=InnoDB;

-- =======================================================
-- 8. DEVOLUCIONES
-- =======================================================

CREATE TABLE devoluciones_venta (
    id_devolucion INT AUTO_INCREMENT PRIMARY KEY,
    id_venta_origen INT NOT NULL,
    id_usuario_autoriza INT NOT NULL,
    fecha_devolucion DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(255),
    total_reembolsado DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (id_venta_origen) REFERENCES ventas(id_venta),
    FOREIGN KEY (id_usuario_autoriza) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

CREATE TABLE devoluciones_det (
    id_devolucion INT,
    id_disco INT,
    cantidad_devuelta INT NOT NULL,
    PRIMARY KEY (id_devolucion, id_disco),
    FOREIGN KEY (id_devolucion) REFERENCES devoluciones_venta(id_devolucion) ON DELETE CASCADE,
    FOREIGN KEY (id_disco) REFERENCES discos(id_disco)
) ENGINE=InnoDB;