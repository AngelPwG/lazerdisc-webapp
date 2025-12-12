<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Nuevo Disco</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>

    <div class="container">
        <h1 class="page-title">Registrar Nuevo Disco</h1>

        <?php if (isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form action="index.php?c=Discos&a=guardar" method="POST" enctype="multipart/form-data"
            class="main-grid-container">

            <!-- INFORMACIÓN GENERAL -->
            <fieldset class="form-section card-info">
                <legend class="section-title">Información General</legend>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Título del Álbum:</label>
                            <input type="text" name="titulo" required>
                        </div>

                        <div class="form-group">
                            <label>Código de Barras:</label>
                            <input type="text" name="codigo_barras" required>
                        </div>

                        <div class="form-group">
                            <label>Artista:</label>
                            <select name="id_artista" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($artistas as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= $a['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Disquera:</label>
                            <select name="id_disquera" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($disqueras as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= $d['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Año Lanzamiento:</label>
                            <input type="number" name="anio" min="1900" max="2099" required>
                        </div>

                        <div class="form-group">
                            <label>Tipo:</label>
                            <select name="tipo">
                                <option value="CD">CD</option>
                                <option value="Vinilo">Vinilo</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Control Parental:</label>
                            <select name="parental">
                                <option value="0">No</option>
                                <option value="1">Sí (Explicit)</option>
                            </select>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Géneros (Ctrl+Click para múltiples):</label>
                            <select name="id_genero[]" multiple required style="height: 100px;">
                                <?php foreach ($generos as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= $g['nombre'] ?></option>
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
                            <input type="number" step="0.01" name="precio" required>
                        </div>
                        <div class="form-group">
                            <label>Costo Promedio:</label>
                            <input type="number" step="0.01" name="costo" required>
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
                        <input type="file" name="imagen" accept="image/*" required>
                    </div>
                    <div class="form-group" style="margin-top: 15px;">
                        <label>Imágenes Extra (Galería):</label>
                        <input type="file" name="imagenes_extra[]" accept="image/*" multiple>
                    </div>
                </div>
            </fieldset>

            <!-- CANCIONES -->
            <fieldset class="form-section card-songs">
                <legend class="section-title">Lista de Canciones</legend>
                <div class="card-body">
                    <div id="canciones-container" class="song-list-container">
                        <div class="song-row">
                            <div class="form-group" style="flex:2">
                                <input type="text" name="canciones[0][titulo]" placeholder="Título Canción" required>
                            </div>
                            <div class="form-group" style="flex:1">
                                <input type="text" name="canciones[0][duracion]" placeholder="Duración" required>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-save" style="width: 100%; margin-top: 15px;"
                        onclick="agregarCancion()">+ Agregar Canción</button>
                </div>
            </fieldset>

            <!-- BOTONES -->
            <div class="form-actions" style="grid-column: 1 / -1;">
                <a href="index.php?c=Discos&a=index" class="btn-save">Cancelar</a>
                <button type="submit" class="btn-save">Guardar Disco</button>
            </div>

        </form>
    </div>

    <script src="assets/js/validaciones.js"></script>
    <script src="assets/js/discos.js"></script>
</body>

</html>