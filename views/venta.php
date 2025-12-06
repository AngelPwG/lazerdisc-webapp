<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ventas</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <h2>Punto de Venta</h2>
  <form method="POST" action="venta_add.php">
    <input type="text" name="codigo" placeholder="Escanee código" autofocus>
    <button type="submit">Agregar</button>
  </form>

  <table>
    <thead>
      <tr><th>Cant</th><th>Producto</th><th>Precio</th><th>Importe</th></tr>
    </thead>
    <tbody>
      <!-- Renderizar carrito -->
    </tbody>
  </table>
  <button onclick="location.href='venta_confirm.php'">Confirmar Venta</button>
</body>
</html>