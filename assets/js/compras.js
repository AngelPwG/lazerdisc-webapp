// Gestión de filas de productos
let itemCount = 0;

// Agregar la primera fila automáticamente al cargar
window.addEventListener('DOMContentLoaded', () => {
    agregarFila();
});

function agregarFila() {
    itemCount++;
    const div = document.createElement('div');
    div.className = 'item-row';
    div.id = `item-${itemCount}`;
    div.innerHTML = `
        <label>Código de Barras:</label>
        <input type="text" class="codigo_barras" data-item="${itemCount}" placeholder="Escanee o ingrese código" style="width: 200px;" onblur="validarProducto(this)">
        <span class="producto-nombre" id="nombre-${itemCount}"></span>
        
        <label style="margin-left: 15px;">Cantidad:</label>
        <input type="number" class="cantidad" data-item="${itemCount}" value="1" min="1" step="1" style="width: 80px;" 
            oninput="validarCantidadPositiva(this)">
        
        <label style="margin-left: 15px;">Costo Unitario:</label>
        <input type="number" step="0.01" class="costo" data-item="${itemCount}" placeholder="0.00" min="0.01" style="width: 100px;" 
            oninput="validarCosto(this)">
        
        ${itemCount > 1 ? '<button class="btn-remove" onclick="this.parentElement.remove()">X</button>' : ''}
    `;
    document.getElementById('items-compra').appendChild(div);

    // Enfocar el campo de código de barras automáticamente
    div.querySelector('.codigo_barras').focus();
}

// Validación de productos por código de barras
function validarProducto(input) {
    const itemId = input.dataset.item;
    const codigo = input.value.trim();
    const nombreSpan = document.getElementById(`nombre-${itemId}`);
    const costoInput = document.querySelector(`.costo[data-item="${itemId}"]`);

    if (!codigo) {
        nombreSpan.textContent = '';
        nombreSpan.className = '';
        return;
    }

    // Realizar petición AJAX para validar el producto
    fetch(`index.php?c=Compras&a=validarProducto&codigo=${encodeURIComponent(codigo)}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                nombreSpan.textContent = `✓ ${data.producto.titulo}`;
                nombreSpan.className = 'producto-info';

                // Autocompletar el costo con costo_promedio
                if (data.producto.costo_promedio) {
                    costoInput.value = parseFloat(data.producto.costo_promedio).toFixed(2);
                }
            } else {
                nombreSpan.textContent = `✗ ${data.message}`;
                nombreSpan.className = 'producto-error';
                costoInput.value = '';
            }
        })
        .catch(err => {
            nombreSpan.textContent = '✗ Error al validar producto';
            nombreSpan.className = 'producto-error';
        });
}

// Guardar compra
function guardarCompra() {
    const idProv = document.getElementById('id_proveedor').value;
    const rows = document.querySelectorAll('.item-row');

    if (!idProv) {
        alert('Seleccione un proveedor');
        return;
    }

    let detalles = [];
    let total = 0;

    // Primero validar todos los productos
    for (let row of rows) {
        const codigo = row.querySelector('.codigo_barras').value.trim();
        const cant = row.querySelector('.cantidad').value;
        const costo = row.querySelector('.costo').value;
        const nombreSpan = row.querySelector('.producto-nombre');

        // Si hay datos ingresados, validar
        if (codigo || cant || costo) {
            // Verificar que todos los campos estén completos
            if (!codigo || !cant || !costo) {
                alert('Todos los campos del producto deben estar completos');
                return;
            }

            // Verificar que el producto sea válido (si hay span de validación)
            if (nombreSpan) {
                if (nombreSpan.className === 'producto-error') {
                    alert(`El código "${codigo}" no es válido. Por favor verifique.`);
                    return;
                }

                // Verificar que haya sido validado
                if (nombreSpan.className !== 'producto-info') {
                    alert(`El código "${codigo}" no ha sido validado. Presione Tab después de ingresar el código.`);
                    return;
                }
            }

            detalles.push({
                codigo_barras: codigo,
                cantidad: parseInt(cant),
                costo: parseFloat(costo)
            });
            total += (parseInt(cant) * parseFloat(costo));
        }
    }

    if (detalles.length === 0) {
        alert('Agregue al menos un producto');
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
                window.location.href = 'index.php?c=Compras&a=index';
            } else {
                alert('Error: ' + res.message);
            }
        })
        .catch(err => {
            alert('Error al guardar: ' + err);
        });
}
