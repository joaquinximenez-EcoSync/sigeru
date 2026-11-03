<?php
// API RESTful de Usuarios - SiGeRU
//
// Endpoints:
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

// Verbo HTTP de la solicitud
$metodo = $_SERVER['REQUEST_METHOD'];

// Endpoint pedido (lo que viene despues de api.php/)
$endpoint = trim($_SERVER['PATH_INFO'] ?? "", "/");

// Datos que llegan en formato JSON
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

// Si llega hasta aca, el endpoint o el metodo no existen
responder(["error" => "Endpoint no encontrado"], 404);
