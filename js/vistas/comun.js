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

// Para que nadie pueda meter codigo HTML en las tablas desde un formulario
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

    // Las partes de la pagina que tienen data-roles solo se muestran si el rol del usuario esta en esa lista
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

// Abre la ventana emergente (modal)
function abrirModal() {
    const modal = document.getElementById("formModal");
    modal.showModal();
}

// Cierra la ventana emergente (modal)
function cerrarModal() {
    const modal = document.getElementById("formModal");
    modal.close();
}

// Elige el color de la etiqueta segun el estado: verde (bien), amarillo (alerta) o rojo (mal)
function colorEstado(estado) {
    if (estado == "Funcional" || estado == "Operativo" || estado == "Disponible") return "";
    if (estado == "En mantenimiento" || estado == "En ruta") return "alerta";
    return "mal";
}
