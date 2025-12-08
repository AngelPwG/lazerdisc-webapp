<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Catálogo de Discos</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>
    <h1>Catálogo de Discos</h1>

    <!-- Mensajes de feedback -->
    <?php if (isset($_GET['msg'])): ?>
        <p><strong><?= htmlspecialchars($_GET['msg']) ?></strong></p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p><strong>Error: <?= htmlspecialchars($_GET['error']) ?></strong></p>
    <?php endif; ?>

    <div>
        <a href="index.php?c=Discos&a=crear">Agregar Nuevo Disco</a>
    </div>

    <!-- Buscador sencillo -->
    <form action="index.php" method="GET">
        <input type="hidden" name="c" value="Discos">
        <input type="hidden" name="a" value="index">
        <input type="text" name="q" placeholder="Buscar por título, artista o código..."
            value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
        <button type="submit">Buscar</button>
    </form>

    <br>

    <table border="1">
        <thead>
            <tr>
                <th>Código</th>
                <th>Portada</th>
                <th>Título</th>
                <th>Artista</th>
                <th>Disquera</th>
                <th>Géneros</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listaDiscos)): ?>
                <tr>
                    <td colspan="9">No se encontraron discos.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($listaDiscos as $disco): ?>
                    <tr>
                        <td><?= htmlspecialchars($disco['codigo_barras']) ?></td>
                        <td>
                            <?php if ($disco['imagen_base64']): ?>
                                <img src="data:image/jpeg;base64,<?= $disco['imagen_base64'] ?>" width="50" alt="Portada">
                            <?php else: ?>
                                Sin imagen
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($disco['titulo']) ?></td>
                        <td><?= htmlspecialchars($disco['nombre_artista']) ?></td>
                        <td><?= htmlspecialchars($disco['nombre_disquera'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($disco['lista_generos'] ?? '') ?></td>
                        <td><?= number_format($disco['precio_venta'], 2) ?></td>
                        <td><?= $disco['stock'] ?? 0 ?></td>
                        <td>
                            <a href="index.php?c=Discos&a=editar&id=<?= $disco['id_disco'] ?>">Editar</a>
                            <!-- Placeholder buttons for future functionality -->
                            <!-- <a href="index.php?c=Discos&a=eliminar&id=<?= $disco['id_disco'] ?>">Eliminar</a> -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>