<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Compra</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'views/includes/menu.php'; ?>

    <div class="page-header">
        <h1 class="page-title">Registrar Nueva Compra</h1>
        <div class="page-actions">
            <!-- Botón movido al final -->
        </div>
    </div>

    <div class="main-grid-container">
        <fieldset class="form-section" style="grid-column: 1 / -1; width: 100%;">
            <legend class="section-title">Información de Compra</legend>
            <div class="card-body">
                <div id="form-compra">
                    <div class="form-group">
                        <label><strong>Proveedor:</strong></label>
                        <select id="id_proveedor" style="width: 100%; max-width: 400px;">
                            <option value="">Seleccione un proveedor...</option>
                            <?php foreach ($proveedores as $p): ?>
                                <option value="<?= $p['id_proveedor'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h3 class="section-title" style="margin-top: 30px; margin-bottom: 20px;">Productos</h3>

                    <div id="items-compra" style="display: flex; flex-direction: column; gap: 15px;">
                        <!-- Los items se agregarán dinámicamente -->
                    </div>

                    <br>
                    <div class="form-group">
                        <button class="btn-add" onclick="agregarFila()">+ Agregar Producto</button>
                    </div>

                    <div class="form-actions" style="margin-top: 30px; text-align: right;">
                        <a href="index.php?c=Compras&a=index" class="btn-primary" style="margin-right: 10px;">Volver al listado</a>
                        <button class="btn-save" onclick="guardarCompra()">Registrar Compra</button>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    <script src="assets/js/validaciones.js"></script>
    <script src="assets/js/compras.js"></script>
    </body>

</html>