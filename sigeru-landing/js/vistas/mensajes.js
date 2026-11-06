// Vista: muestra mensajes en la pagina

// Muestra un mensaje verde (bien) o rojo (error)
function mostrarMensaje(texto, esError) {
    const caja = document.getElementById("mensaje");
    caja.textContent = texto;
    if (esError) {
        caja.className = "mensaje error";
    } else {
        caja.className = "mensaje ok";
    }
}

// Muestra el enlace de verificacion debajo del mensaje (solo en modo prueba, sin Gmail)
function mostrarEnlacePrueba(enlace) {
    const caja = document.getElementById("mensaje");
    caja.innerHTML += `<br><a class="btn btn-secondary btn-sm enlace-prueba" href="${enlace}">Verificar mi cuenta (modo prueba)</a>`;
}
