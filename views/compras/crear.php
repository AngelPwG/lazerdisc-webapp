<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Compra</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .item-row { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background-color: #f9f9f9; }
        .item-row input { margin: 0 5px; }
        .producto-info { color: #28a745; font-weight: bold; margin-left: 10px; }
        .producto-error { color: #dc3545; font-weight: bold; margin-left: 10px; }
        .btn-remove { background-color: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; }
        .btn-add { background-color: #28a745; color: white; border: none; padding: 8px 15px; cursor: pointer; margin: 10px 0; }
        .btn-submit { background-color: #007bff; color: white; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px; }
    </style>
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>

    <h1>Registrar Nueva Compra</h1>
    <a href="index.php?c=Compras&a=index">Volver al listado</a>
    <hr>

    <div id="form-compra">
        <label><strong>Proveedor:</strong></label>
        <select id="id_proveedor" style="width: 300px;">
            <option value="">Seleccione un proveedor...</option>
            <?php foreach ($proveedores as $p): ?>
                <option value="<?= $p['id_proveedor'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <h3>Productos</h3>
        <div id="items-compra">
            <!-- Los items se agregarán dinámicamente -->
        </div>
        <br>
        <button class="btn-add" onclick="agregarFila()">+ Agregar Producto</button>
        <br><br>
        <button class="btn-submit" onclick="guardarCompra()">Registrar Compra</button>
    </div>

    <script src="assets/js/validaciones.js"></script>
    <script src="assets/js/compras.js"></script>
</body>

</html>