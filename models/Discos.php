<?php
// models/Disco.php

// Verificamos que la constante INDEX_KEY esté definida para evitar acceso directo al archivo
if (!defined('INDEX_KEY')) {
    die('Acceso denegado');
}

// Clase Disco: Maneja la lógica de negocio relacionada con los discos (productos)
class Disco
{
    // Propiedad para almacenar la conexión a la base de datos
    private $db;

    // Constructor: Recibe la conexión a la BD y la asigna a la propiedad local
    public function __construct($conexion)
    {
        $this->db = $conexion;
    }

    // =======================================================
    // 1. MÉTODOS DE LECTURA (READ)
    // =======================================================

    // Método para listar discos con paginación y búsqueda
    // $limit: cantidad de registros por página
    // $offset: desde qué registro empezar
    // $busqueda: término de búsqueda (opcional)
    // $mostrarTodos: si es true, muestra activos e inactivos. Si es false, solo activos.
    public function listar($limit = 10, $offset = 0, $busqueda = '', $mostrarTodos = false)
    {
        // Consulta SQL compleja con múltiples JOINs para obtener toda la información relacionada
        $sql = "SELECT d.id_disco, d.titulo, d.codigo_barras, d.precio_venta, d.costo_promedio, d.tipo, d.activo,
                       a.nombre_artista, di.nombre_disquera,
                       e.cantidad_actual as stock,
                       im.contenido_imagen,
                       GROUP_CONCAT(DISTINCT g.nombre_genero SEPARATOR ', ') as lista_generos
                FROM discos d
                JOIN artistas a ON d.id_artista = a.id_artista 
                JOIN discos_generos dg ON d.id_disco = dg.id_disco
                JOIN generos g ON dg.id_genero = g.id_genero
                LEFT JOIN disqueras di ON d.id_disquera = di.id_disquera
                LEFT JOIN existencias e ON d.id_disco = e.id_disco
                LEFT JOIN imagenes_discos im ON d.id_disco = im.id_disco AND im.es_principal = 1
                WHERE 1=1";

        if (!$mostrarTodos) {
            $sql .= " AND d.activo = 1";
        }

        // Si hay término de búsqueda, agregamos condiciones OR para buscar en título, código o artista
        if ($busqueda) {
            $sql .= " AND (d.titulo LIKE ? OR d.codigo_barras LIKE ? OR a.nombre_artista LIKE ?)";
        }

        // Agrupamos por ID de disco y ordenamos descendente
        $sql .= " GROUP BY d.id_disco ORDER BY d.id_disco DESC LIMIT ? OFFSET ?";

        // Preparamos la consulta
        $stmt = $this->db->prepare($sql);

        // Vinculamos parámetros dinámicamente según si hubo búsqueda o no
        if ($busqueda) {
            $term = "%$busqueda%";
            $stmt->bind_param("sssii", $term, $term, $term, $limit, $offset);
        } else {
            $stmt->bind_param("ii", $limit, $offset);
        }

        // Ejecutamos la consulta
        $stmt->execute();
        $result = $stmt->get_result();

        // Procesamos los resultados
        $lista = [];
        while ($row = $result->fetch_assoc()) {
            // Si hay imagen binaria, la convertimos a Base64 para mostrarla en HTML
            if ($row['contenido_imagen']) {
                $row['imagen_base64'] = base64_encode($row['contenido_imagen']);
                unset($row['contenido_imagen']); // Liberamos memoria del binario
            } else {
                $row['imagen_base64'] = null;
            }
            $lista[] = $row;
        }
        return $lista;
    }

    // Método para obtener un solo disco por su código de barras (Detalle)
    public function obtenerPorCodigo($codigo)
    {
        if (empty($codigo))
            return null;

        // Consulta específica para un solo registro
        $sql = "SELECT d.*, a.nombre_artista, di.nombre_disquera,
                       e.cantidad_actual as stock,
                       im.contenido_imagen
                FROM discos d
                JOIN artistas a ON d.id_artista = a.id_artista 
                LEFT JOIN disqueras di ON d.id_disquera = di.id_disquera
                LEFT JOIN existencias e ON d.id_disco = e.id_disco
                LEFT JOIN imagenes_discos im ON d.id_disco = im.id_disco AND im.es_principal = 1
                WHERE d.codigo_barras = ? AND d.activo = 1 LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $disco = $stmt->get_result()->fetch_assoc();

        // Si encontramos el disco, cargamos sus relaciones (géneros, canciones, imágenes extra)
        if ($disco) {
            $disco['generos'] = $this->obtenerGeneros($disco['id_disco']);
            $disco['canciones'] = $this->obtenerCanciones($disco['id_disco']);
            $disco['imagenes_extra'] = $this->obtenerImagenesExtra($disco['id_disco']);

            // Convertimos imagen principal a Base64
            if ($disco['contenido_imagen']) {
                $disco['imagen_base64'] = base64_encode($disco['contenido_imagen']);
                unset($disco['contenido_imagen']);
            }
        }
        return $disco;
    }

    // Método para obtener un solo disco por su ID (Para edición)
    public function obtenerPorId($id)
    {
        if (empty($id))
            return null;

        $sql = "SELECT d.*, a.nombre_artista, di.nombre_disquera,
                       e.cantidad_actual as stock,
                       im.contenido_imagen
                FROM discos d
                JOIN artistas a ON d.id_artista = a.id_artista 
                LEFT JOIN disqueras di ON d.id_disquera = di.id_disquera
                LEFT JOIN existencias e ON d.id_disco = e.id_disco
                LEFT JOIN imagenes_discos im ON d.id_disco = im.id_disco AND im.es_principal = 1
                WHERE d.id_disco = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $disco = $stmt->get_result()->fetch_assoc();

        if ($disco) {
            $disco['generos'] = $this->obtenerGeneros($disco['id_disco']);
            $disco['canciones'] = $this->obtenerCanciones($disco['id_disco']);
            $disco['imagenes_extra'] = $this->obtenerImagenesExtra($disco['id_disco']);

            if ($disco['contenido_imagen']) {
                $disco['imagen_base64'] = base64_encode($disco['contenido_imagen']);
                unset($disco['contenido_imagen']);
            }
        }
        return $disco;
    }

    // Método auxiliar para obtener listas de catálogos (Artistas, Géneros, Disqueras)
    public function obtenerCatalogo($tabla)
    {
        // Lista blanca para evitar inyección SQL en el nombre de la tabla
        $tablas_validas = ['artistas', 'disqueras', 'generos'];
        if (!in_array($tabla, $tablas_validas))
            return [];

        // Construimos nombres de columnas dinámicamente
        $col_id = "id_" . rtrim($tabla, 's'); // artistas -> id_artista
        if ($tabla == 'generos')
            $col_id = 'id_genero';

        $col_nom = "nombre_" . rtrim($tabla, 's');
        if ($tabla == 'generos')
            $col_nom = 'nombre_genero';

        $sql = "SELECT $col_id as id, $col_nom as nombre FROM $tabla ORDER BY $col_nom ASC";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    // =======================================================
    // 2. MÉTODOS DE ESCRITURA (CREATE / UPDATE)
    // =======================================================

    // Método para crear un nuevo disco
    public function crear($datos, $files)
    {
        // Iniciamos transacción para asegurar que todo se guarde o nada
        $this->db->begin_transaction();
        try {
            // 1. Insertar Disco Base
            $stmt = $this->db->prepare("INSERT INTO discos (titulo, id_artista, id_disquera, tipo, codigo_barras, anio_lanzamiento, control_parental, precio_venta, costo_promedio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siissiidd", $datos['titulo'], $datos['artista'], $datos['disquera'], $datos['tipo'], $datos['codigo_barras'], $datos['anio'], $datos['parental'], $datos['precio'], $datos['costo']);
            $stmt->execute();
            $id_disco = $stmt->insert_id; // Obtenemos ID generado
            $stmt->close();

            // 2. Inicializar Stock en 0
            $this->db->query("INSERT INTO existencias (id_disco, cantidad_actual) VALUES ($id_disco, 0)");

            // 3. Procesar Imagen Principal (si existe)
            if (!empty($datos['imagen_binaria'])) {
                $this->guardarImagen($id_disco, $datos['imagen_binaria'], 1);
            }

            // 4. Procesar Imágenes Extra (galería)
            if (isset($files['imagenes_extras'])) {
                $this->procesarImagenesExtras($id_disco, $files['imagenes_extras']);
            }

            // 5. Guardar relaciones (Géneros y Canciones)
            $this->guardarGeneros($id_disco, $datos['generos'] ?? []);
            $this->guardarCanciones($id_disco, $datos['canciones'] ?? []);

            // Confirmamos transacción
            $this->db->commit();
            return $id_disco;

        } catch (Exception $e) {
            // Si falla, revertimos todo
            $this->db->rollback();
            throw $e;
        }
    }

    // Método para actualizar un disco existente
    public function actualizar($id_disco, $datos, $files)
    {
        $this->db->begin_transaction();
        try {
            // 1. Actualizar datos de la tabla Base
            $stmt = $this->db->prepare("UPDATE discos SET titulo=?, id_artista=?, id_disquera=?, tipo=?, anio_lanzamiento=?, control_parental=?, precio_venta=?, costo_promedio=? WHERE id_disco=?");
            $stmt->bind_param("siisiiddi", $datos['titulo'], $datos['artista'], $datos['disquera'], $datos['tipo'], $datos['anio'], $datos['parental'], $datos['precio'], $datos['costo'], $id_disco);
            $stmt->execute();
            $stmt->close();

            // 2. Actualizar Relaciones (Estrategia: Borrar todo y reinsertar lo nuevo)
            $this->db->query("DELETE FROM discos_generos WHERE id_disco = $id_disco");
            $this->guardarGeneros($id_disco, $datos['generos'] ?? []);

            $this->db->query("DELETE FROM canciones WHERE id_disco = $id_disco");
            $this->guardarCanciones($id_disco, $datos['canciones'] ?? []);

            // 3. Actualizar Imagen Principal (Si subieron una nueva)
            if (isset($files['imagen_principal']) && $files['imagen_principal']['error'] === UPLOAD_ERR_OK) {
                $contenido = file_get_contents($files['imagen_principal']['tmp_name']);

                // Borrar anterior si existe y crear nueva
                $this->db->query("DELETE FROM imagenes_discos WHERE id_disco = $id_disco AND es_principal = 1");
                $this->guardarImagen($id_disco, $contenido, 1);
            }

            // 4. Gestionar Imágenes extras (Eliminar seleccionadas y agregar nuevas)
            if (!empty($datos['eliminar_imagenes'])) {
                foreach ($datos['eliminar_imagenes'] as $id_img) {
                    $this->eliminarImagen($id_img, $id_disco);
                }
            }
            if (isset($files['imagenes_extras'])) {
                $this->procesarImagenesExtras($id_disco, $files['imagenes_extras']);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Método para borrado lógico (cambiar estado activo/inactivo)
    public function cambiarEstado($id_disco, $activo)
    {
        $stmt = $this->db->prepare("UPDATE discos SET activo = ? WHERE id_disco = ?");
        $stmt->bind_param("ii", $activo, $id_disco);
        return $stmt->execute();
    }

    // =======================================================
    // 3. MÉTODOS PRIVADOS (HELPERS)
    // =======================================================

    // Helper para guardar relaciones de géneros
    private function guardarGeneros($id_disco, $generos)
    {
        if (empty($generos))
            return;
        $stmt = $this->db->prepare("INSERT INTO discos_generos (id_disco, id_genero) VALUES (?, ?)");
        foreach ($generos as $g_id) {
            $stmt->bind_param("ii", $id_disco, $g_id);
            $stmt->execute();
        }
        $stmt->close();
    }

    // Helper para guardar lista de canciones
    private function guardarCanciones($id_disco, $canciones)
    {
        if (empty($canciones))
            return;
        $stmt = $this->db->prepare("INSERT INTO canciones (id_disco, numero_pista, titulo_cancion, duracion) VALUES (?, ?, ?, ?)");
        $pista = 1;
        foreach ($canciones as $c) {
            if (empty($c['titulo']))
                continue;
            // Formatear duración si viene incompleta
            $duracion = (strlen($c['duracion']) > 5) ? $c['duracion'] : "00:" . $c['duracion'];
            $stmt->bind_param("iiss", $id_disco, $pista, $c['titulo'], $duracion);
            $stmt->execute();
            $pista++;
        }
        $stmt->close();
    }

    // Helper para guardar una imagen BLOB
    private function guardarImagen($id_disco, $binario, $es_principal)
    {
        $stmt = $this->db->prepare("INSERT INTO imagenes_discos (id_disco, contenido_imagen, es_principal) VALUES (?, ?, ?)");
        $null = null; // Necesario para send_long_data
        $stmt->bind_param("ibi", $id_disco, $null, $es_principal);
        $stmt->send_long_data(1, $binario); // Enviamos datos binarios
        $stmt->execute();
        $stmt->close();
    }

    // Helper para procesar array de imágenes múltiples
    private function procesarImagenesExtras($id_disco, $fileArray)
    {
        if (!isset($fileArray['tmp_name']) || !is_array($fileArray['tmp_name']))
            return;

        foreach ($fileArray['tmp_name'] as $key => $tmp) {
            if ($fileArray['error'][$key] === UPLOAD_ERR_OK && !empty($tmp)) {
                $contenido = file_get_contents($tmp);
                $this->guardarImagen($id_disco, $contenido, 0); // 0 = no principal
            }
        }
    }

    // Helper para eliminar una imagen específica
    private function eliminarImagen($id_imagen, $id_disco)
    {
        $stmt = $this->db->prepare("DELETE FROM imagenes_discos WHERE id_imagen = ? AND id_disco = ?");
        $stmt->bind_param("ii", $id_imagen, $id_disco);
        $stmt->execute();
    }

    // Helper para obtener géneros de un disco
    private function obtenerGeneros($id)
    {
        $stmt = $this->db->prepare("SELECT g.id_genero, g.nombre_genero FROM generos g JOIN discos_generos dg ON g.id_genero = dg.id_genero WHERE dg.id_disco = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Helper para obtener canciones de un disco
    private function obtenerCanciones($id)
    {
        $stmt = $this->db->prepare("SELECT numero_pista, titulo_cancion, duracion FROM canciones WHERE id_disco = ? ORDER BY numero_pista ASC");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Helper para obtener imágenes extra de un disco
    private function obtenerImagenesExtra($id)
    {
        $stmt = $this->db->prepare("SELECT id_imagen, contenido_imagen FROM imagenes_discos WHERE id_disco = ? AND es_principal = 0");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $imgs = [];
        while ($row = $result->fetch_assoc()) {
            $row['base64'] = base64_encode($row['contenido_imagen']);
            unset($row['contenido_imagen']);
            $imgs[] = $row;
        }
        return $imgs;
    }
}