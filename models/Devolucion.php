<?php
// models/Devolucion.php

if (!defined('INDEX_KEY')) { die('Acceso denegado'); }

// Clase Devolucion: Maneja el retorno de mercancía y ajuste de inventario
class Devolucion {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Método para crear una devolución
    public function crear($datos) {
        // $datos: 'id_venta', 'id_usuario', 'motivo', 'total_reembolsado', 'detalles'
        
        $this->db->begin_transaction();
        try {
            // 1. Validar cantidades (Nunca mayores a lo vendido - devoluciones previas)
            foreach ($datos['detalles'] as $det) {
                $id_disco = $det['id_disco'];
                $cant_devolver = $det['cantidad'];

                // a) Obtener cantidad vendida originalmente
                $stmtV = $this->db->prepare("SELECT cantidad FROM ventas_det WHERE id_venta = ? AND id_disco = ?");
                $stmtV->bind_param("ii", $datos['id_venta'], $id_disco);
                $stmtV->execute();
                $resV = $stmtV->get_result()->fetch_assoc();
                $stmtV->close();

                if (!$resV) {
                    throw new Exception("El producto ID $id_disco no pertenece a esta venta.");
                }
                $cant_vendida = $resV['cantidad'];

                // b) Obtener cantidad ya devuelta anteriormente
                // (Sumamos devoluciones previas asociadas a esta venta y este producto)
                $sqlDevs = "SELECT IFNULL(SUM(dd.cantidad_devuelta), 0) as total_devuelto 
                            FROM devoluciones_det dd
                            JOIN devoluciones_venta dv ON dd.id_devolucion = dv.id_devolucion
                            WHERE dv.id_venta_origen = ? AND dd.id_disco = ?";
                $stmtD = $this->db->prepare($sqlDevs);
                $stmtD->bind_param("ii", $datos['id_venta'], $id_disco);
                $stmtD->execute();
                $resD = $stmtD->get_result()->fetch_assoc();
                $stmtD->close();
                
                $cant_previa = $resD['total_devuelto'];

                if (($cant_previa + $cant_devolver) > $cant_vendida) {
                    throw new Exception("La cantidad a devolver del producto ID $id_disco excede lo vendido (" . ($cant_vendida - $cant_previa) . " disponibles para devolución).");
                }
            }

            // 2. Insertar Encabezado de Devolución
            $stmt = $this->db->prepare("INSERT INTO devoluciones_venta (id_venta_origen, id_usuario_autoriza, fecha_devolucion, total_reembolsado, motivo) VALUES (?, ?, NOW(), ?, ?)");
            $stmt->bind_param("iids", $datos['id_venta'], $datos['id_usuario'], $datos['total'], $datos['motivo']);
            $stmt->execute();
            $id_devolucion = $this->db->insert_id;
            $stmt->close();

            // 3. Insertar Detalles y Restaurar Stock
            // Preparamos query para registrar qué items se devolvieron
            $stmtDet = $this->db->prepare("INSERT INTO devoluciones_det (id_devolucion, id_disco, cantidad_devuelta) VALUES (?, ?, ?)");
            // Preparamos query para SUMAR al stock (devolución = entrada de almacén)
            $stmtUpdate = $this->db->prepare("UPDATE existencias SET cantidad_actual = cantidad_actual + ?, ultima_actualizacion = NOW() WHERE id_disco = ?");

            foreach ($datos['detalles'] as $det) {
                // Insertar detalle
                $stmtDet->bind_param("iii", $id_devolucion, $det['id_disco'], $det['cantidad']);
                $stmtDet->execute();

                // Actualizar stock (Sumar cantidad devuelta)
                $stmtUpdate->bind_param("ii", $det['cantidad'], $det['id_disco']);
                $stmtUpdate->execute();
            }
            
            $stmtDet->close();
            $stmtUpdate->close();

            $this->db->commit();
            return $id_devolucion;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Obtener detalles de una venta por folio
     * @param string $folio - Folio de la venta
     * @return array|null - Array con datos de venta y detalles, o null si no existe
     */
    public function obtenerDetallesPorFolio($folio) {
        // Buscar venta por folio
        $stmt = $this->db->prepare("SELECT id_venta, folio_venta, total_venta, fecha_venta FROM ventas WHERE folio_venta = ?");
        $stmt->bind_param("s", $folio);
        $stmt->execute();
        $venta = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$venta) {
            return null;
        }
        
        // Obtener detalles de la venta
        $stmt = $this->db->prepare("
            SELECT vd.id_disco, d.titulo, d.codigo_barras, vd.cantidad, vd.precio_unitario, vd.subtotal
            FROM ventas_det vd
            JOIN discos d ON vd.id_disco = d.id_disco
            WHERE vd.id_venta = ?
        ");
        $stmt->bind_param("i", $venta['id_venta']);
        $stmt->execute();
        $detalles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return [
            'venta' => $venta,
            'detalles' => $detalles
        ];
    }

    // Método para listar devoluciones (Reportes)
    public function listar($fechaInicio = null, $fechaFin = null) {
        // Obtenemos datos de la devolución, folio de venta original y usuario que autorizó
        $sql = "SELECT d.id_devolucion, d.fecha_devolucion, d.total_reembolsado, d.motivo,
                       v.folio_venta, u.username as autorizo
                FROM devoluciones_venta d
                JOIN ventas v ON d.id_venta_origen = v.id_venta
                JOIN usuarios u ON d.id_usuario_autoriza = u.id_usuario
                WHERE 1=1";
        
        $params = [];
        $types = "";

        // Filtro por fecha
        if ($fechaInicio && $fechaFin) {
            $sql .= " AND d.fecha_devolucion BETWEEN ? AND ?";
            $params[] = $fechaInicio . " 00:00:00";
            $params[] = $fechaFin . " 23:59:59";
            $types .= "ss";
        }

        $sql .= " ORDER BY d.fecha_devolucion DESC";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
