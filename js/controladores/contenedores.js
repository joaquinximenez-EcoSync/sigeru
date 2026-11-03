// Controlador de la pagina de contenedores

let listaContenedores = [];

async function iniciar() {
    const haySesion = await revisarSesion();
    if (!haySesion) return;

    try {
        const tipos = await obtenerTiposResiduo();
        llenarSelectResiduos(tipos);
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
    await cargarContenedores();
}

async function cargarContenedores() {
    try {
        listaContenedores = await obtenerContenedores();
        mostrarTablaContenedores(listaContenedores);
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

function buscar() {
    const texto = document.getElementById("buscador").value.toLowerCase();

    const filtrados = listaContenedores.filter(function (c) {
        const contenido = (c.calle + " " + c.zona + " " + c.estado + " " + c.residuo).toLowerCase();
        return contenido.includes(texto);
    });
    mostrarTablaContenedores(filtrados);
}

function nuevoContenedor() {
    document.getElementById("formContenedor").reset();
    abrirModal();
}

document.getElementById("formContenedor").addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const datos = {
        calle: document.getElementById("calle").value.trim(),
        zona: document.getElementById("zona").value.trim(),
        estado: document.getElementById("estado").value,
        capacidad: document.getElementById("capacidad").value,
        id_residuo: document.getElementById("id_residuo").value
    };

    try {
        const respuesta = await crearContenedor(datos);
        cerrarModal();
        mostrarMensaje(respuesta.mensaje, false);
        cargarContenedores();
    } catch (error) {
        // El mensaje de la pagina queda detras del modal, por eso usamos alert
        alert(error.message);
    }
});

iniciar();
