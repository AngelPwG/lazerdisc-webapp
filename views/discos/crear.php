<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Nuevo Disco</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'views/includes/menu.php'; ?>
    <div class="container">
        <h1>Registrar Nuevo Disco</h1>

        <?php if (isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form action="index.php?c=Discos&a=guardar" method="POST" enctype="multipart/form-data">

            <fieldset>
                <legend>Información General</legend>

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
                    <label>Géneros (Ctrl+Click para múltiples):</label>
                    <select name="id_genero[]" multiple required style="height: 100px;">
                        <?php foreach ($generos as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= $g['nombre'] ?></option>
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
                        <option value="Digital">Digital</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Control Parental:</label>
                    <select name="parental">
                        <option value="0">No</option>
                        <option value="1">Sí (Explicit)</option>
                    </select>
                </div>
            </fieldset>

            <fieldset>
                <legend>Precios y Costos</legend>
                <div class="form-group">
                    <label>Precio Venta:</label>
                    <input type="number" step="0.01" name="precio" required>
                </div>
                <div class="form-group">
                    <label>Costo Promedio:</label>
                    <input type="number" step="0.01" name="costo" required>
                </div>
            </fieldset>

            <fieldset>
                <legend>Multimedia</legend>
                <div class="form-group">
                    <label>Portada Principal:</label>
                    <input type="file" name="imagen" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label>Imágenes Extra (Galería):</label>
                    <input type="file" name="imagenes_extra[]" accept="image/*" multiple>
                </div>
            </fieldset>

            <fieldset>
                <legend>Lista de Canciones</legend>
                <div id="canciones-container">
                    <div class="song-row">
                        <input type="text" name="canciones[0][titulo]" placeholder="Título Canción" required>
                        <input type="text" name="canciones[0][duracion]" placeholder="Duración (MM:SS)" required>
                    </div>
                </div>
                <button type="button" onclick="agregarCancion()">+ Agregar Canción</button>
            </fieldset>

            <div class="form-group" style="margin-top: 20px;">
                <button type="submit">Guardar Disco</button>
                <a href="index.php?c=Discos&a=index">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        let songCount = 1;
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