// Controlador de la pagina de camiones

let listaCamiones = [];

async function iniciar() {
    const haySesion = await revisarSesion();
    if (!haySesion) return;

    await cargarCamiones();
}

async function cargarCamiones() {
    try {
        listaCamiones = await obtenerCamiones();
        mostrarTablaCamiones(listaCamiones);
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

function buscar() {
    const texto = document.getElementById("buscador").value.toLowerCase();

    const filtrados = listaCamiones.filter(function (c) {
        const contenido = (c.matricula + " " + c.estado + " " + c.disponibilidad).toLowerCase();
        return contenido.includes(texto);
    });
    mostrarTablaCamiones(filtrados);
}

function nuevoCamion() {
    document.getElementById("formCamion").reset();
    abrirModal();
}

document.getElementById("formCamion").addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const datos = {
        matricula: document.getElementById("matricula").value.trim(),
        capacidad_carga: document.getElementById("capacidad_carga").value,
        estado: document.getElementById("estado").value,
        disponibilidad: document.getElementById("disponibilidad").value
    };

    try {
        const respuesta = await crearCamion(datos);
        cerrarModal();
        mostrarMensaje(respuesta.mensaje, false);
        cargarCamiones();
    } catch (error) {
        // El mensaje de la pagina queda detras del modal, por eso usamos alert
        alert(error.message);
    }
});

iniciar();
