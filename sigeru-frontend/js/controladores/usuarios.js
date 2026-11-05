// Controlador de la pagina de usuarios (solo la ve el administrador)

let listaUsuarios = [];
let idEditando = null; // null cuando se esta agregando uno nuevo

async function iniciar() {
    const haySesion = await revisarSesion();
    if (!haySesion) return;

    await cargarUsuarios();
}

async function cargarUsuarios() {
    try {
        listaUsuarios = await obtenerUsuarios();
        mostrarTablaUsuarios(listaUsuarios, true);
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

function buscar() {
    const texto = document.getElementById("buscador").value.toLowerCase();

    // Guardamos solo los que tienen el texto buscado
    const filtrados = [];
    for (const u of listaUsuarios) {
        const contenido = (u.primer_nombre + " " + u.primer_apellido + " " + u.email + " " + u.rol).toLowerCase();
        if (contenido.includes(texto)) {
            filtrados.push(u);
        }
    }
    mostrarTablaUsuarios(filtrados, true);
}

function nuevoUsuario() {
    idEditando = null;
    document.getElementById("formUsuario").reset();
    document.getElementById("tituloModal").textContent = "Nuevo usuario";

    // Al crear un usuario la contraseña es obligatoria
    document.getElementById("contrasena").required = true;
    document.getElementById("ayudaContrasena").textContent = "Mínimo 6 caracteres.";
    abrirModal();
}

// Abre el formulario con los datos del usuario elegido
function editarUsuario(id) {
    const u = buscarPorId(listaUsuarios, id);
    idEditando = id;

    document.getElementById("primer_nombre").value = u.primer_nombre;
    document.getElementById("primer_apellido").value = u.primer_apellido;
    document.getElementById("email").value = u.email;
    document.getElementById("telefono").value = u.telefono ?? "";
    document.getElementById("rol").value = u.rol;
    document.getElementById("calle").value = u.calle ?? "";
    document.getElementById("zona").value = u.zona ?? "";
    document.getElementById("especialidad").value = u.especialidad ?? "";

    // Al modificar, la contraseña solo se cambia si escriben una nueva
    document.getElementById("contrasena").value = "";
    document.getElementById("contrasena").required = false;
    document.getElementById("ayudaContrasena").textContent = "Déjala vacía para no cambiarla.";

    document.getElementById("tituloModal").textContent = "Modificar usuario #" + id;
    abrirModal();
}

async function borrarUsuarioElegido(id) {
    if (!confirm("¿Seguro que quieres borrar el usuario #" + id + "?")) {
        return;
    }
    try {
        const respuesta = await borrarUsuario(id);
        mostrarMensaje(respuesta.mensaje, false);
        cargarUsuarios();
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

document.getElementById("formUsuario").addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const datos = {
        primer_nombre: document.getElementById("primer_nombre").value.trim(),
        primer_apellido: document.getElementById("primer_apellido").value.trim(),
        email: document.getElementById("email").value.trim(),
        telefono: document.getElementById("telefono").value.trim(),
        rol: document.getElementById("rol").value,
        calle: document.getElementById("calle").value.trim(),
        zona: document.getElementById("zona").value.trim(),
        especialidad: document.getElementById("especialidad").value.trim(),
        contrasena: document.getElementById("contrasena").value
    };

    try {
        let respuesta;
        if (idEditando == null) {
            respuesta = await crearUsuario(datos);
        } else {
            respuesta = await modificarUsuario(idEditando, datos);
        }
        cerrarModal();
        mostrarMensaje(respuesta.mensaje, false);
        cargarUsuarios();
    } catch (error) {
        // Si hay un error con la ventana abierta, el mensaje no se ve, por eso usamos alert
        alert(error.message);
    }
});

iniciar();
