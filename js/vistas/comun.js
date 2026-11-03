// Vista: funciones para mostrar cosas en pantalla que usan todas las paginas

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

// Para que nadie pueda meter codigo HTML en las tablas (ataque XSS)
function limpiarTexto(texto) {
    if (texto == null) return "";
    return String(texto)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;");
}

// Muestra el nombre y rol en el menu, y oculta lo que su rol no puede usar
function mostrarDatosUsuario(usuario) {
    document.getElementById("nombreUsuario").textContent = usuario.nombre;
    document.getElementById("rolUsuario").textContent = usuario.rol;

    // Los elementos con data-roles solo se ven si el rol esta en la lista
    const elementos = document.querySelectorAll("[data-roles]");
    for (const elemento of elementos) {
        const roles = elemento.dataset.roles.split(",");
        if (!roles.includes(usuario.rol)) {
            elemento.style.display = "none";
        }
    }

    // Marca en el menu la pagina en la que estamos
    const paginaActual = window.location.pathname.split("/").pop();
    const enlaces = document.querySelectorAll(".sidebar a");
    for (const enlace of enlaces) {
        if (enlace.getAttribute("href") == paginaActual) {
            enlace.classList.add("activo");
        }
    }
}

// Muestra el modal (cuadro de dialogo)
function abrirModal() {
    const modal = document.getElementById("formModal");
    modal.showModal();
}

// Oculta el modal
function cerrarModal() {
    const modal = document.getElementById("formModal");
    modal.close();
}

// Devuelve la clase CSS segun el estado: verde (bien), amarillo (alerta) o rojo (mal)
function colorEstado(estado) {
    if (estado == "Funcional" || estado == "Operativo" || estado == "Disponible") return "";
    if (estado == "En mantenimiento" || estado == "En ruta") return "alerta";
    return "mal";
}
