<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Disco</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>
    <div class="edit-page">
        <!-- Replaced edit-container with main-grid-container for layout matching create page -->
        <h1 class="page-title">Editar Disco: <?= htmlspecialchars($disco['titulo']) ?></h1>

        <form action="index.php?c=Discos&a=actualizar" method="POST" enctype="multipart/form-data" class="main-grid-container">
            <input type="hidden" name="id_disco" value="<?= $disco['id_disco'] ?>">

            <!-- INFORMACIÓN GENERAL -->
            <fieldset class="form-section card-info">
                <legend class="section-title">Información General</legend>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Título del Álbum:</label>
                            <input type="text" name="titulo" value="<?= htmlspecialchars($disco['titulo']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Código de Barras:</label>
                            <input type="text" name="codigo_barras" value="<?= htmlspecialchars($disco['codigo_barras']) ?>" required>
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
                            <label>Año Lanzamiento:</label>
                            <input type="number" name="anio" min="1900" max="2099"
                                value="<?= $disco['anio_lanzamiento'] ?? '' ?>" required 
                                onblur="validarAnio(this)">
                        </div>

                        <div class="form-group">
                            <label>Tipo:</label>
                            <select name="tipo">
                                <option value="CD" <?= ($disco['tipo'] == 'CD') ? 'selected' : '' ?>>CD</option>
                                <option value="Vinilo" <?= ($disco['tipo'] == 'Vinilo') ? 'selected' : '' ?>>Vinilo</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Control Parental:</label>
                            <select name="parental">
                                <option value="0" <?= ($disco['control_parental'] == 0) ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= ($disco['control_parental'] == 1) ? 'selected' : '' ?>>Sí (Explicit)</option>
                            </select>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
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


                    </div>
                </div>
            </fieldset>

            <!-- PRECIOS -->
            <fieldset class="form-section card-prices">
                <legend class="section-title">Precios y Costos</legend>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Precio Venta:</label>
                            <input type="number" step="0.01" name="precio" value="<?= $disco['precio_venta'] ?>" required onblur="validarPrecio(this)">
                        </div>
                        <div class="form-group">
                            <label>Costo Promedio:</label>
                            <input type="number" step="0.01" name="costo" value="<?= $disco['costo_promedio'] ?>" required onblur="validarCosto(this)">
                        </div>
                    </div>
                </div>
            </fieldset>

            <!-- MULTIMEDIA -->
            <fieldset class="form-section card-media">
                <legend class="section-title">Multimedia</legend>
                <div class="card-body">
                    <div class="form-group">
                        <label>Portada Principal:</label>
                        <?php if (!empty($disco['imagen_base64'])): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="data:image/jpeg;base64,<?= $disco['imagen_base64'] ?>" class="img-preview" style="max-height: 100px; border-radius: 8px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="imagen" accept="image/*">
                    </div>

                    <div class="form-group" style="margin-top: 15px;">
                        <label>Imágenes Extra:</label>
                        <div class="gallery-grid">
                            <?php foreach ($disco['imagenes_extra'] as $img): ?>
                                <div class="gallery-card" style="text-align: center;">
                                    <img src="data:image/jpeg;base64,<?= $img['base64'] ?>" class="img-preview" style="max-height: 80px; border-radius: 8px;">
                                    <br>
                                    <label><input type="checkbox" name="eliminar_imagenes[]" value="<?= $img['id_imagen'] ?>"> Del</label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top: 10px;">
                            <label>Agregar Nuevas:</label>
                            <input type="file" name="imagenes_extra[]" accept="image/*" multiple>
                        </div>
                    </div>
                </div>
            </fieldset>

            <!-- CANCIONES -->
            <fieldset class="form-section card-songs">
                <legend class="section-title">Lista de Canciones</legend>
                <div class="card-body">
                    <div id="canciones-container" class="song-list-container">
                        <?php foreach ($disco['canciones'] as $i => $c): ?>
                            <div class="song-row">
                                <div class="form-group" style="flex:2">
                                    <input type="text" name="canciones[<?= $i ?>][titulo]" value="<?= htmlspecialchars($c['titulo_cancion']) ?>" placeholder="Título Canción" required>
                                </div>
                                <div class="form-group" style="flex:1">
                                    <input type="text" name="canciones[<?= $i ?>][duracion]" value="<?= htmlspecialchars($c['duracion']) ?>" placeholder="Duración (MM:SS)" required>
                                </div>
                                <button type="button" class="btn-cancel" style="min-width: 40px; padding: 0 10px;" onclick="this.parentElement.remove()">X</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn-save" style="width: 100%; margin-top: 15px;" onclick="agregarCancion()">+ Agregar Canción</button>
                </div>
            </fieldset>

            <!-- BOTONES -->
            <div class="form-actions" style="grid-column: 1 / -1;">
                <a href="index.php?c=Discos&a=index" class="btn-save">Cancelar</a>
                <button type="submit" class="btn-save">Actualizar Disco</button>
            </div>
        </form>
    </div>

    <script src="assets/js/validaciones.js"></script>
    <script src="assets/js/discos.js"></script>
    <script>
        // Inicializar contador de canciones desde PHP
        initSongCount(<?= count($disco['canciones']) ?>);
    </script>
</body>
</html>