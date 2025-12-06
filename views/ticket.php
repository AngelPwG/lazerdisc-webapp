<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ticket</title>
  <link rel="stylesheet" href="ticket.css">
</head>
<body>
  <div class="ticket">
    <h2>Mi Negocio</h2>
    <p>RFC: XXXX</p>
    <p>Folio: <?php echo $venta['folio']; ?></p>
    <p>Fecha: <?php echo $venta['fecha']; ?></p>
    <hr>
    <table>
      <?php foreach($venta['lineas'] as $l){ ?>
        <tr>
          <td><?php echo $l['cantidad']; ?></td>
          <td><?php echo $l['descripcion']; ?></td>
          <td><?php echo number_format($l['precio_unit'],2); ?></td>
          <td><?php echo number_format($l['importe_linea'],2); ?></td>
        </tr>
      <?php } ?>
    </table>
    <hr>
    <p>Subtotal: <?php echo $venta['subtotal']; ?></p>
    <p>IVA: <?php echo $venta['iva']; ?></p>
    <p>Total: <?php echo $venta['total']; ?></p>
    <p>¡Gracias por su compra!</p>
  </div>
</body>
</html>