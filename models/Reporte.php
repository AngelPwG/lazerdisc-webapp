<?php
// models/Reporte.php

if (!defined('INDEX_KEY')) { die('Acceso denegado'); }

// Clase Reporte: Agrupa consultas complejas para generar reportes del sistema
class Reporte {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // 3.1 Inventario Actual: Muestra stock y estado de productos
    public function inventarioActual($busqueda = '', $soloActivos = false) {
        // Consulta con LEFT JOIN para traer stock aunque sea 0 o nulo
        $sql = "SELECT d.codigo_barras, d.titulo as nombre, d.precio_venta, 
                       IFNULL(e.cantidad_actual, 0) as existencia, -- Si es null, muestra 0
                       CASE WHEN d.activo = 1 THEN 'ACTIVO' ELSE 'INACTIVO' END as estado
                FROM discos d
                LEFT JOIN existencias e ON d.id_disco = e.id_disco
                WHERE 1=1";
        
        $params = [];
        $types = "";

        // Filtro opcional: Solo productos activos
        if ($soloActivos) {
            $sql .= " AND d.activo = 1";
        }

        // Filtro de búsqueda por nombre o código
        if ($busqueda) {
            $sql .= " AND (d.titulo LIKE ? OR d.codigo_barras LIKE ?)";
            $term = "%$busqueda%";
            $params[] = $term;
            $params[] = $term;
            $types .= "ss";
        }
        
        $sql .= " ORDER BY d.titulo ASC";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 3.2 Ventas por Rango (Encabezados): Resumen de ventas
    public function ventasPorRango($inicio, $fin) {
        $sql = "SELECT v.folio_venta as folio, v.fecha_venta as fecha, u.username as cajero,
                       v.total_venta as total
                FROM ventas v
                JOIN usuarios u ON v.id_usuario_cajero = u.id_usuario
                WHERE v.fecha_venta BETWEEN ? AND ?";
        
        $params = [$inicio . " 00:00:00", $fin . " 23:59:59"];
        $types = "ss";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Procesamiento posterior: Calcular desglose de IVA
        foreach ($res as &$row) {
            $row['subtotal'] = round($row['total'] / 1.16, 2);
            $row['iva'] = round($row['total'] - $row['subtotal'], 2);
        }
        return $res;
    }

    // 3.3 Detalle de Ventas por Rango: Línea por línea lo que se vendió
    public function detalleVentas($inicio, $fin) {
        $sql = "SELECT v.fecha_venta as fecha, v.folio_venta as folio, 
                       d.codigo_barras as codigo, d.titulo as nombre,
                       vd.cantidad, vd.precio_unitario, vd.subtotal as importe
                FROM ventas_det vd
                JOIN ventas v ON vd.id_venta = v.id_venta
                JOIN discos d ON vd.id_disco = d.id_disco
                WHERE v.fecha_venta BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $ini = $inicio . " 00:00:00";
        $fi = $fin . " 23:59:59";
        $stmt->bind_param("ss", $ini, $fi);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 3.4 Compras por Rango: Resumen de adquisiciones
    public function comprasPorRango($inicio, $fin) {
        $sql = "SELECT c.id_compra as folio, c.fecha_compra as fecha, 
                       p.nombre as proveedor, c.total_compra as total
                FROM compras c
                JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                WHERE c.fecha_compra BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $ini = $inicio . " 00:00:00";
        $fi = $fin . " 23:59:59";
        $stmt->bind_param("ss", $ini, $fi);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 3.5 Devoluciones por Rango: Qué se devolvió y por qué
    public function devolucionesPorRango($inicio, $fin) {
        // Consulta compleja uniendo devoluciones, detalles, venta original y discos
        $sql = "SELECT d.fecha_devolucion as fecha, v.folio_venta, 
                       di.codigo_barras as codigo, di.titulo as nombre,
                       dd.cantidad_devuelta as cantidad,
                       d.motivo,
                       -- Subquery o cálculo para estimar el importe ajustado (precio original * cantidad devuelta)
                       (dd.cantidad_devuelta * (SELECT precio_unitario FROM ventas_det WHERE id_venta = v.id_venta AND id_disco = di.id_disco LIMIT 1)) as importe_ajustado
                FROM devoluciones_venta d
                JOIN devoluciones_det dd ON d.id_devolucion = dd.id_devolucion
                JOIN ventas v ON d.id_venta_origen = v.id_venta
                JOIN discos di ON dd.id_disco = di.id_disco
                WHERE d.fecha_devolucion BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $ini = $inicio . " 00:00:00";
        $fi = $fin . " 23:59:59";
        $stmt->bind_param("ss", $ini, $fi);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
