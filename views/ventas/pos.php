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

    <script>
        // Foco automático siempre en el input
        const codigoInput = document.getElementById('codigo');
        document.addEventListener('click', () => codigoInput.focus());

        codigoInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Evitar submit de form si lo hubiera
                agregarProducto();
            }
        });

        function mostrarMensaje(msg, tipo) {
            const div = document.getElementById('mensaje');
            div.innerText = msg;
            div.className = tipo === 'error' ? 'msg-error' : 'msg-success';
            div.style.display = 'block';
            setTimeout(() => { div.style.display = 'none'; }, 3000);
        }

        function agregarProducto() {
            const codigo = codigoInput.value.trim();
            if (!codigo) return;

            fetch('index.php?c=Ventas&a=agregar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'codigo=' + encodeURIComponent(codigo)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderCarrito(data.carrito);
                        codigoInput.value = '';
                        mostrarMensaje('Producto agregado', 'success');
                    } else {
                        mostrarMensaje(data.msg, 'error');
                        codigoInput.select();
                    }
                })
                .catch(err => console.error(err));
        }

        function eliminarProducto(id_disco) {
            fetch('index.php?c=Ventas&a=eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_disco=' + encodeURIComponent(id_disco)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderCarrito(data.carrito);
                    }
                });
        }

        function renderCarrito(carrito) {
            const tbody = document.getElementById('tabla-carrito');
            tbody.innerHTML = '';
            let total = 0;
            let items = 0;

            // Ordenar carrito para que lo último agregado aparezca arriba (opcional)
            // carrito.reverse();

            if (carrito && carrito.length > 0) {
                carrito.forEach(item => {
                    total += parseFloat(item.subtotal);
                    items++;
                    tbody.innerHTML += `
                        <tr>
                            <td>${item.codigo}</td>
                            <td>${item.titulo}</td>
                            <td>$${parseFloat(item.precio_venta).toFixed(2)}</td>
                            <td>${item.cantidad}</td>
                            <td>$${parseFloat(item.subtotal).toFixed(2)}</td>
                            <td>
                                <button class="btn-remove" onclick="eliminarProducto('${item.id_disco}')">X</button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#999;">Carrito vacío</td></tr>';
            }

            document.getElementById('total-venta').innerText = total.toFixed(2);
            document.getElementById('total-items').innerText = items;
        }

        function confirmarVenta() {
            // Verificar si hay items
            const total = parseFloat(document.getElementById('total-venta').innerText);
            if (total <= 0) {
                mostrarMensaje('El carrito está vacío', 'error');
                return;
            }

            if (!confirm('¿Confirmar venta por $' + total.toFixed(2) + '?')) return;

            fetch('index.php?c=Ventas&a=confirmar')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Venta realizada con éxito. Folio: ' + data.folio);
                        window.open('index.php?c=Ventas&a=ticket&id=' + data.id_venta, '_blank');
                        location.reload();
                    } else {
                        alert('Error: ' + data.msg);
                    }
                });
        }

        function cancelarVenta() {
            if (!confirm('¿Seguro que desea vaciar el carrito?')) return;

            fetch('index.php?c=Ventas&a=cancelar')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderCarrito([]);
                        mostrarMensaje('Venta cancelada', 'success');
                    }
                });
        }
    </script>
</body>

</html>