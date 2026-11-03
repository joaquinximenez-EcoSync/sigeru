<?php
// API RESTful de Gestion - SiGeRU
//
// Endpoints:
//   GET  api.php/contenedores       lista de contenedores
//   GET  api.php/contenedores?id=1  un contenedor
//   POST api.php/contenedores       registrar un contenedor
//   GET  api.php/tipos_residuo      lista de tipos de residuo
//   GET  api.php/camiones           lista de camiones
//   GET  api.php/camiones?id=1      un camion
//   POST api.php/camiones           registrar un camion

require_once "config.php";
require_once "vistas/respuesta.php";
require_once "modelos/Contenedor.php";
require_once "modelos/Camion.php";
require_once "controladores/contenedores.php";
require_once "controladores/camiones.php";

// Verbo HTTP de la solicitud
$metodo = $_SERVER['REQUEST_METHOD'];

// Endpoint pedido (lo que viene despues de api.php/)
$endpoint = trim($_SERVER['PATH_INFO'] ?? "", "/");

// Datos que llegan en formato JSON
$datos = json_decode(file_get_contents("php://input"), true) ?? [];

switch ($endpoint) {
    case "contenedores":
        if ($metodo == "GET") {
            if (isset($_GET['id'])) {
                obtenerContenedor($_GET['id']);
            } else {
                listarContenedores();
            }
        }
        if ($metodo == "POST") registrarContenedor($datos);
        break;

    case "tipos_residuo":
        if ($metodo == "GET") listarTiposResiduo();
        break;

    case "camiones":
        if ($metodo == "GET") {
            if (isset($_GET['id'])) {
                obtenerCamion($_GET['id']);
            } else {
                listarCamiones();
            }
        }
        if ($metodo == "POST") registrarCamion($datos);
        break;
}

// Si llega hasta aca, el endpoint o el metodo no existen
responder(["error" => "Endpoint no encontrado"], 404);
