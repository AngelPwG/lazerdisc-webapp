<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Catálogo de Discos</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>
    <div class="page-header">
        <h1 class="page-title">Catálogo de Discos</h1>

    <!-- Mensajes de feedback -->
    <?php if (isset($_GET['msg'])): ?>
        <p><strong><?= htmlspecialchars($_GET['msg']) ?></strong></p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p><strong>Error: <?= htmlspecialchars($_GET['error']) ?></strong></p>
    <?php endif; ?>

    <div class="page-actions">
        <?php if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'admin'): ?>
            <a href="index.php?c=Discos&a=crear" class="btn-primary">Agregar Nuevo Disco</a>

            <!-- Filtro para mostrar todos (Activos e Inactivos) -->
            <form action="index.php" method="GET" class="filter-form">
                <input type="hidden" name="c" value="Discos">
                <input type="hidden" name="a" value="index">
                <label>
                    <input type="checkbox" name="mostrar_todos" value="1" <?= (isset($_GET['mostrar_todos']) && $_GET['mostrar_todos']) ? 'checked' : '' ?> onchange="this.form.submit()">
                    Mostrar Inactivos
                </label>
                <!-- Preservar búsqueda al cambiar el filtro -->
                <?php if (isset($_GET['q'])): ?>
                    <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q']) ?>">
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>

    <!-- Buscador sencillo -->
    <form action="index.php" method="GET" class="search-bar">
        <input type="hidden" name="c" value="Discos">
        <input type="hidden" name="a" value="index">
        <!-- Preservar filtro al buscar -->
        <?php if (isset($_GET['mostrar_todos'])): ?>
            <input type="hidden" name="mostrar_todos" value="1">
        <?php endif; ?>

        <input type="text" name="q" placeholder="Buscar por título, artista o código..."
            value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
        <button type="submit" class="btn-primary">Buscar</button>
    </form>
    </div>

    <br>

    <table class="catalog-table" border="1">
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
                <th>Estado</th>
                <?php if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'admin'): ?>
                    <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listaDiscos)): ?>
                <tr>
                    <td colspan="10">No se encontraron discos.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($listaDiscos as $disco): ?>
                    <tr style="<?= $disco['activo'] == 0 ? 'background-color: #fce4e4; color: #888;' : '' ?>">
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
                            <?php if ($disco['activo'] == 1): ?>
                                <span class="status status-active">Activo</span>
                            <?php else: ?>
                                <span class="status status-inactive">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <?php if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'admin'): ?>
                            <td>
                                <a class="action-link edit" href="index.php?c=Discos&a=editar&id=<?= $disco['id_disco'] ?>">Editar</a>
                                |
                                <?php if ($disco['activo'] == 1): ?>
                                    <a class="action-link disable" href="index.php?c=Discos&a=cambiarEstado&id=<?= $disco['id_disco'] ?>&estado=0"
                                        onclick="return confirm('¿Estás seguro de que deseas desactivar este producto? No será visible para ventas.')"
                                        style="color: red;">Desactivar</a>
                                <?php else: ?>
                                    <a class="action-link enable" href="index.php?c=Discos&a=cambiarEstado&id=<?= $disco['id_disco'] ?>&estado=1"
                                        onclick="return confirm('¿Deseas activar este producto nuevamente?')"
                                        style="color: green;">Activar</a>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>