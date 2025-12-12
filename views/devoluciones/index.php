<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Devoluciones</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'views/includes/menu.php'; ?>

    <div class="page-header">
        <h1 class="page-title">Devoluciones</h1>

        <div class="page-actions">
            <a href="index.php?c=Devoluciones&a=crear" class="btn-primary">Registrar Nueva Devolución</a>
            
            <!-- Filtro de fechas -->
            <form action="index.php" method="GET" class="filter-form" style="display: inline-flex; gap: 10px; align-items: center; margin-left: 15px;">
                <input type="hidden" name="c" value="Devoluciones">
                <input type="hidden" name="a" value="index">
                
                <label style="font-weight: 600;">Desde:</label>
                <input type="date" name="f_ini" value="<?= htmlspecialchars($_GET['f_ini'] ?? date('Y-11-01')) ?>" style="padding: 8px; border-radius: 6px; border: 1px solid #ddd;">
                
                <label style="font-weight: 600;">Hasta:</label>
                <input type="date" name="f_fin" value="<?= htmlspecialchars($_GET['f_fin'] ?? date('Y-m-d')) ?>" style="padding: 8px; border-radius: 6px; border: 1px solid #ddd;">
                
                <button type="submit" class="btn-primary" style="padding: 8px 16px; min-width: auto; height: auto;">Filtrar</button>
            </form>
        </div>
    </div>

    <br>

    <!-- Lista de Devoluciones -->
    <table class="catalog-table" border="1">
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