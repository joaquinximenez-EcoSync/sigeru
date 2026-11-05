<?php
// Configuracion general de la API de Usuarios

// Paginas que tienen permiso para usar esta API (landing y frontend)
$paginasPermitidas = [
    "http://localhost"
];

// Roles que existen en el sistema
$rolesValidos = ["Administrador", "Operario", "Recolector", "Vecino"];

// Si la pagina que pide datos esta en la lista, le damos permiso para usar la API
$paginaQuePide = $_SERVER['HTTP_ORIGIN'] ?? "";
if (in_array($paginaQuePide, $paginasPermitidas)) {
    header("Access-Control-Allow-Origin: $paginaQuePide");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
}

// Antes de mandar datos, el navegador pregunta si tiene permiso. Le respondemos que si.
if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    http_response_code(204);
    exit;
}

// En esta entrega los datos se guardan en la sesion (todavia no hay base de datos)
session_name("SIGERU_USUARIOS");
session_start();

header("Content-Type: application/json; charset=utf-8");
