<?php
// models/Compra.php

// Verificamos que la constante INDEX_KEY esté definida para evitar acceso directo al archivo
if (!defined('INDEX_KEY')) {
    die('Acceso denegado');
}

// Clase Compra: Maneja la lógica de negocio relacionada con las compras a proveedores
class Compra
{
    // Propiedad para almacenar la conexión a la base de datos
    private $db;

    // Constructor: Recibe la conexión a la BD y la asigna a la propiedad local
    public function __construct($conexion)
    {
        $this->db = $conexion;
    }

    // Método para registrar una nueva compra en la base de datos
    // Este método utiliza una transacción para asegurar la integridad de los datos
    public function crear($datos)
    {
        // $datos es un arreglo que contiene: 'id_proveedor', 'id_usuario', 'total', y 'detalles' (lista de productos)

        // Iniciamos la transacción. Si algo falla, se pueden revertir todos los cambios.
        $this->db->begin_transaction();
        try {
            // 1. Insertar Encabezado de la Compra
            // Preparamos la consulta SQL para insertar en la tabla 'compras'
            $stmt = $this->db->prepare("INSERT INTO compras (id_proveedor, id_usuario, total_compra, fecha_compra) VALUES (?, ?, ?, NOW())");
            // Vinculamos los parámetros: i (entero), i (entero), d (decimal)
            $stmt->bind_param("iid", $datos['id_proveedor'], $datos['id_usuario'], $datos['total']);
            // Ejecutamos la consulta
            $stmt->execute();
            // Obtenemos el ID de la compra recién creada (AUTO_INCREMENT)
            $id_compra = $stmt->insert_id;
            // Cerramos el statement para liberar recursos
            $stmt->close();

            // 2. Insertar Detalles y Actualizar Stock
            // Preparamos la consulta para insertar cada producto en 'compras_det'
            $stmtDet = $this->db->prepare("INSERT INTO compras_det (id_compra, id_disco, cantidad, costo_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");

            // Preparamos la consulta para actualizar el inventario en 'existencias'
            // Usamos ON DUPLICATE KEY UPDATE: Si ya existe el registro del disco, actualizamos la cantidad. Si no, lo insertamos.
            $stmtStock = $this->db->prepare("INSERT INTO existencias (id_disco, cantidad_actual, ultima_actualizacion) 
                                             VALUES (?, ?, NOW()) 
                                             ON DUPLICATE KEY UPDATE cantidad_actual = cantidad_actual + VALUES(cantidad_actual), ultima_actualizacion = NOW()");

            // Recorremos cada detalle (producto) de la compra
            foreach ($datos['detalles'] as $det) {
                // Calculamos el subtotal de la línea (cantidad * costo unitario)
                $subtotal = $det['cantidad'] * $det['costo'];

                // Vinculamos parámetros para insertar el detalle: id_compra, id_disco, cantidad, costo, subtotal
                $stmtDet->bind_param("iiidd", $id_compra, $det['id_disco'], $det['cantidad'], $det['costo'], $subtotal);
                // Ejecutamos la inserción del detalle
                $stmtDet->execute();

                // Vinculamos parámetros para actualizar el stock: id_disco, cantidad
                $stmtStock->bind_param("ii", $det['id_disco'], $det['cantidad']);
                // Ejecutamos la actualización del stock (suma la cantidad comprada)
                $stmtStock->execute();
            }

            // Cerramos los statements auxiliares
            $stmtDet->close();
            $stmtStock->close();

            // Si todo salió bien, confirmamos la transacción (guardamos los cambios permanentemente)
            $this->db->commit();
            // Retornamos el ID de la compra generada
            return $id_compra;

        } catch (Exception $e) {
            // Si ocurre algún error, revertimos todos los cambios hechos en la transacción
            $this->db->rollback();
            // Lanzamos la excepción para que sea manejada por el controlador
            throw $e;
        }
    }

    // Método para listar compras, útil para reportes o historial
    // Permite filtrar por rango de fechas
    public function listar($fechaInicio = null, $fechaFin = null)
    {
        // Consulta base seleccionando datos relevantes y haciendo JOIN con proveedores y usuarios para obtener nombres
        $sql = "SELECT c.id_compra, c.fecha_compra, c.total_compra, 
                       p.nombre as proveedor, u.username as usuario
                FROM compras c
                JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                JOIN usuarios u ON c.id_usuario = u.id_usuario
                WHERE 1=1"; // 1=1 facilita concatenar condiciones AND dinámicamente

        $params = [];
        $types = "";

        // Si se proporcionan fechas, agregamos la condición al SQL
        if ($fechaInicio && $fechaFin) {
            $sql .= " AND c.fecha_compra BETWEEN ? AND ?";
            // Agregamos las horas para cubrir todo el día inicial y final
            $params[] = $fechaInicio . " 00:00:00";
            $params[] = $fechaFin . " 23:59:59";
            $types .= "ss"; // Dos strings
        }

        // Ordenamos por fecha descendente (lo más reciente primero)
        $sql .= " ORDER BY c.fecha_compra DESC";

        // Preparamos la consulta
        $stmt = $this->db->prepare($sql);
        // Si hay parámetros, los vinculamos dinámicamente
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        // Ejecutamos y retornamos todos los resultados como un arreglo asociativo
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Método para obtener los detalles (productos) de una compra específica
    public function obtenerDetalles($id_compra)
    {
        // Consulta haciendo JOIN con discos para obtener nombre y código de barras
        $sql = "SELECT cd.*, d.titulo, d.codigo_barras 
                FROM compras_det cd
                JOIN discos d ON cd.id_disco = d.id_disco
                WHERE cd.id_compra = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_compra);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Método auxiliar para obtener la lista de proveedores
    public function obtenerProveedores()
    {
        $sql = "SELECT id_proveedor, nombre FROM proveedores ORDER BY nombre ASC";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}
