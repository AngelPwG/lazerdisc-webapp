<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Devolución #<?= $folio_venta ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 80mm;
            /* Ancho estándar de impresora térmica */
            background-color: #fff;
        }

        .header,
        .footer {
            text-align: center;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
        }

        .devolucion-badge {
            background-color: #000;
            color: #fff;
            padding: 8px;
            margin: 10px 0;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            border-radius: 4px;
        }

        .info {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .info p {
            margin: 2px 0;
        }

        .motivo-box {
            background-color: #f5f5f5;
            padding: 8px;
            margin: 10px 0;
            border: 1px dashed #000;
        }

        .motivo-box p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            text-align: left;
            border-bottom: 1px solid #000;
        }

        td {
            padding: 2px 0;
            vertical-align: top;
        }

        .producto-devuelto {
            text-decoration: line-through;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .totals p {
            margin: 2px 0;
            display: flex;
            justify-content: space-between;
        }

        .bold {
            font-weight: bold;
        }

        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            @page {
                margin: 0;
            }
        }

        .btn-print {
            display: block;
            width: 100%;
            padding: 10px;
            background: #000;
            color: #fff;
            text-align: center;
            border: none;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .logo-ticket img {
            max-width: 100px;
            /* Decent readable size */
            height: auto;
            margin-bottom: 5px;
        }

        @media print {
            .btn-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <button class="btn-print" onclick="window.print()">Imprimir Ticket</button>

    <div class="header">
        <div class="logo-ticket">
            <img src="assets/img/logoblanconegro.png" alt="Logo de la Empresa">
        </div>
        <h2>LAZER DISC</h2>
        <p>Av. SeisSiete <br>Ciudad, CP 82018<br>Tel: (555) 555-5555</p>
    </div>

    <div class="devolucion-badge">
        *** DEVOLUCIÓN ***
    </div>

    <div class="info">
        <p><strong>Venta Original:</strong> <?= $folio_venta ?></p>
        <p><strong>Fecha Devolución:</strong> <?= date('d/m/Y H:i', strtotime($fecha_devolucion)) ?></p>
        <p><strong>Autorizado por:</strong> <?= htmlspecialchars($cajero) ?></p>
    </div>

    <div class="motivo-box">
        <p><strong>Motivo:</strong></p>
        <p><?= htmlspecialchars($motivo) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 45%;">Prod</th>
                <th style="width: 15%;">Cant</th>
                <th style="width: 20%;">P.U.</th>
                <th style="width: 20%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lineas as $item): ?>
                <tr class="producto-devuelto">
                    <td><?= htmlspecialchars($item['descripcion']) ?></td>
                    <td style="text-align: center;"><?= $item['cantidad'] ?></td>
                    <td>$<?= number_format($item['precio_unitario'], 2) ?></td>
                    <td class="text-right">$<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <p>
            <span>Subtotal:</span>
            <span>$<?= number_format($subtotal_calc, 2) ?></span>
        </p>
        <p>
            <span>IVA (16%):</span>
            <span>$<?= number_format($iva_calc, 2) ?></span>
        </p>
        <p class="bold" style="font-size: 14px; margin-top: 5px;">
            <span>TOTAL REEMBOLSADO:</span>
            <span>$<?= number_format($total_calc, 2) ?></span>
        </p>
    </div>

    <div class="footer">
        <p style="margin-top: 20px;">Devolución procesada</p>
        <p>Conserve este ticket para<br>cualquier aclaración.</p>
    </div>

    <script>
        // Imprimir automáticamente al cargar
        window.onload = function () {
            window.print();
        }

        // Cerrar ventana automáticamente después de imprimir o cancelar
        window.onafterprint = function () {
            window.close();
        }
    </script>
</body>

</html>
