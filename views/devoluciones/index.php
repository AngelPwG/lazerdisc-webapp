<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Devoluciones</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'views/includes/menu.php'; ?>

    <h1>Devoluciones</h1>
    
    <div style="margin-bottom: 10px;">
        <a href="index.php?c=Devoluciones&a=crear">Registrar Nueva Devolución</a>
    </div>

    <!-- Filtro de fechas -->
    <form action="index.php" method="GET">
        <input type="hidden" name="c" value="Devoluciones">
        <input type="hidden" name="a" value="index">
        
        <label>Desde:</label>
        <input type="date" name="f_ini" value="<?= htmlspecialchars($_GET['f_ini'] ?? date('Y-11-01')) ?>">
        
        <label>Hasta:</label>
        <input type="date" name="f_fin" value="<?= htmlspecialchars($_GET['f_fin'] ?? date('Y-m-d')) ?>">
        
        <button type="submit">Filtrar</button>
    </form>

    <br>

    <!-- Lista de Devoluciones -->
    <table border="1">
        <thead>
            <tr>
                <th>ID Devolución</th>
                <th>Folio Venta</th>
                <th>Usuario</th>
                <th>Fecha</th>
                <th>Total Reembolsado</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($devoluciones)): ?>
                <tr><td colspan="6">No hay devoluciones en este rango.</td></tr>
            <?php else: ?>
                <?php foreach($devoluciones as $d): ?>
                    <tr>
                        <td><?= $d['id_devolucion'] ?></td>
                        <td><?= $d['folio_venta'] ?></td> 
                        <td><?= $d['autorizo']?></td>
                        <td><?= $d['fecha_devolucion'] ?></td>
                        <td><?= number_format($d['total_reembolsado'], 2) ?></td>
                        <td><?= htmlspecialchars($d['motivo']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>