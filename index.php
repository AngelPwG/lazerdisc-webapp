<?php
    session_start();

    // 1. Controlador y acción por defecto
    $nombre_controlador = isset($_GET['c']) ? $_GET['c'] : 'Auth';
    $nombre_accion      = isset($_GET['a']) ? $_GET['a'] : 'index';

    $controlador_clase = $nombre_controlador . 'Controller';

    // 2. Cargar controlador
    require_once "controllers/$controlador_clase.php";

    // 3. Instanciar
    $controlador = new $controlador_clase();

    // 4. Ejecutar acción
    $controlador->$nombre_accion();
 