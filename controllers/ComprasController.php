<?php
// controllers/ComprasController.php
// Verificamos acceso seguro
if (!defined('INDEX_KEY'))
    die('Acceso denegado');

require_once 'models/Compra.php';
require_once 'models/Discos.php';

// Clase ComprasController: Maneja las peticiones HTTP relacionadas con compras
class ComprasController
{
    // Acción index: Muestra el listado de compras
    public function index()
    {
        global $db_connection;
        $modelo = new Compra($db_connection);

        $fechaInicio = $_GET['f_ini'] ?? date('Y-11-01');
        $fechaFin = $_GET['f_fin'] ?? date('Y-m-d');

        $compras = $modelo->listar($fechaInicio, $fechaFin);

        require_once 'views/compras/index.php';
    }

    // Acción crear: Muestra el formulario de registro de nueva compra
    public function crear()
    {
        global $db_connection;
        $modelo = new Compra($db_connection);
        $proveedores = $modelo->obtenerProveedores();
        require_once 'views/compras/crear.php';
    }

    // Acción para validar producto por código de barras (API endpoint para AJAX)
    public function validarProducto()
    {
        global $db_connection;
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $codigo = $_GET['codigo'] ?? '';
            
            if (empty($codigo)) {
                echo json_encode(['status' => 'error', 'message' => 'Código requerido']);
                return;
            }
            
            $modeloDisco = new Disco($db_connection);
            $producto = $modeloDisco->obtenerPorCodigo($codigo);
            
            if (!$producto) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Producto no encontrado.'
                ]);
                return;
            }
            
            echo json_encode([
                'status' => 'success',
                'producto' => [
                    'id_disco' => $producto['id_disco'],
                    'titulo' => $producto['titulo'],
                    'codigo_barras' => $producto['codigo_barras'],
                    'costo_promedio' => $producto['costo_promedio'],
                    'precio_venta' => $producto['precio_venta']
                ]
            ]);
        }
    }

    // Acción guardar: Procesa el formulario o petición JSON para crear una compra
    public function guardar()
    {
        global $db_connection;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input)
                $input = $_POST;

            $modelo = new Compra($db_connection);
            $modeloDisco = new Disco($db_connection); // Necesitamos el modelo de discos para buscar por código

            if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => "No autorizado"]); return;
            }

            // Procesar detalles para obtener IDs a partir de códigos de barras
            $detallesProcesados = [];
            foreach ($input['detalles'] as $detalle) {
                $codigo = $detalle['codigo_barras'];
                $disco = $modeloDisco->obtenerPorCodigo($codigo);

                if (!$disco) {
                    http_response_code(400); // Bad Request
                    echo json_encode(['status' => 'error', 'message' => "El código de barras '$codigo' no existe."]);
                    return;
                }

                $detallesProcesados[] = [
                    'id_disco' => $disco['id_disco'],
                    'cantidad' => $detalle['cantidad'],
                    'costo' => $detalle['costo']
                ];
            }

            $datos = [
                'id_proveedor' => $input['id_proveedor'],
                'id_usuario' => $_SESSION['usuario']['id_usuario'],
                'total' => $input['total'],
                'detalles' => $detallesProcesados
            ];

            try {
                $id = $modelo->crear($datos);
                echo json_encode(['status' => 'success', 'id_compra' => $id]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }
}
