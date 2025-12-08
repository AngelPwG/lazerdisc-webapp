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

            // 2. Procesar Detalles (Buscar ID Disco por Código de Barras)
            $detallesProcesados = [];
            foreach ($input['detalles'] as $item) {
                $codigo = $item['codigo_barras'] ?? '';

                $stmtD = $db_connection->prepare("SELECT id_disco, titulo FROM discos WHERE codigo_barras = ?");
                $stmtD->bind_param("s", $codigo);
                $stmtD->execute();
                $resDisco = $stmtD->get_result()->fetch_assoc();
                $stmtD->close();

                if (!$resDisco) {
                    echo json_encode(['status' => 'error', 'message' => "Producto con código '$codigo' no encontrado"]);
                    return;
                }

                $detallesProcesados[] = [
                    'id_disco' => $resDisco['id_disco'],
                    'cantidad' => $item['cantidad'],
                    'titulo' => $resDisco['titulo'] // Opcional, para debug error msg si falla stock
                ];
            }

            $datos = [
                'id_venta' => $id_venta,
                'id_usuario' => $id_usuario,
                'motivo' => $input['motivo'],
                'total_reembolsado' => $input['total'],
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
