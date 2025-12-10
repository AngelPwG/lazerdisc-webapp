<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Devolución</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .producto-item { margin: 5px 0; padding: 10px; border: 1px solid #ddd; }
        .producto-item input[type="checkbox"] { margin-right: 10px; }
        .producto-item input[type="number"] { width: 80px; margin-left: 10px; }
        #tabla-productos { display: none; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        table th { background-color: #f0f0f0; }
        .info-venta { background-color: #e8f4f8; padding: 10px; margin: 10px 0; border-left: 4px solid #2196F3; }
        .total-reembolso { font-size: 18px; font-weight: bold; margin: 15px 0; }
    </style>
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>

    <h1>Nueva Devolución</h1>
    <?php if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'admin'): ?>
        <a href="index.php?c=Devoluciones&a=index">Volver al listado</a>
    <?php endif; ?>
    <hr>

    <div id="form-devolucion">
        <!-- Paso 1: Buscar Venta por Folio -->
        <div>
            <label><strong>Folio de Venta:</strong></label>
            <input type="text" id="folio_venta" placeholder="Ej. V-20251209..." autocomplete="off" style="width: 250px;">
            <button onclick="buscarVenta()">Buscar Venta</button>
        </div>

        <!-- Paso 2: Mostrar Información de la Venta y Productos -->
        <div id="info-venta-container" style="display:none;">
            <div class="info-venta">
                <p><strong>Folio:</strong> <span id="info-folio"></span></p>
                <p><strong>Fecha:</strong> <span id="info-fecha"></span></p>
                <p><strong>Total Original:</strong> $<span id="info-total"></span></p>
            </div>

            <h3>Productos de la Venta</h3>
            <table id="tabla-productos">
                <thead>
                    <tr>
                        <th style="width: 50px;">Devolver</th>
                        <th>Producto</th>
                        <th style="width: 100px;">Cant. Original</th>
                        <th style="width: 120px;">Cant. a Devolver</th>
                        <th style="width: 100px;">Precio Unit.</th>
                        <th style="width: 100px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="productos-lista">
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>

            <div class="total-reembolso">
                Total a Reembolsar: $<span id="total-reembolso">0.00</span>
            </div>

            <label><strong>Motivo de Devolución:</strong></label><br>
            <textarea id="motivo" rows="3" style="width: 400px;" placeholder="Descripción del motivo..."></textarea>
            <br><br>

            <button onclick="guardarDevolucion()" style="padding: 10px 20px; font-size: 16px;">Guardar Devolución</button>
        </div>
    </div>

    <script src="assets/js/validaciones.js"></script>
    <script src="assets/js/devoluciones.js"></script>
</body>

</html>