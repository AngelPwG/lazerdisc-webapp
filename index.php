<?php
    session_start();

    // -- IMPORTANTE: Definimos esta constante para que DiscosController no falle
    define('INDEX_KEY', true); 

    // 1. Controlador y acción por defecto
    $nombre_controlador = isset($_GET['c']) ? $_GET['c'] : 'Auth';
    $nombre_accion      = isset($_GET['a']) ? $_GET['a'] : 'index';

    // 2. LÓGICA DE SEGURIDAD
    // Si la ruta solicitada NO es Auth Y el usuario NO tiene una sesión,
    // lo enviamos al login.
    if ($nombre_controlador !== 'Auth' && !isset($_SESSION['usuario'])) {
        header("Location: index.php?c=Auth&a=index");
        exit;
    }
    // -------------------------

    $controlador_clase = $nombre_controlador . 'Controller';

    // 3. Cargar controlador
    require_once "controllers/$controlador_clase.php";

    // 4. Instanciar
    $controlador = new $controlador_clase();

    // 5. Ejecutar acción
    $controlador->$nombre_accion();
?>