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
            // 1. Insertar Encabezado de Devolución
            // Registramos quién autorizó y de qué venta proviene
            $stmt = $this->db->prepare("INSERT INTO devoluciones_venta (id_venta_origen, id_usuario_autoriza, fecha_devolucion, motivo, total_reembolsado) VALUES (?, ?, NOW(), ?, ?)");
            $stmt->bind_param("iisd", $datos['id_venta'], $datos['id_usuario'], $datos['motivo'], $datos['total_reembolsado']);
            $stmt->execute();
            $id_devolucion = $stmt->insert_id;
            $stmt->close();

            // 2. Insertar Detalles y Restaurar Stock
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
