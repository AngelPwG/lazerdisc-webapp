<?php
require_once "config/db_config.php";

class Usuario {

    public static function login($username, $password) {
        $conn = getDBConnection();

        $sql = "SELECT * FROM usuarios 
                WHERE username = ? 
                AND password = ? 
                AND activo = 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc(); // array o null
    }
}
