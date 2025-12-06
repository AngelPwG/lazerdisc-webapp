<?php
require_once "config/db_config.php";

class Usuario {

    public static function login($username, $password) {
        $conn = getDBConnection();

        // 1. Buscar al usuario solo por el nombre de usuario
        $sql = "SELECT * FROM usuarios 
                WHERE username = ? 
                AND activo = 1"; 

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);

        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        if ($usuario && $password === $usuario['password']) {
            return $usuario;
        }

        return null;
    }
}