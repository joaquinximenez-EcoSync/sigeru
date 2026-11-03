// Modelo: pedidos a la API de Usuarios

function iniciarSesion(email, contrasena) {
    return pedirAlServidor(API_USUARIOS + "/login", "POST", { email: email, contrasena: contrasena });
}

function cerrarSesion() {
    return pedirAlServidor(API_USUARIOS + "/logout", "POST");
}

function obtenerSesion() {
    return pedirAlServidor(API_USUARIOS + "/sesion");
}

function obtenerUsuarios() {
    return pedirAlServidor(API_USUARIOS + "/usuarios");
}

function crearUsuario(datos) {
    return pedirAlServidor(API_USUARIOS + "/usuarios", "POST", datos);
}
