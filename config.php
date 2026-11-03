<?php
// Configuracion general de la API de Usuarios

// Paginas que tienen permitido usar esta API (landing y frontend)
$origenesPermitidos = [
    "http://localhost"
];

// Roles que existen en el sistema
$rolesValidos = ["Administrador", "Operario", "Recolector", "Vecino"];

// CORS: como el frontend esta en otra direccion, hay que permitirle usar la API
$origen = $_SERVER['HTTP_ORIGIN'] ?? "";
if (in_array($origen, $origenesPermitidos)) {
    header("Access-Control-Allow-Origin: $origen");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
}

// El navegador manda una solicitud OPTIONS antes de cada POST, solo hay que responderle OK
if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    http_response_code(204);
    exit;
}

// En esta entrega los datos se guardan en la sesion (todavia no hay base de datos)
session_name("SIGERU_USUARIOS");
session_start();

header("Content-Type: application/json; charset=utf-8");
