// Modelo: pedidos a la API de Gestion

// ----- Contenedores -----
function obtenerContenedores() {
    return pedirAlServidor(API_GESTION + "/contenedores");
}

function crearContenedor(datos) {
    return pedirAlServidor(API_GESTION + "/contenedores", "POST", datos);
}

function modificarContenedor(id, datos) {
    return pedirAlServidor(API_GESTION + "/contenedores?id=" + id, "PUT", datos);
}

function borrarContenedor(id) {
    return pedirAlServidor(API_GESTION + "/contenedores?id=" + id, "DELETE");
}

function obtenerTiposResiduo() {
    return pedirAlServidor(API_GESTION + "/tipos_residuo");
}

// ----- Centros de acopio -----
function obtenerCentros() {
    return pedirAlServidor(API_GESTION + "/centros");
}

function crearCentro(datos) {
    return pedirAlServidor(API_GESTION + "/centros", "POST", datos);
}

function modificarCentro(id, datos) {
    return pedirAlServidor(API_GESTION + "/centros?id=" + id, "PUT", datos);
}

function borrarCentro(id) {
    return pedirAlServidor(API_GESTION + "/centros?id=" + id, "DELETE");
}

function obtenerOperarios() {
    return pedirAlServidor(API_GESTION + "/operarios");
}

// ----- Maquinaria -----
function obtenerMaquinaria() {
    return pedirAlServidor(API_GESTION + "/maquinaria");
}

function crearMaquina(datos) {
    return pedirAlServidor(API_GESTION + "/maquinaria", "POST", datos);
}

function modificarMaquina(id, datos) {
    return pedirAlServidor(API_GESTION + "/maquinaria?id=" + id, "PUT", datos);
}

function borrarMaquina(id) {
    return pedirAlServidor(API_GESTION + "/maquinaria?id=" + id, "DELETE");
}

function obtenerInstalaciones() {
    return pedirAlServidor(API_GESTION + "/instalaciones");
}

// ----- Mapa -----
function vaciarContenedor(id) {
    return pedirAlServidor(API_GESTION + "/vaciar?id=" + id, "POST");
}

function reportarProblema(datos) {
    return pedirAlServidor(API_GESTION + "/incidencias", "POST", datos);
}

// Limites de los municipios (archivo GeoJSON descargado de la Intendencia).
// Si el archivo no esta, devuelve null y el mapa funciona igual.
async function obtenerLimitesMunicipios() {
    try {
        const respuesta = await fetch("js/datos/municipios.json");
        if (!respuesta.ok) {
            return null;
        }
        return await respuesta.json();
    } catch (error) {
        return null;
    }
}

// ----- Incidencias -----
// filtros es opcional, por ejemplo "estado=Abierta" o "desde=2026-11-01&hasta=2026-11-30"
function obtenerIncidencias(filtros) {
    let direccion = API_GESTION + "/incidencias";
    if (filtros) {
        direccion = direccion + "?" + filtros;
    }
    return pedirAlServidor(direccion);
}

function seguimientoIncidencia(id, datos) {
    return pedirAlServidor(API_GESTION + "/incidencias?id=" + id, "PUT", datos);
}

// ----- Panel de control -----
// mes es como "2026-11"
function obtenerEstadisticas(mes) {
    return pedirAlServidor(API_GESTION + "/estadisticas?mes=" + mes);
}

function obtenerAlertas() {
    return pedirAlServidor(API_GESTION + "/alertas");
}

// ----- Avisos de los vecinos -----
function avisarProblema(datos) {
    return pedirAlServidor(API_GESTION + "/notificaciones", "POST", datos);
}

function obtenerAvisos() {
    return pedirAlServidor(API_GESTION + "/notificaciones");
}
