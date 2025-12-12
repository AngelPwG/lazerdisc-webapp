<?php
// controllers/DevolucionesController.php
if (!defined('INDEX_KEY'))
    die('Acceso denegado');

require_once 'models/Devolucion.php';

// Clase DevolucionesController: Maneja la lógica de devoluciones
class DevolucionesController
{

    // Acción index: Lista las devoluciones realizadas
    public function index()
    {
        // Si es operador, redirigir directamente a crear devolución
        if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'operador') {
            header("Location: index.php?c=Devoluciones&a=crear");
            exit;
        }

        global $db_connection;
        $modelo = new Devolucion($db_connection);

        $fechaInicio = $_GET['f_ini'] ?? date('Y-11-01');
        $fechaFin = $_GET['f_fin'] ?? date('Y-m-d');

        $devoluciones = $modelo->listar($fechaInicio, $fechaFin);

        require_once 'views/devoluciones/index.php';
    }

    // Acción crear: Muestra la vista para registrar nueva devolución
    public function crear()
    {
        require_once 'views/devoluciones/crear.php';
    }

    // Acción para obtener detalles de una venta por folio (API endpoint para AJAX)
    public function obtenerDetalleVenta()
    {
        global $db_connection;
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $folio = $_GET['folio'] ?? '';
            
            if (empty($folio)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Folio requerido']);
                return;
            }
            
            $modelo = new Devolucion($db_connection);
            $resultado = $modelo->obtenerDetallesPorFolio($folio);
            
            if (!$resultado) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Folio no encontrado']);
                return;
            }
            
            echo json_encode([
                'status' => 'success',
                'venta' => $resultado['venta'],
                'detalles' => $resultado['detalles']
            ]);
        }
    }

    // Acción guardar: Procesa una nueva devolución
    public function guardar()
    {
        global $db_connection;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input)
                $input = $_POST;

            $modelo = new Devolucion($db_connection);

            // Verificar sesión (compatible con ambas claves por si acaso)
            $id_usuario = $_SESSION['usuario']['id_usuario'] ?? ($_SESSION['user']['id'] ?? null);
            if (!$id_usuario) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
                return;
            }

            // 1. Obtener ID Venta desde Folio
            $folio = $input['folio_venta'] ?? '';
            $stmt = $db_connection->prepare("SELECT id_venta FROM ventas WHERE folio_venta = ?");
            $stmt->bind_param("s", $folio);
            $stmt->execute();
            $resVenta = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$resVenta) {
                echo json_encode(['status' => 'error', 'message' => 'Folio de venta no encontrado']);
                return;
            }
            $id_venta = $resVenta['id_venta'];

            // 2. Procesar Detalles - ahora esperamos id_disco directamente
            $detallesProcesados = [];
            foreach ($input['detalles'] as $item) {
                $id_disco = $item['id_disco'] ?? 0;
                $cantidad = $item['cantidad'] ?? 0;

                if ($id_disco && $cantidad > 0) {
                    $detallesProcesados[] = [
                        'id_disco' => $id_disco,
                        'cantidad' => $cantidad
                    ];
                }
            }

            if (empty($detallesProcesados)) {
                echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar al menos un producto para devolver']);
                return;
            }

            // Validar motivo
            $motivo = trim($input['motivo'] ?? '');
            if (empty($motivo)) {
                echo json_encode(['status' => 'error', 'message' => 'El motivo de la devolución es obligatorio']);
                return;
            }

            $datos = [
                'id_venta' => $id_venta,
                'id_usuario' => $id_usuario,
                'motivo' => $motivo,
                'total' => $input['total'] ?? 0,
                'detalles' => $detallesProcesados
            ];

            try {
                $id = $modelo->crear($datos);
                echo json_encode(['status' => 'success', 'id_devolucion' => $id]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    // Acción ticket: Genera el ticket de devolución para imprimir
    public function ticket()
    {
        global $db_connection;

        $id_devolucion = $_GET['id_devolucion'] ?? 0;

        if (!$id_devolucion) {
            die('ID de devolución no especificado');
        }

        // Obtener datos de la devolución
        $stmt = $db_connection->prepare("
            SELECT dv.id_devolucion, dv.fecha_devolucion, dv.total_reembolsado, dv.motivo,
                   v.folio_venta, v.fecha_venta,
                   u.username as cajero
            FROM devoluciones_venta dv
            JOIN ventas v ON dv.id_venta_origen = v.id_venta
            JOIN usuarios u ON dv.id_usuario_autoriza = u.id_usuario
            WHERE dv.id_devolucion = ?
        ");
        $stmt->bind_param("i", $id_devolucion);
        $stmt->execute();
        $devolucion = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$devolucion) {
            die('Devolución no encontrada');
        }

        // Obtener detalles de productos devueltos
        $stmt = $db_connection->prepare("
            SELECT dd.cantidad_devuelta as cantidad, 
                   d.titulo as descripcion,
                   vd.precio_unitario,
                   (dd.cantidad_devuelta * vd.precio_unitario) as subtotal
            FROM devoluciones_det dd
            JOIN discos d ON dd.id_disco = d.id_disco
            JOIN ventas_det vd ON vd.id_disco = dd.id_disco 
                AND vd.id_venta = (SELECT id_venta_origen FROM devoluciones_venta WHERE id_devolucion = ?)
            WHERE dd.id_devolucion = ?
        ");
        $stmt->bind_param("ii", $id_devolucion, $id_devolucion);
        $stmt->execute();
        $lineas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Preparar variables para la vista
        $folio_venta = $devolucion['folio_venta'];
        $fecha_devolucion = $devolucion['fecha_devolucion'];
        $cajero = $devolucion['cajero'];
        $motivo = $devolucion['motivo'];
        
        // Calcular totales
        $subtotal_calc = 0;
        foreach ($lineas as $item) {
            $subtotal_calc += $item['subtotal'];
        }
        
        $iva_calc = $subtotal_calc * 0.16;
        $total_calc = $subtotal_calc + $iva_calc;

        // Cargar vista del ticket
        require_once 'views/devoluciones/ticket_devolucion.php';
    }
}
