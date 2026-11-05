// Controlador de la pagina de usuarios

let listaUsuarios = [];

async function iniciar() {
    const haySesion = await revisarSesion();
    if (!haySesion) return;

    await cargarUsuarios();
}

async function cargarUsuarios() {
    try {
        listaUsuarios = await obtenerUsuarios();
        mostrarTablaUsuarios(listaUsuarios);
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
    mostrarTablaUsuarios(filtrados);
}

function nuevoUsuario() {
    document.getElementById("formUsuario").reset();
    abrirModal();
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
        const respuesta = await crearUsuario(datos);
        cerrarModal();
        mostrarMensaje(respuesta.mensaje, false);
        cargarUsuarios();
    } catch (error) {
        // Si hay un error con la ventana abierta, el mensaje no se ve, por eso usamos alert
        alert(error.message);
    }
});

iniciar();
