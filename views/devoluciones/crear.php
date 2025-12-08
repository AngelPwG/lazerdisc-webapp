<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Devolución</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>

    <h1>Nueva Devolución</h1>
    <a href="index.php?c=Devoluciones&a=index">Volver al listado</a>
    <hr>

    <div id="form-devolucion">
        <label>Folio Venta:</label>
        <input type="text" id="folio_venta" placeholder="Ej. V-2023..." autocomplete="off">

        <div id="items-devolucion" style="margin-top:20px;">
            <div class="item-row">
                <input type="text" class="codigo_barras" placeholder="Código de Barras">
                <input type="number" class="cantidad" placeholder="Cantidad">
                <input type="number" class="precio" placeholder="Precio Unitario (Reembolso)">
            </div>
        </div>
        <br>
        <button onclick="agregarFila()">+ Agregar Item</button>

        <br><br>
        <label>Motivo:</label>
        <input type="text" id="motivo" placeholder="Motivo de la devolución" style="width: 300px;">
        <br><br>

        <button onclick="guardarDevolucion()">Guardar Devolución</button>
    </div>

    <script>
        function agregarFila() {
            const div = document.createElement('div');
            div.className = 'item-row';
            div.style.marginTop = '5px';
            div.innerHTML = `
                <input type="text" class="codigo_barras" placeholder="Código de Barras">
                <input type="number" class="cantidad" placeholder="Cantidad">
                <input type="number" class="precio" placeholder="Precio Unitario (Reembolso)">
                <button onclick="this.parentElement.remove()">X</button>
            `;
            document.getElementById('items-devolucion').appendChild(div);
        }

        function guardarDevolucion() {
            const folioVenta = document.getElementById('folio_venta').value.trim();
            const motivo = document.getElementById('motivo').value.trim();
            const rows = document.querySelectorAll('.item-row');

            let detalles = [];
            let total = 0;

            rows.forEach(row => {
                const codigo = row.querySelector('.codigo_barras').value.trim();
                const cant = row.querySelector('.cantidad').value;
                const precio = row.querySelector('.precio').value; // Precio unitario a reembolsar

                if (codigo && cant) {
                    detalles.push({
                        codigo_barras: codigo,
                        cantidad: cant
                    });
                    total += (cant * precio);
                }
            });

            if (!folioVenta || detalles.length === 0) {
                alert('Faltan datos (Folio o Items)');
                return;
            }

            const data = {
                folio_venta: folioVenta,
                motivo: motivo,
                total: total,
                detalles: detalles
            };

            fetch('index.php?c=Devoluciones&a=guardar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        alert('Devolución registrada ID: ' + res.id_devolucion);
                        window.location.href = 'index.php?c=Devoluciones&a=index';
                    } else {
                        alert('Error: ' + res.message);
                    }
                });
        }
    </script>
</body>

</html>