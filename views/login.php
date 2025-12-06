<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="/dashboard/lazerdisc-webapp/styles/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo-space">
            <img src="/dashboard/lazerdisc-webapp/assets/img/logo.png" alt="Logo de la Empresa">
        </div>
        
        <h2>Iniciar Sesión</h2>
      
        <form method="POST" action="auth.php">
            <div class="input-group">
                <label for="user">Usuario</label>
                <input type="text" id="user" name="user" placeholder="" required>
            </div>
            
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="" required>
            </div>
            
            <button type="submit">Entrar</button>
            
        </form>
    </div>
</body>
</html>