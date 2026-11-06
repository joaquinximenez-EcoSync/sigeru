// Modelo: pedidos a la API de Recoleccion

// ----- Camiones -----
function obtenerCamiones() {
    return pedirAlServidor(API_RECOLECCION + "/camiones");
}

function crearCamion(datos) {
    return pedirAlServidor(API_RECOLECCION + "/camiones", "POST", datos);
}

function modificarCamion(id, datos) {
    return pedirAlServidor(API_RECOLECCION + "/camiones?id=" + id, "PUT", datos);
}

function borrarCamion(id) {
    return pedirAlServidor(API_RECOLECCION + "/camiones?id=" + id, "DELETE");
}

// ----- Mantenimientos de un camion -----
function obtenerMantenimientos(idCamion) {
    return pedirAlServidor(API_RECOLECCION + "/mantenimientos?camion=" + idCamion);
}

function crearMantenimiento(datos) {
    return pedirAlServidor(API_RECOLECCION + "/mantenimientos", "POST", datos);
}

function borrarMantenimiento(idCamion, nro) {
    return pedirAlServidor(API_RECOLECCION + "/mantenimientos?camion=" + idCamion + "&nro=" + nro, "DELETE");
}

// ----- Recorridos (rutas asignadas a las cuadrillas) -----
function obtenerRecorridos() {
    return pedirAlServidor(API_RECOLECCION + "/recorridos");
}

function crearRecorrido(datos) {
    return pedirAlServidor(API_RECOLECCION + "/recorridos", "POST", datos);
}

function modificarRecorrido(id, datos) {
    return pedirAlServidor(API_RECOLECCION + "/recorridos?id=" + id, "PUT", datos);
}

function borrarRecorrido(id) {
    return pedirAlServidor(API_RECOLECCION + "/recorridos?id=" + id, "DELETE");
}

function obtenerCuadrillas() {
    return pedirAlServidor(API_RECOLECCION + "/cuadrillas");
}

function obtenerRutas() {
    return pedirAlServidor(API_RECOLECCION + "/rutas");
}

function obtenerZonas() {
    return pedirAlServidor(API_RECOLECCION + "/zonas");
}
