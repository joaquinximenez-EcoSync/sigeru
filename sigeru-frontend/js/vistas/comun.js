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

    // Solo el administrador ve lo de usuarios
    if (usuario.rol != "Administrador") {
        ocultar("solo-admin");
    }

    // Solo el administrador y el operario pueden agregar, editar y borrar contenedores, camiones, centros y maquinaria
    if (usuario.rol != "Administrador" && usuario.rol != "Operario") {
        ocultar("solo-gestion");
    }

    // Los vecinos no ven los camiones, los centros de acopio ni la maquinaria
    if (usuario.rol == "Vecino") {
        ocultar("no-vecino");
    }
}

// Oculta todas las partes de la pagina que tienen esa clase
function ocultar(clase) {
    const elementos = document.querySelectorAll("." + clase);
    for (const elemento of elementos) {
        elemento.style.display = "none";
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
    if (estado == "Funcional" || estado == "Operativo" || estado == "Operativa" || estado == "Disponible") return "";
    if (estado == "En mantenimiento" || estado == "En reparación" || estado == "En ruta") return "alerta";
    return "mal";
}

// Busca un elemento de una lista por su id. Si no lo encuentra devuelve null.
function buscarPorId(lista, id) {
    for (const item of lista) {
        if (item.id == id) {
            return item;
        }
    }
    return null;
}
