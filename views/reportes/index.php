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
    <div class="page-header">
        <h1 class="page-title">Generación de Reportes</h1>
    </div>

    <div class="main-grid-container">
        <!-- Selector de Reporte -->
        <fieldset class="form-section" style="grid-column: 1 / -1; width: 100%;">
            <legend class="section-title">Selección de Reporte</legend>
            <div class="card-body">
                <?php
                $tipoPreseleccionado = $_GET['tipo'] ?? '';
                $esCorte = ($tipoPreseleccionado === 'corte');
                ?>
                <div class="form-group">
                    <label for="tipoReporte"><strong>Tipo de Reporte:</strong></label>
                    <?php if ($esCorte): ?>
                            <select id="tipoReporte" disabled style="background-color: #eee; width: 100%;">
                                <option value="corte" selected>Cierre de Caja</option>
                            </select>
                            <input type="hidden" id="tipoReporteHidden" value="corte">
                    <?php else: ?>
                            <select id="tipoReporte" onchange="mostrarReporte()" style="width: 100%;">
                                <option value="">-- Seleccione una opción --</option>
                                <option value="inventario">Inventario Actual</option>
                                <option value="ventas">Reporte de Ventas</option>
                                <option value="ventas_detalle">Detalle de Ventas</option>
                                <option value="compras">Reporte de Compras</option>
                                <option value="corte">Cierre de Caja</option>
                            </select>
                    <?php endif; ?>
                </div>
            </div>
        </fieldset>

        <!-- Secciones de reportes (ocultas por defecto) -->
        
        <!-- INVENTARIO -->
        <div id="reporte_inventario" class="reporte-section" style="display:none; grid-column: 1 / -1;">
            <fieldset class="form-section">
                <legend class="section-title">Filtros de Inventario</legend>
                <div class="card-body">
                    <form action="index.php" method="GET" class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: end;">
                        <input type="hidden" name="c" value="Reportes">
                        <input type="hidden" name="a" value="generar">
                        <input type="hidden" name="tipo" value="inventario">

                        <div class="form-group">
                            <label>Buscar:</label>
                            <input type="text" name="q" placeholder="Producto...">
                        </div>

                        <div class="form-group" style="display: flex; align-items: center; padding-bottom: 12px;">
                            <label style="cursor: pointer;">
                                <input type="checkbox" name="activos" value="1" checked style="width: auto; margin-right: 8px;"> Solo Activos
                            </label>
                        </div>

                        <div class="form-actions" style="grid-column: 1 / -1; display: flex; gap: 10px; margin-top: 10px;">
                            <button type="submit" name="formato" value="json" class="btn-primary">Ver Reporte</button>
                            <button type="submit" name="formato" value="csv" class="btn-primary box-shadow-none" style="background-color: #28a745; border-color: #28a745;">Exportar CSV</button>
                            <button type="submit" name="formato" value="print" formtarget="_blank" class="btn-primary-outline">🖨️ Imprimir</button>
                        </div>
                    </form>
                </div>
            </fieldset>
        </div>

        <!-- VENTAS -->
        <div id="reporte_ventas" class="reporte-section" style="display:none; grid-column: 1 / -1;">
            <fieldset class="form-section">
                <legend class="section-title">Filtros de Ventas</legend>
                <div class="card-body">
                    <form action="index.php" method="GET" class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <input type="hidden" name="c" value="Reportes">
                        <input type="hidden" name="a" value="generar">
                        <input type="hidden" name="tipo" value="ventas">

                        <div class="form-group">
                            <label>Desde:</label>
                            <input type="date" name="f_ini" required value="<?= date('Y-m-01') ?>">
                        </div>

                        <div class="form-group">
                            <label>Hasta:</label>
                            <input type="date" name="f_fin" required value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="form-actions" style="grid-column: 1 / -1; display: flex; gap: 10px; margin-top: 10px;">
                            <button type="submit" name="formato" value="json" class="btn-primary">Ver Reporte</button>
                            <button type="submit" name="formato" value="csv" class="btn-primary" style="background-color: #28a745; border-color: #28a745;">Exportar CSV</button>
                            <button type="submit" name="formato" value="print" formtarget="_blank" class="btn-primary-outline">🖨️ Imprimir</button>
                        </div>
                    </form>
                </div>
            </fieldset>
        </div>

        <!-- VENTAS DETALLE -->
        <div id="reporte_ventas_detalle" class="reporte-section" style="display:none; grid-column: 1 / -1;">
            <fieldset class="form-section">
                <legend class="section-title">Detalle de Ventas</legend>
                <div class="card-body">
                    <form action="index.php" method="GET" class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <input type="hidden" name="c" value="Reportes">
                        <input type="hidden" name="a" value="generar">
                        <input type="hidden" name="tipo" value="ventas_detalle">

                        <div class="form-group">
                            <label>Desde:</label>
                            <input type="date" name="f_ini" required value="<?= date('Y-m-01') ?>">
                        </div>

                        <div class="form-group">
                            <label>Hasta:</label>
                            <input type="date" name="f_fin" required value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="form-actions" style="grid-column: 1 / -1; display: flex; gap: 10px; margin-top: 10px;">
                            <button type="submit" name="formato" value="json" class="btn-primary">Ver Reporte</button>
                            <button type="submit" name="formato" value="csv" class="btn-primary" style="background-color: #28a745; border-color: #28a745;">Exportar CSV</button>
                            <button type="submit" name="formato" value="print" formtarget="_blank" class="btn-primary-outline">🖨️ Imprimir</button>
                        </div>
                    </form>
                </div>
            </fieldset>
        </div>

        <!-- COMPRAS -->
        <div id="reporte_compras" class="reporte-section" style="display:none; grid-column: 1 / -1;">
            <fieldset class="form-section">
                <legend class="section-title">Reporte de Compras</legend>
                <div class="card-body">
                    <form action="index.php" method="GET" class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <input type="hidden" name="c" value="Reportes">
                        <input type="hidden" name="a" value="generar">
                        <input type="hidden" name="tipo" value="compras">

                        <div class="form-group">
                            <label>Desde:</label>
                            <input type="date" name="f_ini" required value="<?= date('Y-m-01') ?>">
                        </div>

                        <div class="form-group">
                            <label>Hasta:</label>
                            <input type="date" name="f_fin" required value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="form-actions" style="grid-column: 1 / -1; display: flex; gap: 10px; margin-top: 10px;">
                            <button type="submit" name="formato" value="json" class="btn-primary">Ver Reporte</button>
                            <button type="submit" name="formato" value="csv" class="btn-primary" style="background-color: #28a745; border-color: #28a745;">Exportar CSV</button>
                            <button type="submit" name="formato" value="print" formtarget="_blank" class="btn-primary-outline">🖨️ Imprimir</button>
                        </div>
                    </form>
                </div>
            </fieldset>
        </div>

        <!-- CORTE -->
        <div id="reporte_corte" class="reporte-section" style="display:none; grid-column: 1 / -1;">
            <fieldset class="form-section">
                <legend class="section-title">Cierre de Caja</legend>
                <div class="card-body">
                    <form action="index.php" method="GET" class="form-grid" style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                        <input type="hidden" name="c" value="Reportes">
                        <input type="hidden" name="a" value="generar">
                        <input type="hidden" name="tipo" value="corte">

                        <div class="form-group">
                            <label>Fecha de Corte:</label>
                            <input type="date" name="f_ini" required value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="form-actions" style="grid-column: 1 / -1; display: flex; gap: 10px; margin-top: 10px;">
                            <button type="submit" name="formato" value="json" class="btn-primary">Ver Corte</button>
                            <button type="submit" name="formato" value="csv" class="btn-primary" style="background-color: #28a745; border-color: #28a745;">Exportar CSV</button>
                            <button type="submit" name="formato" value="print" formtarget="_blank" class="btn-primary-outline">🖨️ Imprimir</button>
                        </div>
                    </form>
                </div>
            </fieldset>
        </div>
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
