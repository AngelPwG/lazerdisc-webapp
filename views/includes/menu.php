<?php
// views/includes/menu.php
// Obtenemos el rol de la sesión. Si no existe, asumimos 'guest' (aunque index.php ya protege).
$rol = $_SESSION['usuario']['rol'] ?? 'guest';
?>
<nav style="background: #eee; padding: 10px; margin-bottom: 20px;">
    <strong>LazerDisc</strong> |
    
    <!-- Catálogo visible para todos (admin y operador) -->
    <a href="index.php?c=Discos&a=index">Catálogo</a> |
    
    <!-- Ventas visible para todos -->
    <a href="index.php?c=Ventas&a=index">Punto de Venta</a> |
    
    <!-- Compras SOLO para admin -->
    <?php if ($rol === 'admin'): ?>
        <a href="index.php?c=Compras&a=index">Compras</a> |
    <?php endif; ?>

    <!-- Devoluciones visible para todos -->
    <a href="index.php?c=Devoluciones&a=index">Devoluciones</a> |
    
    <!-- Reportes SOLO para admin -->
    <?php if ($rol === 'admin'): ?>
        <a href="index.php?c=Reportes&a=index">Reportes</a> |
    <?php endif; ?>

    <a href="index.php?c=Auth&a=logout" style="float: right;">Salir</a>
</nav>
<hr>