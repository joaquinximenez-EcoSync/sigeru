<?php
// Revisa que haya alguien con la sesion iniciada y que su rol tenga permiso.
// Si no, corta y devuelve el error.
function verificarRol($rolesPermitidos) {
    if (!isset($_SESSION['usuario_logueado'])) {
        responder(["error" => "Tenés que iniciar sesión"], 401);
    }
    if (!in_array($_SESSION['usuario_logueado']['rol'], $rolesPermitidos)) {
        responder(["error" => "No tenés permiso para hacer esto"], 403);
    }
}
