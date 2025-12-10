// Gestión de canciones
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

// Inicializar songCount para editar (se sobreescribirá desde PHP si es necesario)
function initSongCount(count) {
    songCount = count;
}
