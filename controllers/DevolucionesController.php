<?php
// controllers/DevolucionesController.php
if (!defined('INDEX_KEY')) die('Acceso denegado');

// Clase DevolucionesController: Maneja la lógica de devoluciones
class DevolucionesController {
    
    // Acción index: Lista las devoluciones realizadas
    public function index() {
        global $db_connection;
        $modelo = new Devolucion($db_connection);
        
        $fechaInicio = $_GET['f_ini'] ?? date('Y-m-01');
        $fechaFin = $_GET['f_fin'] ?? date('Y-m-d');
        
        $devoluciones = $modelo->listar($fechaInicio, $fechaFin);
        
        // require_once 'views/devoluciones/index.php';
        // echo json_encode($devoluciones);
    }

    // Acción guardar: Procesa una nueva devolución
    public function guardar() {
        global $db_connection;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;

            $modelo = new Devolucion($db_connection);
            
            // Requiere sesión (normalmente Admin o Supervisor)
            if (!isset($_SESSION['user'])) {
                http_response_code(403);
                die("No autorizado");
            }

            $datos = [
                'id_venta' => $input['id_venta'], // ID de la venta original
                'id_usuario' => $_SESSION['user']['id'], // Usuario que autoriza
                'motivo' => $input['motivo'],
                'total_reembolsado' => $input['total'],
                'detalles' => $input['detalles'] // Array de {id_disco, cantidad} a devolver
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
