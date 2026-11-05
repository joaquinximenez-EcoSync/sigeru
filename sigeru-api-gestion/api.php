<?php
// API RESTful de Gestion - SiGeRU
//
// Direcciones que tiene esta API (endpoints):
//   GET  api.php/contenedores       lista de contenedores
//   GET  api.php/contenedores?id=1  un contenedor
//   POST api.php/contenedores       registrar un contenedor
//   GET  api.php/tipos_residuo      lista de tipos de residuo
//   GET  api.php/camiones           lista de camiones
//   GET  api.php/camiones?id=1      un camion
//   POST api.php/camiones           registrar un camion

require_once "config.php";
require_once "conexion.php";
require_once "vistas/respuesta.php";
require_once "modelos/Contenedor.php";
require_once "modelos/Camion.php";
require_once "controladores/contenedores.php";
require_once "controladores/camiones.php";

// Tipo de pedido: GET (pedir datos) o POST (mandar datos)
$metodo = $_SERVER['REQUEST_METHOD'];

// Que se pidio: lo que viene despues de api.php/ (por ejemplo login)
$endpoint = trim($_SERVER['PATH_INFO'] ?? "", "/");

// Datos que mando el formulario (llegan en formato JSON)
$datos = json_decode(file_get_contents("php://input"), true) ?? [];

// Modelos que usan la base de datos
$contenedor = new Contenedor($conn);
$camion = new Camion($conn);

switch ($endpoint) {
    case "contenedores":
        if ($metodo == "GET") {
            if (isset($_GET['id'])) {
                obtenerContenedor($contenedor, $_GET['id']);
            } else {
                listarContenedores($contenedor);
            }
        }
        if ($metodo == "POST") registrarContenedor($contenedor, $datos);
        break;

    case "tipos_residuo":
        if ($metodo == "GET") listarTiposResiduo($contenedor);
        break;

    case "camiones":
        if ($metodo == "GET") {
            if (isset($_GET['id'])) {
                obtenerCamion($camion, $_GET['id']);
            } else {
                listarCamiones($camion);
            }
        }
        if ($metodo == "POST") registrarCamion($camion, $datos);
        break;
}

// Si llega hasta aca es porque se pidio algo que la API no tiene
responder(["error" => "Endpoint no encontrado"], 404);
