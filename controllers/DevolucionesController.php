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
}
