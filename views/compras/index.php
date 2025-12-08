<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compras</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'views/includes/menu.php'; ?>

    <h1>Gestión de Compras (Entradas)</h1>

    <div style="margin-bottom: 10px;">
        <a href="index.php?c=Compras&a=crear">Registrar Nueva Compra</a>
    </div>

    <form action="index.php" method="GET">
        <input type="hidden" name="c" value="Compras">
        <input type="hidden" name="a" value="index">
        
        <label>Desde:</label>
        <input type="date" name="f_ini" value="<?= htmlspecialchars($_GET['f_ini'] ?? date('Y-11-01')) ?>">
        
        <label>Hasta:</label>
        <input type="date" name="f_fin" value="<?= htmlspecialchars($_GET['f_fin'] ?? date('Y-m-d')) ?>">
        
        <button type="submit">Filtrar</button>
    </form>
    
    <br>

    <table border="1">
        <thead>
            <tr>
                <th>ID Compra</th>
                <th>Proveedor</th>
                <th>Usuario</th>
                <th>Fecha</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($compras)): ?>
                <tr><td colspan="5">No hay compras en este rango.</td></tr>
            <?php else: ?>
                <?php foreach($compras as $c): ?>
                    <tr>
                        <td><?= $c['id_compra'] ?></td>
                        <td><?= htmlspecialchars($c['proveedor']) ?></td>
                        <td><?= htmlspecialchars($c['usuario']) ?></td>
                        <td><?= $c['fecha_compra'] ?></td>
                        <td><?= number_format($c['total_compra'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
