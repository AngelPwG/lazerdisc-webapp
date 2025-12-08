<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>
    <h1>Generación de Reportes</h1>

    <section>
        <h2>Inventario Actual</h2>
        <form action="index.php" method="GET">
            <input type="hidden" name="c" value="Reportes">
            <input type="hidden" name="a" value="generar">
            <input type="hidden" name="tipo" value="inventario">

            <label>Buscar:</label>
            <input type="text" name="q" placeholder="Producto...">

            <label>
                <input type="checkbox" name="activos" value="1" checked> Solo Activos
            </label>

            <button type="submit" name="formato" value="json">Ver JSON</button>
            <button type="submit" name="formato" value="csv">Exportar CSV</button>
        </form>
    </section>

    <hr>

    <section>
        <h2>Reporte de Ventas</h2>
        <form action="index.php" method="GET">
            <input type="hidden" name="c" value="Reportes">
            <input type="hidden" name="a" value="generar">
            <input type="hidden" name="tipo" value="ventas">

            <label>Desde:</label>
            <input type="date" name="f_ini" required value="<?= date('Y-m-01') ?>">

            <label>Hasta:</label>
            <input type="date" name="f_fin" required value="<?= date('Y-m-d') ?>">

            <button type="submit" name="formato" value="json">Ver JSON</button>
            <button type="submit" name="formato" value="csv">Exportar CSV</button>
        </form>
    </section>

    <hr>

    <section>
        <h2>Detalle de Ventas</h2>
        <form action="index.php" method="GET">
            <input type="hidden" name="c" value="Reportes">
            <input type="hidden" name="a" value="generar">
            <input type="hidden" name="tipo" value="ventas_detalle">

            <label>Desde:</label>
            <input type="date" name="f_ini" required value="<?= date('Y-m-01') ?>">

            <label>Hasta:</label>
            <input type="date" name="f_fin" required value="<?= date('Y-m-d') ?>">

            <button type="submit" name="formato" value="json">Ver JSON</button>
            <button type="submit" name="formato" value="csv">Exportar CSV</button>
        </form>
    </section>

    <hr>

    <section>
        <h2>Reporte de Compras</h2>
        <form action="index.php" method="GET">
            <input type="hidden" name="c" value="Reportes">
            <input type="hidden" name="a" value="generar">
            <input type="hidden" name="tipo" value="compras">

            <label>Desde:</label>
            <input type="date" name="f_ini" required value="<?= date('Y-m-01') ?>">

            <label>Hasta:</label>
            <input type="date" name="f_fin" required value="<?= date('Y-m-d') ?>">

            <button type="submit" name="formato" value="json">Ver JSON</button>
            <button type="submit" name="formato" value="csv">Exportar CSV</button>
        </form>
    </section>
</body>

</html>