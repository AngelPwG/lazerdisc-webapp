<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body class="login-page">
    <div class="login-container">
        <div class="logo-space">
            <img src="assets/img/logo.png" alt="Logo de la Empresa">
        </div>

        <h2>Iniciar Sesión</h2>

        <form method="POST" action="index.php?c=Auth&a=login">
            <div class="input-group">
                <label for="user">Usuario</label>
                <input type="text" id="user" name="username" placeholder="" required>
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