// Controlador de la sesion: lo usan todas las paginas del panel

// Guarda los datos del usuario que inicio sesion
let usuarioLogueado = null;

// Revisa que haya un usuario logueado. Si no hay, lo manda al login y devuelve false.
async function revisarSesion() {
    try {
        const respuesta = await obtenerSesion();
        usuarioLogueado = respuesta.usuario;
    } catch (error) {
        window.location.href = "index.html";
        return false;
    }

    mostrarDatosUsuario(usuarioLogueado);
    return true;
}

// Devuelve true si el usuario puede dar de alta contenedores y camiones
function puedeRegistrar() {
    return usuarioLogueado.rol == "Administrador" || usuarioLogueado.rol == "Operario";
}

async function salir() {
    await cerrarSesion();
    window.location.href = "index.html";
}
