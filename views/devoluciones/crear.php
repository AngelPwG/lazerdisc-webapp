<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Devolución</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>

    <div class="page-header">
        <h1 class="page-title">Nueva Devolución</h1>
        <div class="page-actions">
            <!-- Botón movido al final -->
        </div>
    </div>

    <div class="main-grid-container">
        <!-- SECCIÓN DE BÚSQUEDA -->
        <fieldset class="form-section" style="grid-column: 1 / -1; width: 100%;">
            <legend class="section-title">Buscar Venta</legend>
            <div class="card-body">
                <div id="form-devolucion">
                    <!-- Paso 1: Buscar Venta por Folio -->
                    <div class="form-group" style="display: flex; gap: 15px; align-items: flex-end;">
                        <div style="flex-grow: 1;">
                            <label><strong>Folio de Venta:</strong></label>
                            <input type="text" id="folio_venta" placeholder="Ej. V-20251209..." autocomplete="off">
                        </div>
                        <button onclick="buscarVenta()" class="btn-primary"
                            style="height: 48px; margin-bottom: 0;">Buscar Venta</button>
                    </div>

                    <!-- Paso 2: Mostrar Información de la Venta y Productos -->
                    <div id="info-venta-container" style="display:none; margin-top: 30px;">
                        <div class="info-venta"
                            style="background-color: #f8f9fa; padding: 20px; border-radius: 12px; border-left: 5px solid var(--color-secondary-dark); margin-bottom: 25px;">
                            <h3 style="margin-top: 0; color: var(--color-secondary-dark);">Detalles de la Venta</h3>
                            <div
                                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                                <p style="margin: 0;"><strong>Folio:</strong> <span id="info-folio"></span></p>
                                <p style="margin: 0;"><strong>Fecha:</strong> <span id="info-fecha"></span></p>
                                <p style="margin: 0;"><strong>Total Original:</strong> $<span id="info-total"></span>
                                </p>
                            </div>
                        </div>

                        <h3 class="section-title" style="margin-bottom: 15px; font-size: 1.1rem; padding-left: 5px;">
                            Seleccionar Productos a Devolver</h3>

                        <div
                            style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden;">
                            <table id="tabla-productos" class="catalog-table"
                                style="display: table; margin: 0; border: none;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px; text-align: center;">Devolver</th>
                                        <th>Producto</th>
                                        <th style="width: 120px; text-align: center;">Cant. Original</th>
                                        <th style="width: 140px;">Cant. a Devolver</th>
                                        <th style="width: 120px;">Precio Unit.</th>
                                        <th style="width: 120px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="productos-lista">
                                    <!-- Se llenará dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                        <div class="total-reembolso"
                            style="text-align: right; font-size: 1.2rem; margin: 25px 0; padding: 15px; background: #fff; border-radius: 10px; border: 1px solid #eee;">
                            Total a Reembolsar: <strong style="color: var(--color-accent);">$<span
                                    id="total-reembolso">0.00</span></strong>
                        </div>

                        <div class="form-group">
                            <label><strong>Motivo de Devolución:</strong></label>
                            <textarea id="motivo" rows="3"
                                style="width: 100%; padding: 12px; border: none; background: #eaeaea; border-radius: 10px; font-family: inherit;"
                                placeholder="Descripción del motivo..."></textarea>
                        </div>

                        <div style="margin-top: 25px; text-align: right;">
                            <button onclick="guardarDevolucion()" class="btn-save">Guardar Devolución</button>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Botones de Acción (Fuera de la tarjeta) -->
        <div class="form-actions" style="grid-column: 1 / -1; margin-top: 20px;">
            <?php if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'admin'): ?>
                <a href="index.php?c=Devoluciones&a=index" class="btn-primary">Volver al listado</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/validaciones.js"></script>
    <script src="assets/js/devoluciones.js"></script>
</body>

</html>