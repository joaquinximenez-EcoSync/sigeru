// Modelo: pedidos a la API de Gestion

function obtenerContenedores() {
    return pedirAlServidor(API_GESTION + "/contenedores");
}

function crearContenedor(datos) {
    return pedirAlServidor(API_GESTION + "/contenedores", "POST", datos);
}

function obtenerTiposResiduo() {
    return pedirAlServidor(API_GESTION + "/tipos_residuo");
}

function obtenerCamiones() {
    return pedirAlServidor(API_GESTION + "/camiones");
}

function crearCamion(datos) {
    return pedirAlServidor(API_GESTION + "/camiones", "POST", datos);
}
