<?php
// views/includes/menu.php
// Obtenemos el rol de la sesión. Si no existe, asumimos 'guest' (aunque index.php ya protege).
$rol = $_SESSION['usuario']['rol'] ?? 'guest';
?>
<nav style="background: #6F9BD1; " class="menu">
    <a href="index.php" class="menu-logo"><img src="assets/img/logo.png" alt="LazerDisc"></a>

    <div class="menu-center">
        <!-- Catálogo visible para todos (admin y operador) -->
        <a href="index.php?c=Discos&a=index" class="menu-text">Catálogo</a>

        <!-- Ventas visible para todos -->
        <a href="index.php?c=Ventas&a=index" class="menu-text">Punto de Venta</a>

        <!-- Compras SOLO para admin -->
        <?php if ($rol === 'admin'): ?>
            <a href="index.php?c=Compras&a=index" class="menu-text">Compras</a>
        <?php endif; ?>

        <!-- Devoluciones visible para todos -->
        <a href="index.php?c=Devoluciones&a=index" class="menu-text">Devoluciones</a>

        <!-- Reportes SOLO para admin -->
        <?php if ($rol === 'admin'): ?>
            <a href="index.php?c=Reportes&a=index" class="menu-text">Reportes</a>
        <?php endif; ?>

        <!-- Cierre de Caja (visible para todos) -->
        <?php if ($rol === 'operador'): ?>
            <a href="index.php?c=Reportes&a=generar&tipo=corte&formato=html&f_ini=<?= date('Y-m-d') ?>" class="menu-text" target="_blank">Cierre de Caja IMPRIMIR</a>
        <?php endif; ?>
        <!-- Cierre de Caja SOLO para operador (en CSV direct) -->
        <?php if ($rol === 'operador'): ?>
            <a href="index.php?c=Reportes&a=generar&tipo=corte&formato=csv&f_ini=<?= date('Y-m-d') ?>" class="menu-text" target="_blank">Cierre de Caja CSV</a> |
        <?php endif; ?>
    </div>

    <a href="index.php?c=Auth&a=logout" class="logout"><img class="menu-icon" src="assets/img/salir.png" alt="Salir"></a>
</nav>
<hr>