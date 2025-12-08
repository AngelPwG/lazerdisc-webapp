<?php
session_start();

// -- IMPORTANTE: Definimos esta constante para que DiscosController no falle
define('INDEX_KEY', true);

// Incluimos la configuración de la BD
require_once 'config/db_config.php';

// Inicializamos la conexión global que usan los controladores
$db_connection = getDBConnection();

// 1. Controlador y acción por defecto
$nombre_controlador = isset($_GET['c']) ? $_GET['c'] : 'Auth';
$nombre_accion = isset($_GET['a']) ? $_GET['a'] : 'index';

// 2. LÓGICA DE SEGURIDAD
// Si la ruta solicitada NO es Auth Y el usuario NO tiene una sesión,
// lo enviamos al login.
if ($nombre_controlador !== 'Auth' && !isset($_SESSION['usuario'])) {
    header("Location: index.php?c=Auth&a=index");
    exit;
}

// 2.1 CONTROL DE PERMISOS (RBAC)
if (isset($_SESSION['usuario'])) {
    $rol = $_SESSION['usuario']['rol'];
    
    // Definir permisos: 'Controlador' => ['acciones_permitidas'] o '*' para todas
    $permisos = [
        'admin' => '*', // Admin tiene acceso total
        'operador' => [
            'Auth' => '*',
            'Ventas' => '*',
            'Devoluciones' => '*',
            'Discos' => ['index'] // Solo lectura en catálogo
        ]
    ];

    // Verificar si el rol tiene permisos
    $acceso_permitido = false;

    if ($permisos[$rol] === '*') {
        $acceso_permitido = true;
    } elseif (isset($permisos[$rol][$nombre_controlador])) {
        $acciones = $permisos[$rol][$nombre_controlador];
        if ($acciones === '*' || in_array($nombre_accion, $acciones)) {
            $acceso_permitido = true;
        }
    }

    if (!$acceso_permitido) {
        // Redirigir a una página segura por defecto o mostrar error
        if ($rol === 'operador') {
             // Si intenta entrar a algo no permitido, lo mandamos a Ventas que es su home
             header("Location: index.php?c=Ventas&a=index");
        } else {
             die("Acceso denegado: No tienes permisos para esta sección.");
        }
        exit;
    }
}
// -------------------------

$controlador_clase = $nombre_controlador . 'Controller';

// 3. Cargar controlador
if (file_exists("controllers/$controlador_clase.php")) {
    require_once "controllers/$controlador_clase.php";
} else {
    die("El controlador $controlador_clase no existe.");
}

// 4. Instanciar
$controlador = new $controlador_clase();

// 5. Ejecutar acción
if (method_exists($controlador, $nombre_accion)) {
    $controlador->$nombre_accion();
} else {
    die("La acción $nombre_accion no existe en el controlador $controlador_clase.");
}
?>