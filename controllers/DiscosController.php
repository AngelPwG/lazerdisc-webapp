<?php
// controllers/DiscosController.php
if (!defined('INDEX_KEY'))
    die('Acceso denegado');

require_once "models/Discos.php";
// Clase DiscosController: Maneja las peticiones relacionadas con el catálogo de discos
class DiscosController
{

    // Acción index: Muestra la lista de discos
    public function index()
    {
        global $db_connection; // Usamos la conexión global

        $modelo = new Disco($db_connection);

        // Capturar término de búsqueda si existe
        $busqueda = isset($_GET['q']) ? $_GET['q'] : '';

        // Llamamos al método listar del modelo con la búsqueda (limit 10)
        $listaDiscos = $modelo->listar(10, 0, $busqueda);

        // Cargamos la vista correspondiente
        require_once 'views/discos/index.php';
    }

    // Acción crear: Muestra el formulario para un nuevo disco
    public function crear($error = null)
    {
        global $db_connection;
        // Instanciamos el modelo y cargamos los catálogos necesarios para los selectores
        $modelo = new Disco($db_connection);

        $artistas = $modelo->obtenerCatalogo('artistas');
        $generos = $modelo->obtenerCatalogo('generos');
        $disqueras = $modelo->obtenerCatalogo('disqueras');

        // Cargamos la vista de creación
        require_once 'views/discos/crear.php';
    }

    // Acción editar: Muestra el formulario para editar un disco existente
    public function editar($id = null, $error = null)
    {
        global $db_connection;

        // Verificamos que venga el ID (prioridad al parámetro, luego GET)
        $id_disco = $id ?? $_GET['id'] ?? null;

        if (!$id_disco) {
            header("Location: index.php?c=Discos&a=index&error=ID no proporcionado");
            return;
        }

        $modelo = new Disco($db_connection);
        // Obtenemos los datos del disco y los catálogos
        $disco = $modelo->obtenerPorId($id_disco);

        if (!$disco) {
            header("Location: index.php?c=Discos&a=index&error=Disco no encontrado");
            return;
        }

        $artistas = $modelo->obtenerCatalogo('artistas');
        $generos = $modelo->obtenerCatalogo('generos');
        $disqueras = $modelo->obtenerCatalogo('disqueras');

        // Cargamos la vista de edición (puede ser la misma que crear pero con datos)
        require_once 'views/discos/editar.php';
    }

    // Acción guardar: Procesa el formulario de creación de nuevo disco
    public function guardar()
    {
        global $db_connection;

        // Solo procesamos si es petición POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelo = new Disco($db_connection);

            // Procesamos la imagen principal subida
            $tmpName = $_FILES['imagen']['tmp_name'];
            $contenidoBinario = file_get_contents($tmpName);

            // Recogemos todos los datos del formulario POST
            $datos = [
                'titulo' => $_POST['titulo'],
                'artista' => $_POST['id_artista'],
                'generos' => $_POST['id_genero'], // Puede ser array
                'anio' => $_POST['anio'],
                'disquera' => $_POST['id_disquera'],
                'codigo_barras' => $_POST['codigo_barras'],
                'parental' => $_POST['parental'],
                'precio' => $_POST['precio'],
                'costo' => $_POST['costo'],
                'canciones' => $_POST['canciones'] ?? [], // Lista de canciones
                'imagen_binaria' => $contenidoBinario
            ];

            // Recogemos imagenes adicionales (galería)
            $files = $_FILES['imagenes_extra'];

            try {
                // Llamamos al modelo para crear el registro
                $modelo->crear($datos, $files);
                // Redireccionamos al índice con mensaje de éxito
                header("Location: index.php?c=Discos&a=index&msg=Creado");
            } catch (Exception $e) {
                // Si hay error, lo capturamos y mostramos la vista de creación nuevamente
                $this->crear($e->getMessage());
            }
        }
    }

    // Acción actualizar: Procesa el formulario de edición de un disco existente
    public function actualizar()
    {
        global $db_connection;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelo = new Disco($db_connection);

            // Recogemos datos del formulario
            $datos = [
                'titulo' => $_POST['titulo'],
                'artista' => $_POST['id_artista'],
                'generos' => $_POST['id_genero'],
                'anio' => $_POST['anio'],
                'disquera' => $_POST['id_disquera'],
                'codigo_barras' => $_POST['codigo_barras'],
                'parental' => $_POST['parental'],
                'precio' => $_POST['precio'],
                'costo' => $_POST['costo'],
                'canciones' => $_POST['canciones'] ?? [],
            ];

            $files['imagen_principal'] = $_FILES['imagen'];
            // Recogemos imagenes adicionales
            $files['imagenes_extra'] = $_FILES['imagenes_extra'] ?? [];

            try {
                // Llamamos al modelo para actualizar
                $modelo->actualizar($_POST['id_disco'], $datos, $files);
                header("Location: index.php?c=Discos&a=index&msg=Actualizado"); // Mensaje podría ser "Actualizado"
            } catch (Exception $e) {
                $this->editar($_POST['id_disco'], $e->getMessage());
            }
        }
    }

}