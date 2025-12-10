// Variables globales
let ventaDetalles = [];

// Buscar venta por folio
function buscarVenta() {
    const folio = document.getElementById('folio_venta').value.trim();
    if (!folio) {
        alert('Ingrese un folio de venta');
        return;
    }

    fetch(`index.php?c=Devoluciones&a=obtenerDetalleVenta&folio=${encodeURIComponent(folio)}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                mostrarDetallesVenta(data.venta, data.detalles);
            } else {
                alert('Error: ' + data.message);
                document.getElementById('info-venta-container').style.display = 'none';
            }
        })
        .catch(err => {
            alert('Error al buscar venta: ' + err);
        });
}

// Mostrar detalles de la venta
function mostrarDetallesVenta(venta, detalles) {
    ventaDetalles = detalles;

    // Mostrar info de la venta
    document.getElementById('info-folio').textContent = venta.folio_venta;
    document.getElementById('info-fecha').textContent = venta.fecha_venta;
    document.getElementById('info-total').textContent = parseFloat(venta.total_venta).toFixed(2);

    // Llenar tabla de productos
    const tbody = document.getElementById('productos-lista');
    tbody.innerHTML = '';

    detalles.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="text-align: center;">
                <input type="checkbox" class="chk-producto" data-index="${index}" onchange="calcularTotal()">
            </td>
            <td>${item.titulo}</td>
            <td style="text-align: center;">${item.cantidad}</td>
            <td style="text-align: center;">
                <input type="number" class="cant-devolver" data-index="${index}" min="1" max="${item.cantidad}" value="1" step="1" 
                    oninput="validarCantidadDevolucion(this, ${item.cantidad})" 
                    onchange="validarCantidad(this, ${item.cantidad}); calcularTotal();">
            </td>
            <td style="text-align: right;">$${parseFloat(item.precio_unitario).toFixed(2)}</td>
            <td style="text-align: right;" class="subtotal-item" data-index="${index}">$0.00</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('info-venta-container').style.display = 'block';
    document.getElementById('tabla-productos').style.display = 'table';
}

// Calcular total de reembolso
function calcularTotal() {
    let totalReembolso = 0;
    const checkboxes = document.querySelectorAll('.chk-producto');

    checkboxes.forEach(chk => {
        const index = parseInt(chk.dataset.index);
        const cantInput = document.querySelector(`.cant-devolver[data-index="${index}"]`);
        const subtotalCell = document.querySelector(`.subtotal-item[data-index="${index}"]`);

        if (chk.checked) {
            const cantidad = parseInt(cantInput.value) || 0;
            const precioUnit = parseFloat(ventaDetalles[index].precio_unitario);
            const subtotal = cantidad * precioUnit;
            totalReembolso += subtotal;
            subtotalCell.textContent = `$${subtotal.toFixed(2)}`;
            cantInput.disabled = false;
        } else {
            subtotalCell.textContent = '$0.00';
            cantInput.disabled = true;
        }
    });

    document.getElementById('total-reembolso').textContent = totalReembolso.toFixed(2);
}

// Guardar devolución
function guardarDevolucion() {
    const folio = document.getElementById('folio_venta').value.trim();
    const motivo = document.getElementById('motivo').value.trim();
    const checkboxes = document.querySelectorAll('.chk-producto:checked');

    if (!motivo) {
        alert('❌ Debe ingresar un motivo para la devolución');
        document.getElementById('motivo').focus();
        return;
    }

    if (checkboxes.length === 0) {
        alert('Seleccione al menos un producto para devolver');
        return;
    }

    let detalles = [];
    let total = 0;

    checkboxes.forEach(chk => {
        const index = parseInt(chk.dataset.index);
        const cantidad = parseInt(document.querySelector(`.cant-devolver[data-index="${index}"]`).value);
        const item = ventaDetalles[index];

        detalles.push({
            id_disco: item.id_disco,
            cantidad: cantidad
        });

        total += cantidad * parseFloat(item.precio_unitario);
    });

    const data = {
        folio_venta: folio,
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
                alert('Devolución registrada correctamente. ID: ' + res.id_devolucion);
                window.location.href = 'index.php?c=Devoluciones&a=index';
            } else {
                alert('Error: ' + res.message);
            }
        })
        .catch(err => {
            alert('Error al guardar: ' + err);
        });
}
