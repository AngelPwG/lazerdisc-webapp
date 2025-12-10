// Validaciones comunes para toda la aplicación

/**
 * Valida que un año esté entre 1900 y 2099
 * @param {HTMLInputElement} input - El input a validar
 */
function validarAnio(input) {
    const valor = parseInt(input.value);
    if (valor < 1900) {
        alert('❌ El año debe ser 1900 o posterior.');
        input.value = 1900;
    } else if (valor > 2099) {
        alert('❌ El año no puede ser mayor a 2099.');
        input.value = 2099;
    }
}

/**
 * Valida que el precio de venta sea mayor a 0
 * @param {HTMLInputElement} input - El input a validar
 */
function validarPrecio(input) {
    const valor = parseFloat(input.value);
    if (valor <= 0 || isNaN(valor)) {
        alert('❌ El precio de venta debe ser mayor a 0. No se permiten valores negativos o cero.');
        input.value = '';
    }
}

/**
 * Valida que el costo sea mayor a 0
 * @param {HTMLInputElement} input - El input a validar
 */
function validarCosto(input) {
    const valor = parseFloat(input.value);
    if (valor <= 0 || isNaN(valor)) {
        alert('❌ El costo debe ser mayor a 0. No se permiten valores negativos o cero.');
        input.value = '';
    }
}

/**
 * Valida que la cantidad sea un entero positivo
 * @param {HTMLInputElement} input - El input a validar
 */
function validarCantidadPositiva(input) {
    const valor = parseFloat(input.value);
    if (valor <= 0 || isNaN(valor)) {
        alert('❌ La cantidad debe ser mayor a 0. No se permiten valores negativos o cero.');
        input.value = 1;
    } else if (!Number.isInteger(valor)) {
        // Redondear a entero si se ingresó decimal
        input.value = Math.round(valor);
    }
}

/**
 * Valida cantidad en devoluciones con límite máximo
 * @param {HTMLInputElement} input - El input a validar
 * @param {number} max - Cantidad máxima permitida
 */
function validarCantidadDevolucion(input, max) {
    const valor = parseFloat(input.value);
    if (valor <= 0 || isNaN(valor)) {
        alert('❌ La cantidad debe ser mayor a 0. No se permiten valores negativos o cero.');
        input.value = 1;
        return;
    }
    if (!Number.isInteger(valor)) {
        input.value = Math.round(valor);
    }
    if (parseInt(input.value) > max) {
        input.value = max;
    }
}

/**
 * Valida cantidad con límite máximo (versión simplificada)
 * @param {HTMLInputElement} input - El input a validar
 * @param {number} max - Cantidad máxima permitida
 */
function validarCantidad(input, max) {
    if (parseInt(input.value) > max) {
        input.value = max;
        alert(`La cantidad máxima para este producto es ${max}`);
    }
    if (parseInt(input.value) < 1) {
        input.value = 1;
    }
}
