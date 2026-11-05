<?php
// API RESTful de Usuarios - SiGeRU
//
// Direcciones que tiene esta API (endpoints):
//   POST api.php/registro      registro de un vecino (desde la landing)
//   POST api.php/login         inicio de sesion
//   POST api.php/logout        cierre de sesion
//   GET  api.php/sesion        usuario que inicio sesion
//   GET  api.php/usuarios      lista de usuarios
//   GET  api.php/usuarios?id=1 un usuario
//   POST api.php/usuarios      crear un usuario con cualquier rol

require_once "config.php";
require_once "vistas/respuesta.php";
require_once "modelos/Usuario.php";
require_once "controladores/usuarios.php";

// Tipo de pedido: GET (pedir datos) o POST (mandar datos)
$metodo = $_SERVER['REQUEST_METHOD'];

// Que se pidio: lo que viene despues de api.php/ (por ejemplo login)
$endpoint = trim($_SERVER['PATH_INFO'] ?? "", "/");

// Datos que mando el formulario (llegan en formato JSON)
$datos = json_decode(file_get_contents("php://input"), true) ?? [];

switch ($endpoint) {
    case "registro":
        if ($metodo == "POST") registrarUsuario($datos);
        break;

    case "login":
        if ($metodo == "POST") iniciarSesion($datos);
        break;

    case "logout":
        if ($metodo == "POST") cerrarSesion();
        break;

    case "sesion":
        if ($metodo == "GET") sesionActual();
        break;

    case "usuarios":
        if ($metodo == "GET") {
            if (isset($_GET['id'])) {
                obtenerUsuario($_GET['id']);
            } else {
                listarUsuarios();
            }
        }
        if ($metodo == "POST") crearUsuario($datos);
        break;
}

// Si llega hasta aca es porque se pidio algo que la API no tiene
responder(["error" => "Endpoint no encontrado"], 404);
