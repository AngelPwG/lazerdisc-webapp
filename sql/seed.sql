-- =======================================================
-- PROYECTO FINAL CORTE III: TIENDA DE DISCOS "LAZER DISC"
-- Autor: Mercedes Del Carmen Ramos Vega
-- =======================================================

USE lazerdisc_BD;

-- 1. USUARIOS (Contraseñas: 12345)
-- =======================================================
INSERT INTO usuarios (username, password, nombre_completo, rol) VALUES 
('admin', '12345', 'Gerente General', 'admin'),
('cajero1', '12345', 'Beto el Cajero', 'operador');

-- 2. CATÁLOGOS BASE (Artistas, Disqueras, Géneros)
-- =======================================================
INSERT INTO artistas (nombre_artista, nacionalidad) VALUES 
('Michael Jackson', 'EEUU'),        -- ID 1
('Pink Floyd', 'Reino Unido'),      -- ID 2
('Daft Punk', 'Francia');           -- ID 3

INSERT INTO disqueras (nombre_disquera) VALUES 
('Sony Music'),
('Columbia Records'),
('EMI');

INSERT INTO generos (nombre_genero) VALUES 
('Pop'),            -- ID 1
('Rock'),           -- ID 2
('R&B'),            -- ID 3
('Electrónica'),    -- ID 4
('Progresivo');     -- ID 5

-- 3. PROVEEDORES
-- =======================================================
INSERT INTO proveedores (nombre, telefono, contacto) VALUES 
('Sony Music México', '5512345678', 'Lic. Juan Ventas'),
('Discos del Norte', '6699876543', 'Ana Distribuidora');

-- 4. DISCOS (INVENTARIO PRINCIPAL)
-- =======================================================
INSERT INTO discos (titulo, id_artista, id_disquera, tipo, codigo_barras, anio_lanzamiento, precio_venta, costo_promedio) VALUES 
-- Disco 1: Thriller
('Thriller', 1, 1, 'Vinilo', '75010001', 1982, 450.00, 300.00),
-- Disco 2: Dark Side of the Moon
('The Dark Side of the Moon', 2, 2, 'CD', '75010002', 1973, 250.00, 150.00),
-- Disco 3: Random Access Memories
('Random Access Memories', 3, 2, 'Vinilo', '75010003', 2013, 600.00, 400.00);

-- 4.1 IMÁGENES DE DISCOS (NUEVA TABLA)
-- =======================================================
-- Insertamos texto dummy porque estamos en SQL puro. 
-- En la app real, aquí se guardarían los bytes de la foto.
INSERT INTO imagenes_discos (id_disco, contenido_imagen) VALUES 
(1, 'bytes_dummy_thriller'),
(2, 'bytes_dummy_pinkfloyd'),
(3, 'bytes_dummy_daftpunk');

-- 5. RELACIÓN DISCOS - GÉNEROS
-- =======================================================
INSERT INTO discos_generos (id_disco, id_genero) VALUES (1, 1), (1, 3); -- Thriller (Pop, R&B)
INSERT INTO discos_generos (id_disco, id_genero) VALUES (2, 2), (2, 5); -- Pink Floyd (Rock, Progresivo)
INSERT INTO discos_generos (id_disco, id_genero) VALUES (3, 4), (3, 1); -- Daft Punk (Electrónica, Pop)

-- 6. CANCIONES
-- =======================================================
INSERT INTO canciones (id_disco, numero_pista, titulo_cancion, duracion) VALUES
-- Thriller
(1, 1, 'Wanna Be Startin Somethin', '00:06:02'),
(1, 4, 'Thriller', '00:05:57'),
-- Pink Floyd
(2, 1, 'Speak to Me', '00:01:13'),
(2, 2, 'Breathe', '00:02:43'),
-- Daft Punk
(3, 1, 'Give Life Back to Music', '00:04:34'),
(3, 8, 'Get Lucky', '00:06:09');

-- 7. EXISTENCIAS
-- =======================================================
INSERT INTO existencias (id_disco, cantidad_actual) VALUES 
(1, 10), 
(2, 5),  
(3, 8);  

-- 8. HISTORIAL DE PRUEBA (Compras y Ventas)
-- =======================================================

-- Compra (Entrada)
INSERT INTO compras (id_proveedor, id_usuario, total_compra, fecha_compra) VALUES 
(1, 1, 3000.00, '2025-11-01 10:00:00');

INSERT INTO compras_det (id_compra, id_disco, cantidad, costo_unitario, subtotal) VALUES 
(1, 1, 10, 300.00, 3000.00);

-- Venta (Salida)
INSERT INTO ventas (folio_venta, id_usuario_cajero, total_venta, fecha_venta) VALUES 
('V-0001', 2, 450.00, '2025-11-20 15:30:00');

INSERT INTO ventas_det (id_venta, id_disco, cantidad, precio_unitario, subtotal) VALUES 
(1, 1, 1, 450.00, 450.00);

-- 9. HISTORIAL DE DEVOLUCIONES
-- =======================================================
-- Devolvemos el disco de la Venta 1 (Thriller)
-- Lo autoriza el Admin (ID 1)

INSERT INTO devoluciones_venta (id_venta_origen, id_usuario_autoriza, fecha_devolucion, motivo, total_reembolsado) VALUES
(1, 1, '2025-11-22 10:00:00', 'Disco rayado / Defecto de fábrica', 450.00);

-- Detalle: Se devuelve 1 unidad del Disco 1
INSERT INTO devoluciones_det (id_devolucion, id_disco, cantidad_devuelta) VALUES
(1, 1, 1);