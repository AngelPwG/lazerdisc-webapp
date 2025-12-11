<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>
    
    <!-- Mostrar alerta si existe un mensaje de error (desde el controlador) -->
    <?php if (isset($error_message)): ?>
        <script>
            alert("<?= addslashes($error_message) ?>");
        </script>
    <?php endif; ?>

    <h1>Generación de Reportes</h1>

    <?php
    $tipoPreseleccionado = $_GET['tipo'] ?? '';
    $esCorte = ($tipoPreseleccionado === 'corte');
    
    // Funciones helper para mantener valores del formulario (sticky form)
    function val($name, $default = '') {
        return isset($_GET[$name]) ? htmlspecialchars($_GET[$name]) : $default;
    }
    
    function sel($current, $value) {
        return $current === $value ? 'selected' : '';
    }
    ?>

    <div style="margin-bottom: 20px;">
        <label for="tipoReporte">Seleccione el tipo de reporte:</label>
        
        <?php if ($esCorte): ?>
            <!-- Si es corte, mostramos un select deshabilitado visualmente pero funcional (o un input hidden) -->
            <select id="tipoReporte" disabled style="background-color: #eee;">
                <option value="corte" selected>Cierre de Caja</option>
            </select>
            <!-- Input hidden para que JS (si lo necesitara) o forms lo procesen, aunque aquí el JS lee el select -->
            <input type="hidden" id="tipoReporteHidden" value="corte">
        <?php else: ?>
            <select id="tipoReporte" onchange="mostrarReporte()">
                <option value="">-- Seleccione una opción --</option>
                <option value="inventario" <?= sel($tipoPreseleccionado, 'inventario') ?>>Inventario Actual</option>
                <option value="ventas" <?= sel($tipoPreseleccionado, 'ventas') ?>>Reporte de Ventas</option>
                <option value="ventas_detalle" <?= sel($tipoPreseleccionado, 'ventas_detalle') ?>>Detalle de Ventas</option>
                <option value="compras" <?= sel($tipoPreseleccionado, 'compras') ?>>Reporte de Compras</option>
                <option value="corte" <?= sel($tipoPreseleccionado, 'corte') ?>>Cierre de Caja</option>
            </select>
        <?php endif; ?>
    </div>

    <!-- Secciones de reportes (ocultas por defecto) -->
    <div id="reporte_inventario" class="reporte-section" style="display:none;">
        <section>
            <h2>Inventario Actual</h2>
            <form action="index.php" method="GET">
                <input type="hidden" name="c" value="Reportes">
                <input type="hidden" name="a" value="generar">
                <input type="hidden" name="tipo" value="inventario">

                <label>Buscar:</label>
                <input type="text" name="q" placeholder="Producto..." value="<?= val('q') ?>">

                <label>
                    <input type="checkbox" name="activos" value="1" <?= isset($_GET['activos']) || !isset($_GET['c']) ? 'checked' : '' ?>> Solo Activos
                </label>

                <button type="submit" name="formato" value="csv" formtarget="_blank">Exportar CSV</button>
                <button type="submit" name="formato" value="print" formtarget="_blank">🖨️ Imprimir</button>
            </form>
        </section>
    </div>

    <div id="reporte_ventas" class="reporte-section" style="display:none;">
        <hr>
        <section>
            <h2>Reporte de Ventas</h2>
            <form action="index.php" method="GET">
                <input type="hidden" name="c" value="Reportes">
                <input type="hidden" name="a" value="generar">
                <input type="hidden" name="tipo" value="ventas">

                <label>Desde:</label>
                <input type="date" name="f_ini" required value="<?= val('f_ini', date('Y-m-01')) ?>">

                <label>Hasta:</label>
                <input type="date" name="f_fin" required value="<?= val('f_fin', date('Y-m-d')) ?>">

                <button type="submit" name="formato" value="csv" formtarget="_blank">Exportar CSV</button>
                <button type="submit" name="formato" value="print" formtarget="_blank">🖨️ Imprimir</button>
            </form>
        </section>
    </div>

    <div id="reporte_ventas_detalle" class="reporte-section" style="display:none;">
        <hr>
        <section>
            <h2>Detalle de Ventas</h2>
            <form action="index.php" method="GET">
                <input type="hidden" name="c" value="Reportes">
                <input type="hidden" name="a" value="generar">
                <input type="hidden" name="tipo" value="ventas_detalle">

                <label>Desde:</label>
                <input type="date" name="f_ini" required value="<?= val('f_ini', date('Y-m-01')) ?>">

                <label>Hasta:</label>
                <input type="date" name="f_fin" required value="<?= val('f_fin', date('Y-m-d')) ?>">

                <button type="submit" name="formato" value="csv" formtarget="_blank">Exportar CSV</button>
                <button type="submit" name="formato" value="print" formtarget="_blank">🖨️ Imprimir</button>
            </form>
        </section>
    </div>

    <div id="reporte_compras" class="reporte-section" style="display:none;">
        <hr>
        <section>
            <h2>Reporte de Compras</h2>
            <form action="index.php" method="GET">
                <input type="hidden" name="c" value="Reportes">
                <input type="hidden" name="a" value="generar">
                <input type="hidden" name="tipo" value="compras">

                <label>Desde:</label>
                <input type="date" name="f_ini" required value="<?= val('f_ini', date('Y-m-01')) ?>">

                <label>Hasta:</label>
                <input type="date" name="f_fin" required value="<?= val('f_fin', date('Y-m-d')) ?>">

                <button type="submit" name="formato" value="csv" formtarget="_blank">Exportar CSV</button>
                <button type="submit" name="formato" value="print" formtarget="_blank">🖨️ Imprimir</button>
            </form>
        </section>
    </div>

    <div id="reporte_corte" class="reporte-section" style="display:none;">
        <hr>
        <section>
            <h2>Cierre de Caja</h2>
            <form action="index.php" method="GET">
                <input type="hidden" name="c" value="Reportes">
                <input type="hidden" name="a" value="generar">
                <input type="hidden" name="tipo" value="corte">

                <label>Fecha de Corte:</label>
                <input type="date" name="f_ini" required value="<?= val('f_ini', date('Y-m-d')) ?>">

                <button type="submit" name="formato" value="csv">Exportar CSV</button>
                <button type="submit" name="formato" value="print" formtarget="_blank">🖨️ Imprimir</button>
            </form>
        </section>
    </div>

    <script>
        function mostrarReporte() {
            // Ocultar todas las secciones
            var secciones = document.getElementsByClassName('reporte-section');
            for (var i = 0; i < secciones.length; i++) {
                secciones[i].style.display = 'none';
            }

            // Obtener el valor seleccionado
            var select = document.getElementById('tipoReporte');
            // Si está disabled, tomamos su valor (o del hidden si fuera necesario, pero disabled select tiene valor)
            var seleccion = select.value;

            // Mostrar la sección correspondiente si hay una seleccionada
            if (seleccion) {
                var idSeccion = 'reporte_' + seleccion;
                var seccion = document.getElementById(idSeccion);
                if (seccion) {
                    seccion.style.display = 'block';
                }
            }
        }

        // Ejecutar al cargar si hay algo preseleccionado
        window.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('tipoReporte');
            if (select && select.value) {
                mostrarReporte();
            }
        });
    </script>
</body>

</html>
