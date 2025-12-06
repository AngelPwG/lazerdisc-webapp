<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Productos</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <h2>Catálogo de Productos</h2>
  <form method="POST" action="producto_add.php" enctype="multipart/form-data">
    <input type="text" name="codigo" placeholder="Código" required>
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="number" step="0.01" name="precio" placeholder="Precio" required>
    <textarea name="descripcion" placeholder="Descripción"></textarea>
    <input type="file" name="imagen" accept="image/*" required>
    <button type="submit">Agregar Producto</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>Código</th><th>Nombre</th><th>Precio</th><th>Imagen</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Ejemplo de renderizado
      // foreach($productos as $p){
      //   echo "<tr><td>{$p['codigo']}</td><td>{$p['nombre']}</td><td>{$p['precio']}</td>
      //   <td><img src='img.php?id={$p['id']}' width='50'></td>
      //   <td><a href='producto_edit.php?id={$p['id']}'>Editar</a></td></tr>";
      // }
      ?>
    </tbody>
  </table>
</body>
</html>