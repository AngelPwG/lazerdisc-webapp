<?php
require_once "models/Usuario.php";

class AuthController
{

    public function index()
    {
        require "views/auth/index.php";
    }

    public function login()
    {

        if (!isset($_POST['username'], $_POST['password'])) {
            header("Location: index.php?c=Auth&a=index");
            exit;
        }

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);


        $usuario = Usuario::login($username, $password);

        if ($usuario) {
            $_SESSION['usuario'] = $usuario;
            header("Location: index.php?c=Discos&a=index");
            exit;
        } else {
            header("Location: index.php?c=Auth&a=index&error=1");
            exit;
        }
    }

    public function logout()
    {
        session_destroy();
        header("Location: index.php?c=Auth&a=index");
        exit;
    }
}