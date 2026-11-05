<?php
// API RESTful de Usuarios - SiGeRU
//
// Direcciones que tiene esta API (endpoints):
//   POST   api.php/registro        registro de un vecino (desde la landing)
//   POST   api.php/login           inicio de sesion
//   POST   api.php/logout          cierre de sesion
//   GET    api.php/sesion          usuario que inicio sesion
//   GET    api.php/usuarios        lista de usuarios          (solo administrador)
//   GET    api.php/usuarios?id=1   un usuario                 (solo administrador)
//   POST   api.php/usuarios        crear un usuario           (solo administrador)
//   PUT    api.php/usuarios?id=1   modificar un usuario       (solo administrador)
//   DELETE api.php/usuarios?id=1   borrar un usuario          (solo administrador)

require_once "config.php";
require_once "conexion.php";
require_once "vistas/respuesta.php";
require_once "seguridad.php";
require_once "modelos/Usuario.php";
require_once "controladores/usuarios.php";

// Tipo de pedido: GET (pedir), POST (crear), PUT (modificar) o DELETE (borrar)
$metodo = $_SERVER['REQUEST_METHOD'];

// Que se pidio: lo que viene despues de api.php/ (por ejemplo login)
$endpoint = trim($_SERVER['PATH_INFO'] ?? "", "/");

// Id que viene en la direccion (para ver, modificar o borrar uno)
$id = $_GET['id'] ?? null;

// Datos que mando el formulario (llegan en formato JSON)
$datos = json_decode(file_get_contents("php://input"), true) ?? [];

// Modelo que usa la base de datos
$usuario = new Usuario($conn);

switch ($endpoint) {
    case "registro":
        if ($metodo == "POST") registrarUsuario($usuario, $datos);
        break;

    case "login":
        if ($metodo == "POST") iniciarSesion($usuario, $datos);
        break;

    case "logout":
        if ($metodo == "POST") cerrarSesion();
        break;

    case "sesion":
        if ($metodo == "GET") sesionActual();
        break;

    case "usuarios":
        // RNE04: solo un administrador puede ver, crear, modificar o borrar usuarios
        verificarRol(["Administrador"]);

        if ($metodo == "GET") {
            if ($id != null) {
                obtenerUsuario($usuario, $id);
            } else {
                listarUsuarios($usuario);
            }
        }
        if ($metodo == "POST") crearUsuario($usuario, $datos);
        if ($metodo == "PUT") modificarUsuario($usuario, $id, $datos);
        if ($metodo == "DELETE") eliminarUsuario($usuario, $id);
        break;
}

// Si llega hasta aca es porque se pidio algo que la API no tiene
responder(["error" => "Endpoint no encontrado"], 404);
