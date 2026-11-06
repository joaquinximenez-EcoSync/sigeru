// Controlador de la pagina de incidencias: registro y seguimiento

let listaIncidencias = [];
let misRecorridos = [];   // recorridos del recolector (solo puede cambiar las incidencias de estos)
let idSiguiendo = null;   // incidencia que se esta cambiando en la ventana de seguimiento

async function iniciar() {
    const haySesion = await revisarSesion();
    if (!haySesion) return;

    try {
        // Contenedores para el formulario de registrar
        const contenedores = await obtenerContenedores();
        const select = document.getElementById("id_contenedor");
        for (const c of contenedores) {
            select.innerHTML += `<option value="${c.id}">#${c.id} ${limpiarTexto(c.calle)} (${limpiarTexto(c.zona)})</option>`;
        }

        // Recorridos: el administrador y el operario los eligen en el seguimiento,
        // y al recolector le llegan solo los de su cuadrilla
        const recorridos = await obtenerRecorridos();
        const selectRecorrido = document.getElementById("seguimientoRecorrido");
        for (const r of recorridos) {
            selectRecorrido.innerHTML += `<option value="${r.id}">${limpiarTexto(r.cuadrilla)} - ${limpiarTexto(r.ruta)} (${limpiarTexto(r.fecha_asignacion)})</option>`;
            misRecorridos.push(Number(r.id));
        }
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
    await cargarIncidencias();
}

// Devuelve true si el usuario puede hacerle el seguimiento a esa incidencia
function puedeCambiar(i) {
    if (i.estado == "Resuelta") return false;
    if (puedeEditar()) return true;
    // El recolector solo las que tiene asignadas su cuadrilla
    return misRecorridos.includes(Number(i.id_recorrido));
}

async function cargarIncidencias() {
    const estado = document.getElementById("filtroEstado").value;
    let filtros = "";
    if (estado != "") {
        filtros = "estado=" + encodeURIComponent(estado);
    }

    try {
        listaIncidencias = await obtenerIncidencias(filtros);
        for (const i of listaIncidencias) {
            i.puedeCambiar = puedeCambiar(i);
        }
        buscar();
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

// Filtra la tabla con lo que se escribe en el buscador
function buscar() {
    const texto = document.getElementById("buscador").value.toLowerCase();
    const filtrada = [];
    for (const i of listaIncidencias) {
        const datos = (i.calle + " " + i.zona_incidencia + " " + i.municipio + " " + i.tipo + " " + i.prioridad + " " + (i.cuadrilla ?? "")).toLowerCase();
        if (datos.includes(texto)) {
            filtrada.push(i);
        }
    }
    mostrarTablaIncidencias(filtrada);
}

// ----- Registrar una incidencia -----
function nuevaIncidencia() {
    document.getElementById("formIncidencia").reset();
    document.getElementById("tituloModal").textContent = "Registrar una incidencia";
    abrirModal();
}

document.getElementById("formIncidencia").addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const datos = {
        id_contenedor: document.getElementById("id_contenedor").value,
        problema: document.getElementById("problema").value,
        prioridad: document.getElementById("prioridad").value,
        descripcion: document.getElementById("descripcion").value
    };

    try {
        const respuesta = await reportarProblema(datos);
        cerrarModal();
        mostrarMensaje(respuesta.mensaje, false);
        cargarIncidencias();
    } catch (error) {
        alert(error.message);
    }
});

// ----- Seguimiento -----
function abrirSeguimiento(id) {
    const i = buscarPorId(listaIncidencias, id);
    idSiguiendo = id;

    document.getElementById("tituloSeguimiento").textContent = "Incidencia #" + id + " - " + i.calle;
    document.getElementById("detalleSeguimiento").textContent = i.tipo + " · detectada el " + i.fecha_deteccion + " · " + i.descripcion;
    document.getElementById("seguimientoEstado").value = i.estado;
    document.getElementById("seguimientoPrioridad").value = i.prioridad;
    document.getElementById("seguimientoRecorrido").value = i.id_recorrido ?? "";

    document.getElementById("seguimientoModal").showModal();
}

function cerrarSeguimiento() {
    document.getElementById("seguimientoModal").close();
}

document.getElementById("formSeguimiento").addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const datos = {
        estado: document.getElementById("seguimientoEstado").value,
        prioridad: document.getElementById("seguimientoPrioridad").value,
        id_recorrido: document.getElementById("seguimientoRecorrido").value
    };

    if (datos.estado == "Resuelta" && !confirm("Una incidencia resuelta ya no se puede cambiar. ¿Seguro que está resuelta?")) {
        return;
    }

    try {
        const respuesta = await seguimientoIncidencia(idSiguiendo, datos);
        cerrarSeguimiento();
        mostrarMensaje(respuesta.mensaje, false);
        cargarIncidencias();
    } catch (error) {
        alert(error.message);
    }
});

// ----- Historial de un contenedor -----
async function verHistorial(idContenedor) {
    try {
        const historial = await obtenerIncidencias("contenedor=" + idContenedor);
        let titulo = "Historial del contenedor #" + idContenedor;
        if (historial.length > 0) {
            titulo = titulo + " - " + historial[0].calle;
        }
        document.getElementById("tituloHistorial").textContent = titulo;
        mostrarTablaHistorial(historial);
        document.getElementById("historialModal").showModal();
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

function cerrarHistorial() {
    document.getElementById("historialModal").close();
}

iniciar();
