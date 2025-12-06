<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reportes</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <h2>Reportes</h2>
  <form method="GET" action="reporte_inventario.php">
    <label>Fecha inicio</label><input type="date" name="inicio">
    <label>Fecha fin</label><input type="date" name="fin">
    <button type="submit">Generar</button>
  </form>
</body>
</html>