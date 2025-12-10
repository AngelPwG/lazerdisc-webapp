<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta | LazerDisc</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>

    <div class="pos-container">
        <!-- Izquierda: Búsqueda y Tabla -->
        <div class="pos-left">
            <div class="pos-header">
                <h2><span style="color: var(--color-secondary-dark);">Punto de Venta</span></h2>
            </div>

            <div class="search-bar">
                <input type="text" id="codigo" placeholder="Escanear o ingresar código de barras..." autofocus
                    autocomplete="off">
                <button onclick="agregarProducto()">Agregar</button>
            </div>
            <div id="mensaje"></div>

            <div class="cart-table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%">Código</th>
                            <th style="width: 40%">Producto</th>
                            <th style="width: 15%">Precio</th>
                            <th style="width: 10%">Cant.</th>
                            <th style="width: 15%">Subtotal</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody id="tabla-carrito">
                        <!-- Items agregados via JS -->
                        <?php if (isset($_SESSION['carrito'])): ?>
                            <?php foreach ($_SESSION['carrito'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['codigo']) ?></td>
                                    <td><?= htmlspecialchars($item['titulo']) ?></td>
                                    <td>$<?= number_format($item['precio_venta'], 2) ?></td>
                                    <td><?= $item['cantidad'] ?></td>
                                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                                    <td>
                                        <button class="btn-remove"
                                            onclick="eliminarProducto('<?= $item['id_disco'] ?>')">X</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Derecha: Totales y Botones -->
        <div class="pos-right">
            <div class="totals-area">
                <div class="totals-card">
                    <h4>Total a Pagar</h4>
                    <div class="total-amount">$<span
                            id="total-venta"><?= isset($_SESSION['carrito']) ? number_format(array_sum(array_column($_SESSION['carrito'], 'subtotal')), 2) : '0.00' ?></span>
                    </div>
                    <small>Items: <span
                            id="total-items"><?= isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0 ?></span></small>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn-confirm" onclick="confirmarVenta()">Cobrar</button>
                <button class="btn-cancel" onclick="cancelarVenta()">Cancelar Venta</button>
            </div>
        </div>
    </div>

    <script src="assets/js/pos.js"></script>
</body>

</html>