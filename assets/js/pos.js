// POS - Punto de Venta

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

                // Mostrar mensaje apropiado
                if (data.duplicado) {
                    mostrarMensaje('⚠️ ' + data.msg, 'success');
                } else {
                    mostrarMensaje('✓ ' + data.msg, 'success');
                }
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

    if (carrito && carrito.length > 0) {
        carrito.forEach(item => {
            total += parseFloat(item.subtotal);
            items++;
            tbody.innerHTML += `
                <tr>
                    <td>${item.codigo}</td>
                    <td>${item.titulo}</td>
                    <td>$${parseFloat(item.precio_venta).toFixed(2)}</td>
                    <td>
                        <input type="number" value="${item.cantidad}" min="1" step="1" 
                            style="width: 60px; text-align: center;" 
                            onchange="actualizarCantidad('${item.id_disco}', this.value)">
                    </td>
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

    // Calcular subtotal e IVA (asumiendo que el precio incluye IVA del 16%)
    const IVA_RATE = 0.16;
    const subtotal = total / (1 + IVA_RATE);
    const iva = total - subtotal;

    document.getElementById('subtotal-venta').innerText = subtotal.toFixed(2);
    document.getElementById('iva-venta').innerText = iva.toFixed(2);
    document.getElementById('total-venta').innerText = total.toFixed(2);
    document.getElementById('total-items').innerText = items;
}

function actualizarCantidad(id_disco, cantidad) {
    cantidad = parseInt(cantidad);
    if (cantidad < 1) {
        mostrarMensaje('La cantidad debe ser al menos 1', 'error');
        renderCarrito(JSON.parse(sessionStorage.getItem('carrito') || '[]'));
        return;
    }

    fetch('index.php?c=Ventas&a=actualizar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_disco=${encodeURIComponent(id_disco)}&cantidad=${cantidad}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                renderCarrito(data.carrito);
                sessionStorage.setItem('carrito', JSON.stringify(data.carrito));
            } else {
                mostrarMensaje(data.msg, 'error');
            }
        })
        .catch(err => console.error(err));
}

function confirmarVenta() {
    // Verificar si hay items
    const total = parseFloat(document.getElementById('total-venta').innerText);
    if (total <= 0) {
        mostrarMensaje('El carrito está vacío', 'error');
        return;
    }

    if (!confirm('¿Confirmar venta por $' + total.toFixed(2) + '?')) return;

    // Abrir ventana ANTES del fetch para evitar que el bloqueador de popups la bloquee
    // Abrimos con una página de espera o en blanco
    const ticketWindow = window.open('', '_blank');
    if (ticketWindow) {
        ticketWindow.document.write('<html><head><title>Generando ticket...</title></head><body style="font-family: Arial; text-align: center; padding-top: 50px;"><h2>Generando ticket...</h2><p>Por favor espere...</p></body></html>');
    }

    fetch('index.php?c=Ventas&a=confirmar')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Venta realizada con éxito. Folio: ' + data.folio);
                // Redirigir la ventana que ya abrimos
                if (ticketWindow) {
                    ticketWindow.location.href = 'index.php?c=Ventas&a=ticket&id=' + data.id_venta;
                }
                location.reload();
            } else {
                // Si hay error, cerrar la ventana que abrimos
                if (ticketWindow) {
                    ticketWindow.close();
                }
                alert('Error: ' + data.msg);
            }
        })
        .catch(err => {
            // En caso de error de red, también cerrar la ventana
            if (ticketWindow) {
                ticketWindow.close();
            }
            console.error(err);
            alert('Error de conexión al procesar la venta');
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
