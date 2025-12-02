<?php
// controllers/ComprasController.php
// Verificamos acceso seguro
if (!defined('INDEX_KEY')) die('Acceso denegado');

// Clase ComprasController: Maneja las peticiones HTTP relacionadas con compras
class ComprasController {
    
    // Acción index: Muestra el listado de compras
    public function index() {
        global $db_connection; // Usamos la conexión global
        $modelo = new Compra($db_connection); // Instanciamos el modelo
        
        // Obtenemos filtros de fecha de la URL (GET), o usamos valores por defecto (mes actual / hoy)
        $fechaInicio = $_GET['f_ini'] ?? date('Y-m-01');
        $fechaFin = $_GET['f_fin'] ?? date('Y-m-d');
        
        // Llamamos al modelo para obtener los datos
        $compras = $modelo->listar($fechaInicio, $fechaFin);
        
        // Aquí normalmente se incluiría la vista HTML
        // require_once 'views/compras/index.php';
        
        // Para propósitos de demostración/debug, imprimimos JSON si no hay vista
        // echo json_encode($compras);
    }

    // Acción guardar: Procesa el formulario o petición JSON para crear una compra
    public function guardar() {
        global $db_connection;
        
        // Solo procesamos si el método es POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Intentamos leer el cuerpo de la petición como JSON (común en aplicaciones modernas/SPA)
            $input = json_decode(file_get_contents('php://input'), true);
            // Si no es JSON, asumimos que son datos de formulario estándar ($_POST)
            if (!$input) $input = $_POST; 

            $modelo = new Compra($db_connection);
            
            // Verificamos que el usuario haya iniciado sesión
            if (!isset($_SESSION['user'])) {
                http_response_code(403); // Código HTTP Prohibido
                die("No autorizado");
            }

            // Preparamos el arreglo de datos para el modelo
            $datos = [
                'id_proveedor' => $input['id_proveedor'],
                'id_usuario' => $_SESSION['user']['id'], // El usuario que registra es el de la sesión actual
                'total' => $input['total'],
                'detalles' => $input['detalles'] // Lista de productos: {id_disco, cantidad, costo}
            ];

            try {
                // Intentamos crear la compra
                $id = $modelo->crear($datos);
                // Si éxito, respondemos con JSON indicando el ID creado
                echo json_encode(['status' => 'success', 'id_compra' => $id]);
            } catch (Exception $e) {
                // Si error, enviamos código 500 y el mensaje de error
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }
}
