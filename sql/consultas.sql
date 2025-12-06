-- =======================================================
-- PROYECTO FINAL CORTE III: TIENDA DE DISCOS "LAZER DISC"
-- Autor: Mercedes Del Carmen Ramos Vega
-- =======================================================

USE lazerdisc_BD;

-- 1. BÚSQUEDA (Requerimiento: "Búsqueda LIKE")
-- =======================================================
-- Esta consulta busca discos por Título, por Artista o por Código de Barras.

SELECT discos.titulo, artistas.nombre_artista, discos.precio_venta 
FROM discos
JOIN artistas ON discos.id_artista = artistas.id_artista
WHERE discos.titulo LIKE '%Thriller%' 
   OR artistas.nombre_artista LIKE '%Jackson%'
   OR discos.codigo_barras = '75010001';


-- 2. CATÁLOGO CON IMAGEN (Requerimiento: "Miniatura por item")
-- =======================================================
-- Ahora hacemos JOIN con la tabla 'imagenes_discos' para traer la foto.

SELECT discos.id_disco, discos.titulo, discos.precio_venta, imagenes_discos.contenido_imagen 
FROM discos
JOIN imagenes_discos ON discos.id_disco = imagenes_discos.id_disco
WHERE discos.activo = 1 
  AND imagenes_discos.es_principal = 1; -- Solo traemos la portada principal


-- 3. RESUMEN DE EXISTENCIAS (Requerimiento: "Resumen de existencias")
-- =======================================================
-- Muestra cuánto stock tenemos de cada disco.
-- Hacemos JOIN entre la tabla de discos y la tabla de existencias.

SELECT discos.titulo, existencias.cantidad_actual, discos.codigo_barras
FROM discos
JOIN existencias ON discos.id_disco = existencias.id_disco
ORDER BY existencias.cantidad_actual DESC; -- Los que tienen más stock primero


-- 4. REPORTES POR FECHA (Requerimiento: "Reportes por fecha")
-- =======================================================
-- Muestra todas las ventas que se hicieron en un día específico (Corte de caja).
-- Suma el total vendido ese día.

SELECT ventas.folio_venta, ventas.fecha_venta, ventas.total_venta, usuarios.username
FROM ventas
JOIN usuarios ON ventas.id_usuario_cajero = usuarios.id_usuario
WHERE DATE(ventas.fecha_venta) = '2025-11-20'; -- <--- Aquí cambias la fecha