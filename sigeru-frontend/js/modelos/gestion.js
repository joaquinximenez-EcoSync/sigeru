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

// ----- Camiones -----
function obtenerCamiones() {
    return pedirAlServidor(API_GESTION + "/camiones");
}

function crearCamion(datos) {
    return pedirAlServidor(API_GESTION + "/camiones", "POST", datos);
}

function modificarCamion(id, datos) {
    return pedirAlServidor(API_GESTION + "/camiones?id=" + id, "PUT", datos);
}

function borrarCamion(id) {
    return pedirAlServidor(API_GESTION + "/camiones?id=" + id, "DELETE");
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

function obtenerIncidencias() {
    return pedirAlServidor(API_GESTION + "/incidencias");
}

function reportarProblema(datos) {
    return pedirAlServidor(API_GESTION + "/incidencias", "POST", datos);
}

function obtenerRecorridos() {
    return pedirAlServidor(API_GESTION + "/recorridos");
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
