<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Compra</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>

    <h1>Registrar Nueva Compra</h1>
    <a href="index.php?c=Compras&a=index">Volver al listado</a>
    <hr>

    <div id="form-compra">
        <label>Proveedor:</label>
        <select id="id_proveedor">
            <option value="">Seleccione un proveedor...</option>
            <?php foreach ($proveedores as $p): ?>
                <option value="<?= $p['id_proveedor'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <div id="items-compra" style="margin-top:20px;">
            <div class="item-row">
                <input type="text" class="codigo_barras" placeholder="Código de Barras">
                <input type="number" class="cantidad" placeholder="Cantidad">
                <input type="number" class="costo" placeholder="Costo Unitario">
            </div>
        </div>
        <br>
        <button onclick="agregarFila()">+ Agregar Item</button>
        <br><br>
        <button onclick="guardarCompra()">Registrar Compra</button>
    </div>

    <script>
        function agregarFila() {
            const div = document.createElement('div');
            div.className = 'item-row';
            div.style.marginTop = '5px';
            div.innerHTML = `
                <input type="text" class="codigo_barras" placeholder="Código de Barras">
                <input type="number" class="cantidad" placeholder="Cantidad">
                <input type="number" class="costo" placeholder="Costo Unitario">
                <button onclick="this.parentElement.remove()">X</button>
            `;
            document.getElementById('items-compra').appendChild(div);
        }

        function guardarCompra() {
            const idProv = document.getElementById('id_proveedor').value;
            const rows = document.querySelectorAll('.item-row');

            let detalles = [];
            let total = 0;

            rows.forEach(row => {
                const codigo = row.querySelector('.codigo_barras').value;
                const cant = row.querySelector('.cantidad').value;
                const costo = row.querySelector('.costo').value;

                if (codigo && cant && costo) {
                    detalles.push({
                        codigo_barras: codigo,
                        cantidad: cant,
                        costo: costo
                    });
                    total += (cant * costo);
                }
            });

            if (!idProv || detalles.length === 0) {
                alert('Faltan datos');
                return;
            }

            const data = {
                id_proveedor: idProv,
                total: total,
                detalles: detalles
            };

            fetch('index.php?c=Compras&a=guardar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        alert('Compra registrada correctamente. ID: ' + res.id_compra);
                        window.location.href = 'index.php?c=Compras&a=index'; // Redirección
                    } else {
                        alert('Error: ' + res.message);
                    }
                });
        }
    </script>
</body>

</html>