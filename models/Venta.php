<?php
// models/Venta.php

// Seguridad: Evitar acceso directo
if (!defined('INDEX_KEY')) {
    die('Acceso denegado');
}

// Clase Venta: Maneja la lógica de ventas, validación de stock y tickets
class Venta
{
    private $db;

    public function __construct($conexion)
    {
        $this->db = $conexion;
    }

    // Método para registrar una venta. Es crítico usar transacciones aquí.
    public function crear($datos)
    {
        // $datos contiene: 'id_usuario', 'total', y 'detalles' (lista de items a vender)

        $this->db->begin_transaction(); // Inicia transacción
        try {
            // 1. Validar Stock ANTES de insertar nada
            // Es importante bloquear las filas (FOR UPDATE) o verificar stock actual para evitar condiciones de carrera
            foreach ($datos['detalles'] as $det) {
                // Seleccionamos el stock actual del producto, bloqueando la fila para lectura segura
                $stmtStock = $this->db->prepare("SELECT cantidad_actual FROM existencias WHERE id_disco = ? FOR UPDATE");
                $stmtStock->bind_param("i", $det['id_disco']);
                $stmtStock->execute();
                $res = $stmtStock->get_result()->fetch_assoc();
                $stmtStock->close();

                // Si no hay registro de existencia o la cantidad es menor a la solicitada, lanzamos error
                if (!$res || $res['cantidad_actual'] < $det['cantidad']) {
                    throw new Exception("Stock insuficiente para el producto 
                    " . $det['titulo'] . " con ID: " . $det['id_disco']);
                }
            }

            // 2. Insertar Encabezado de Venta
            // Generamos un folio único basado en fecha y aleatorio
            $folio = "V-" . date('Ymd-His') . "-" . rand(100, 999);
            $stmt = $this->db->prepare("INSERT INTO ventas (folio_venta, id_usuario_cajero, total_venta, fecha_venta, estado) VALUES (?, ?, ?, NOW(), 'completada')");
            $stmt->bind_param("sid", $folio, $datos['id_usuario'], $datos['total']);
            $stmt->execute();
            $id_venta = $stmt->insert_id; // ID autogenerado
            $stmt->close();

            // 3. Insertar Detalles y Descontar Stock
            // Preparamos queries para insertar detalle y actualizar inventario
            $stmtDet = $this->db->prepare("INSERT INTO ventas_det (id_venta, id_disco, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            // Restamos la cantidad vendida del stock actual
            $stmtUpdate = $this->db->prepare("UPDATE existencias SET cantidad_actual = cantidad_actual - ?, ultima_actualizacion = NOW() WHERE id_disco = ?");

            foreach ($datos['detalles'] as $det) {
                $subtotal = $det['cantidad'] * $det['precio'];

                // Insertamos el detalle de la venta
                $stmtDet->bind_param("iiidd", $id_venta, $det['id_disco'], $det['cantidad'], $det['precio'], $subtotal);
                $stmtDet->execute();

                // Actualizamos (restamos) el stock
                $stmtUpdate->bind_param("ii", $det['cantidad'], $det['id_disco']);
                $stmtUpdate->execute();
            }

            // Cerramos statements
            $stmtDet->close();
            $stmtUpdate->close();

            // Confirmamos transacción
            $this->db->commit();
            // Retornamos ID y Folio para mostrar en el ticket
            return ['id_venta' => $id_venta, 'folio' => $folio];

        } catch (Exception $e) {
            // Si algo falla (ej. sin stock), revertimos todo
            $this->db->rollback();
            throw $e;
        }
    }

    // Método para obtener todos los datos necesarios para imprimir el ticket
    public function obtenerParaTicket($id_venta)
    {
        // Obtenemos encabezado de venta y nombre del cajero
        $sql = "SELECT v.id_venta, v.folio_venta, v.fecha_venta, v.total_venta, 
                       u.username as cajero
                FROM ventas v
                JOIN usuarios u ON v.id_usuario_cajero = u.id_usuario
                WHERE v.id_venta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_venta);
        $stmt->execute();
        $venta = $stmt->get_result()->fetch_assoc();

        if (!$venta)
            return null; // Si no existe la venta, retornamos null

        // Obtenemos los detalles (productos) de esa venta
        $sqlDet = "SELECT vd.cantidad, vd.precio_unitario, vd.subtotal, 
                          d.titulo as descripcion
                   FROM ventas_det vd
                   JOIN discos d ON vd.id_disco = d.id_disco
                   WHERE vd.id_venta = ?";
        $stmtDet = $this->db->prepare($sqlDet);
        $stmtDet->bind_param("i", $id_venta);
        $stmtDet->execute();
        // Agregamos las líneas al arreglo de venta
        $venta['lineas'] = $stmtDet->get_result()->fetch_all(MYSQLI_ASSOC);

        return $venta;
    }
}
