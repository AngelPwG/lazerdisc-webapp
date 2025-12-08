<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Disco</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>
    <div class="container">
        <h1>Editar Disco: <?= htmlspecialchars($disco['titulo']) ?></h1>

        <?php if (isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form action="index.php?c=Discos&a=actualizar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_disco" value="<?= $disco['id_disco'] ?>">

            <fieldset>
                <legend>Información General</legend>

                <div class="form-group">
                    <label>Título del Álbum:</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($disco['titulo']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Código de Barras:</label>
                    <input type="text" name="codigo_barras" value="<?= htmlspecialchars($disco['codigo_barras']) ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Artista:</label>
                    <select name="id_artista" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($artistas as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= ($a['id'] == $disco['id_artista']) ? 'selected' : '' ?>>
                                <?= $a['nombre'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Disquera:</label>
                    <select name="id_disquera" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($disqueras as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($d['id'] == $disco['id_disquera']) ? 'selected' : '' ?>>
                                <?= $d['nombre'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Géneros (Ctrl+Click para múltiples):</label>
                    <?php
                    // Extraer IDs de géneros actuales
                    $ids_actuales = array_column($disco['generos'], 'id_genero');
                    ?>
                    <select name="id_genero[]" multiple required style="height: 100px;">
                        <?php foreach ($generos as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= in_array($g['id'], $ids_actuales) ? 'selected' : '' ?>>
                                <?= $g['nombre'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Año Lanzamiento:</label>
                    <input type="number" name="anio" min="1900" max="2099"
                        value="<?= $disco['anio_lanzamiento'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label>Tipo:</label>
                    <select name="tipo">
                        <option value="CD" <?= ($disco['tipo'] == 'CD') ? 'selected' : '' ?>>CD</option>
                        <option value="Vinilo" <?= ($disco['tipo'] == 'Vinilo') ? 'selected' : '' ?>>Vinilo</option>
                        <option value="Digital" <?= ($disco['tipo'] == 'Digital') ? 'selected' : '' ?>>Digital</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Control Parental:</label>
                    <select name="parental">
                        <option value="0" <?= ($disco['control_parental'] == 0) ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= ($disco['control_parental'] == 1) ? 'selected' : '' ?>>Sí (Explicit)</option>
                    </select>
                </div>
            </fieldset>

            <fieldset>
                <legend>Precios y Costos</legend>
                <div class="form-group">
                    <label>Precio Venta:</label>
                    <input type="number" step="0.01" name="precio" value="<?= $disco['precio_venta'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Costo Promedio:</label>
                    <input type="number" step="0.01" name="costo" value="<?= $disco['costo_promedio'] ?>" required>
                </div>
            </fieldset>

            <fieldset>
                <legend>Multimedia</legend>
                <div class="form-group">
                    <label>Portada Principal (Dejar vacío para mantener actual):</label>
                    <?php if (!empty($disco['imagen_base64'])): ?>
                        <img src="data:image/jpeg;base64,<?= $disco['imagen_base64'] ?>" class="img-preview">
                    <?php endif; ?>
                    <input type="file" name="imagen" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Imágenes Extra (Galería):</label>
                    <div class="gallery-preview">
                        <?php foreach ($disco['imagenes_extra'] as $img): ?>
                            <div style="text-align: center;">
                                <img src="data:image/jpeg;base64,<?= $img['base64'] ?>" class="img-preview">
                                <br>
                                <label><input type="checkbox" name="eliminar_imagenes[]" value="<?= $img['id_imagen'] ?>">
                                    Eliminar</label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <label style="margin-top:10px; display:block;">Agregar Nuevas:</label>
                    <input type="file" name="imagenes_extra[]" accept="image/*" multiple>
                </div>
            </fieldset>

            <fieldset>
                <legend>Lista de Canciones</legend>
                <div id="canciones-container">
                    <?php foreach ($disco['canciones'] as $i => $c): ?>
                        <div class="song-row">
                            <input type="text" name="canciones[<?= $i ?>][titulo]"
                                value="<?= htmlspecialchars($c['titulo_cancion']) ?>" placeholder="Título Canción" required>
                            <input type="text" name="canciones[<?= $i ?>][duracion]"
                                value="<?= htmlspecialchars($c['duracion']) ?>" placeholder="Duración (MM:SS)" required>
                            <button type="button" onclick="this.parentElement.remove()">X</button>
                        </div>
                    <?php endforeach; ?>
                    <!-- Si no hay canciones, mostramos al menos una fila vacía? No, dejamos botón agregar. -->
                </div>
                <button type="button" onclick="agregarCancion()">+ Agregar Canción</button>
            </fieldset>

            <div class="form-group" style="margin-top: 20px;">
                <button type="submit">Actualizar Disco</button>
                <a href="index.php?c=Discos&a=index">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        let songCount = <?= count($disco['canciones']) ?>;
        function agregarCancion() {
            const container = document.getElementById('canciones-container');
            const div = document.createElement('div');
            div.className = 'song-row';
            div.innerHTML = `
                <input type="text" name="canciones[${songCount}][titulo]" placeholder="Título Canción" required>
                <input type="text" name="canciones[${songCount}][duracion]" placeholder="Duración (MM:SS)" required>
                <button type="button" onclick="this.parentElement.remove()">X</button>
            `;
            container.appendChild(div);
            songCount++;
        }
    </script>
</body>

</html>