<?php
// controllers/DiscosController.php
// Verificamos acceso seguro
if (!defined('INDEX_KEY')) die('Acceso denegado');
require_once "models/Discos.php";
// Clase DiscosController: Maneja las peticiones relacionadas con el catálogo de discos
class DiscosController {
    
    // Acción index: Muestra la lista de discos
    public function index() {
        global $db_connection; // Usamos la conexión global
        
        $modelo = new Disco($db_connection);
        // Llamamos al método listar del modelo
        $listaDiscos = $modelo->listar(); 
        
        // Cargamos la vista correspondiente
        require_once 'views/discos/index.php';
    }

    // Acción guardar: Procesa el formulario de creación de nuevo disco
    public function guardar() {
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
                'genero' => $_POST['id_genero'], // Puede ser array
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
                $error = $e->getMessage();
                require_once 'views/discos/crear.php';
            }
        }
    }

    // Acción actualizar: Procesa el formulario de edición de un disco existente
    public function actualizar() {
        global $db_connection;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelo = new Disco($db_connection);
            
            // Procesamos imagen principal si se subió una nueva
            $tmpName = $_FILES['imagen']['tmp_name'];
            $contenidoBinario = file_get_contents($tmpName);

            // Recogemos datos del formulario
            $datos = [
                'titulo' => $_POST['titulo'],
                'artista' => $_POST['id_artista'],
                'genero' => $_POST['id_genero'],
                'anio' => $_POST['anio'],
                'disquera' => $_POST['id_disquera'],
                'codigo_barras' => $_POST['codigo_barras'],
                'parental' => $_POST['parental'],
                'precio' => $_POST['precio'],
                'costo' => $_POST['costo'],
                'canciones' => $_POST['canciones'] ?? [],
                'imagen_binaria' => $contenidoBinario
            ];
            
            // Recogemos imagenes adicionales
            $files = $_FILES['imagenes_extra'];

            try {
                // Llamamos al modelo para actualizar
                $modelo->actualizar($_POST['id_disco'], $datos, $files);
                header("Location: index.php?c=Discos&a=index&msg=Creado"); // Mensaje podría ser "Actualizado"
            } catch (Exception $e) {
                $error = $e->getMessage();
                require_once 'views/discos/crear.php'; // O editar.php
            }
        }
    }

}