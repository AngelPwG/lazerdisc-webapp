<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Compras</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>

    <div class="page-header">
        <h1 class="page-title">Gestión de Compras (Entradas)</h1>

        <div class="page-actions">
            <a href="index.php?c=Compras&a=crear" class="btn-primary">Registrar Nueva Compra</a>

            <form action="index.php" method="GET" class="filter-form"
                style="display: inline-flex; gap: 10px; align-items: center; margin-left: 15px;">
                <input type="hidden" name="c" value="Compras">
                <input type="hidden" name="a" value="index">

                <label style="font-weight: 600;">Desde:</label>
                <input type="date" name="f_ini" value="<?= htmlspecialchars($_GET['f_ini'] ?? date('Y-11-01')) ?>"
                    style="padding: 8px; border-radius: 6px; border: 1px solid #ddd;">

                <label style="font-weight: 600;">Hasta:</label>
                <input type="date" name="f_fin" value="<?= htmlspecialchars($_GET['f_fin'] ?? date('Y-m-d')) ?>"
                    style="padding: 8px; border-radius: 6px; border: 1px solid #ddd;">

                <button type="submit" class="btn-primary"
                    style="padding: 8px 16px; min-width: auto; height: auto;">Filtrar</button>
            </form>
        </div>
    </div>

    <br>

    <table class="catalog-table" border="1">
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
                <tr>
                    <td colspan="5">No hay compras en este rango.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($compras as $c): ?>
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