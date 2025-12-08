<?php
// controllers/VentasController.php
if (!defined('INDEX_KEY'))
    die('Acceso denegado');

// Clase VentasController: Controla el flujo del Punto de Venta (POS)
require_once 'models/Discos.php';
require_once 'models/Venta.php';

class VentasController
{

    // Acción index: Carga la vista principal del POS
    public function index()
    {
        // Aquí se cargaría la interfaz gráfica donde se escanean productos
        require_once 'views/ventas/pos.php';
    }

    // Acción agregar: Recibe un código de barras (AJAX) y agrega el producto al carrito en sesión
    public function agregar()
    {
        global $db_connection;

        $codigo = $_POST['codigo'] ?? '';
        if (empty($codigo)) {
            echo json_encode(['status' => 'error', 'msg' => 'Código vacío']);
            return;
        }

        // Reutilizamos el modelo Disco para buscar el producto
        $modeloDisco = new Disco($db_connection);
        $disco = $modeloDisco->obtenerPorCodigo($codigo);

        if ($disco) {
            // Inicializamos el carrito en sesión si no existe
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }

            // Buscamos si el producto ya está en el carrito para solo aumentar cantidad
            $encontrado = false;
            foreach ($_SESSION['carrito'] as &$item) {
                // Usamos referencia &$item para modificar el array original
                if ($item['id_disco'] == $disco['id_disco']) {
                    $item['cantidad']++;
                    $item['subtotal'] = $item['cantidad'] * $item['precio_venta'];
                    $encontrado = true;
                    break;
                }
            }

            // Si no estaba, lo agregamos como nuevo item
            if (!$encontrado) {
                $_SESSION['carrito'][] = [
                    'id_disco' => $disco['id_disco'],
                    'codigo' => $disco['codigo_barras'],
                    'titulo' => $disco['titulo'],
                    'precio_venta' => $disco['precio_venta'],
                    'cantidad' => 1,
                    'subtotal' => $disco['precio_venta']
                ];
            }

            // Devolvemos el carrito actualizado para que JS actualice la tabla
            echo json_encode(['status' => 'success', 'carrito' => array_values($_SESSION['carrito'])]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Producto no encontrado']);
        }
    }

    // Acción eliminar: Elimina un item del carrito
    public function eliminar()
    {
        $id_disco = $_POST['id_disco'] ?? null;
        if ($id_disco && isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as $key => $item) {
                if ($item['id_disco'] == $id_disco) {
                    unset($_SESSION['carrito'][$key]);
                    break;
                }
            }
            // Reindexar array
            $_SESSION['carrito'] = array_values($_SESSION['carrito']);
        }
        echo json_encode(['status' => 'success', 'carrito' => $_SESSION['carrito']]);
    }

    // Acción cancelar: Limpia todo el carrito
    public function cancelar()
    {
        if (isset($_SESSION['carrito'])) {
            unset($_SESSION['carrito']);
        }
        echo json_encode(['status' => 'success']);
    }

    // Acción confirmar: Finaliza la venta guardando en BD
    public function confirmar()
    {
        global $db_connection;

        // Validaciones básicas
        if (empty($_SESSION['carrito'])) {
            echo json_encode(['status' => 'error', 'msg' => 'Carrito vacío']);
            return;
        }

        if (!isset($_SESSION['usuario'])) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            return;
        }

        $modelo = new Venta($db_connection);

        // Calculamos total y preparamos detalles desde la sesión
        $total = 0;
        $detalles = [];
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['subtotal'];
            $detalles[] = [
                'id_disco' => $item['id_disco'],
                'cantidad' => $item['cantidad'],
                'precio' => $item['precio_venta']
            ];
        }

        $datos = [
            'id_usuario' => $_SESSION['usuario']['id_usuario'],
            'total' => $total * 1.16,
            'detalles' => $detalles
        ];

        try {
            // Llamamos al modelo para crear la transacción
            $res = $modelo->crear($datos);
            // Si éxito, limpiamos el carrito
            unset($_SESSION['carrito']);
            // Retornamos ID y Folio para redireccionar al ticket
            echo json_encode(['status' => 'success', 'id_venta' => $res['id_venta'], 'folio' => $res['folio']]);
        } catch (Exception $e) {
            // Error (ej. stock insuficiente)
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }

    // Acción ticket: Genera los datos para la impresión del ticket
    public function ticket()
    {
        global $db_connection;
        $id_venta = $_GET['id'] ?? 0;

        $modelo = new Venta($db_connection);
        $datosTicket = $modelo->obtenerParaTicket($id_venta);

        if ($datosTicket) {
            // Cálculos fiscales para mostrar desglose en ticket
            // Asumiendo precio con IVA incluido (dividir entre 1.16)
            $total = $datosTicket['total_venta'];
            $subtotal = $total / 1.16;
            $iva = $total - $subtotal;

            // Agregamos valores calculados al arreglo
            $datosTicket['subtotal_calc'] = $subtotal;
            $datosTicket['iva_calc'] = $iva;

            // Extraemos variables para uso directo en la vista
            extract($datosTicket);

            // Cargamos la vista de impresión
            require_once 'views/ventas/ticket.php';
        } else {
            echo "Venta no encontrada";
        }
    }
}
