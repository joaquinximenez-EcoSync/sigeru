<?php
// Tests de los endpoints de la API de Usuarios.
// Le hacen pedidos a la API como si fueran el navegador y revisan las respuestas.
// Necesitan Apache y MySQL prendidos, con la base de datos y los datos de prueba importados.
// Lo que crean lo borran al final.
//
// Se corren con: php tests/test_api.php
// Si la API esta en otra direccion: php tests/test_api.php http://localhost:8080/sigeru-api-usuarios/api.php

require_once __DIR__ . "/funciones_test.php";

$api = "http://localhost/sigeru-api-usuarios/api.php";
if (isset($argv[1])) {
    $api = $argv[1];
}
echo "Probando la API en $api\n";

// Correo distinto cada vez, para que no choque con otros usuarios
$correoPrueba = "prueba" . time() . "@mail.com";

titulo("Inicio de sesion");

$r = entrar($api, "admin", "admin@sigeru.uy", "contraseña-mal");
verificar($r['codigo'] == 401, "Con la contraseña mal devuelve 401");

$r = pedir("GET", $api . "/sesion", null, "admin");
verificar($r['codigo'] == 401, "Sin sesión, /sesion devuelve 401");

$r = entrar($api, "admin", "admin@sigeru.uy", "admin123");
verificar($r['codigo'] == 200 && $r['json']['usuario']['rol'] == "Administrador", "El administrador inicia sesión");

$r = pedir("GET", $api . "/sesion", null, "admin");
verificar($r['codigo'] == 200 && $r['json']['usuario']['id'] == 1, "/sesion devuelve quién inició sesión");

$r = pedir("POST", $api . "/login", ["email" => ""], "otro");
verificar($r['codigo'] == 400, "Login sin datos devuelve 400");

titulo("Permisos (RNE04: solo el administrador maneja usuarios)");

$r = pedir("GET", $api . "/usuarios", null, "nadie");
verificar($r['codigo'] == 401, "Sin sesión no se pueden ver los usuarios (401)");

entrar($api, "vecino", "vecino@sigeru.uy", "123456");
$r = pedir("GET", $api . "/usuarios", null, "vecino");
verificar($r['codigo'] == 403, "Un vecino no puede ver los usuarios (403)");

entrar($api, "operario", "operario@sigeru.uy", "123456");
$r = pedir("POST", $api . "/usuarios", ["primer_nombre" => "X"], "operario");
verificar($r['codigo'] == 403, "Un operario no puede crear usuarios (403)");

titulo("Registro desde la landing");

$r = pedir("POST", $api . "/registro", [
    "primer_nombre" => "Prueba",
    "primer_apellido" => "Registro",
    "email" => "registro" . $correoPrueba,
    "contrasena" => "123456",
    "rol" => "Administrador"
], "landing");
verificar($r['codigo'] == 201, "Registro correcto devuelve 201");
verificar($r['json']['usuario']['rol'] == "Vecino", "El que se registra solo queda como Vecino aunque mande otro rol");
$idRegistrado = $r['json']['usuario']['id'];

$r = pedir("POST", $api . "/registro", ["primer_nombre" => "A", "primer_apellido" => "B", "email" => "admin@sigeru.uy", "contrasena" => "123456"], "landing");
verificar($r['codigo'] == 409, "Con un correo que ya existe devuelve 409");

$r = pedir("POST", $api . "/registro", ["primer_nombre" => "A", "primer_apellido" => "B", "email" => "correo-malo", "contrasena" => "123456"], "landing");
verificar($r['codigo'] == 400, "Con un correo mal escrito devuelve 400");

$r = pedir("POST", $api . "/registro", ["primer_nombre" => "A", "primer_apellido" => "B", "email" => "x" . $correoPrueba, "contrasena" => "123"], "landing");
verificar($r['codigo'] == 400, "Con una contraseña de menos de 6 caracteres devuelve 400");

titulo("CRUD de usuarios (administrador)");

$r = pedir("POST", $api . "/usuarios", [
    "primer_nombre" => "Ana",
    "primer_apellido" => "Prueba",
    "email" => $correoPrueba,
    "contrasena" => "123456",
    "rol" => "Recolector"
], "admin");
verificar($r['codigo'] == 201 && $r['json']['usuario']['rol'] == "Recolector", "Crea un usuario con rol Recolector");
$idNuevo = $r['json']['usuario']['id'];

$r = pedir("GET", $api . "/usuarios?id=" . $idNuevo, null, "admin");
verificar($r['codigo'] == 200 && $r['json']['email'] == $correoPrueba, "Lo encuentra por su id");

$r = pedir("POST", $api . "/usuarios", ["primer_nombre" => "Ana", "primer_apellido" => "Prueba", "email" => "y" . $correoPrueba, "contrasena" => "123456", "rol" => "Jefe"], "admin");
verificar($r['codigo'] == 400, "Con un rol que no existe devuelve 400");

$r = pedir("PUT", $api . "/usuarios?id=" . $idNuevo, [
    "primer_nombre" => "Ana María",
    "primer_apellido" => "Prueba",
    "email" => $correoPrueba,
    "contrasena" => "",
    "rol" => "Operario",
    "especialidad" => "Choferes"
], "admin");
verificar($r['codigo'] == 200 && $r['json']['usuario']['rol'] == "Operario", "Modifica el usuario y le cambia el rol");

$r = pedir("PUT", $api . "/usuarios?id=" . $idNuevo, ["primer_nombre" => "Ana", "primer_apellido" => "Prueba", "email" => "admin@sigeru.uy", "rol" => "Operario"], "admin");
verificar($r['codigo'] == 409, "No deja poner el correo de otro usuario (409)");

$r = pedir("PUT", $api . "/usuarios?id=99999", ["primer_nombre" => "A", "primer_apellido" => "B", "email" => "z" . $correoPrueba, "rol" => "Vecino"], "admin");
verificar($r['codigo'] == 404, "Modificar un usuario que no existe devuelve 404");

$r = pedir("DELETE", $api . "/usuarios?id=1", null, "admin");
verificar($r['codigo'] == 400, "El administrador no se puede borrar a sí mismo");

$r = pedir("DELETE", $api . "/usuarios?id=" . $idNuevo, null, "admin");
verificar($r['codigo'] == 200, "Borra el usuario de prueba");

$r = pedir("GET", $api . "/usuarios?id=" . $idNuevo, null, "admin");
verificar($r['codigo'] == 404, "El usuario borrado ya no existe (404)");

// Borramos tambien el que se registro desde la landing
$r = pedir("DELETE", $api . "/usuarios?id=" . $idRegistrado, null, "admin");
verificar($r['codigo'] == 200, "Borra el usuario que se registró en la prueba");

titulo("Cerrar sesion");

$r = pedir("POST", $api . "/logout", null, "admin");
verificar($r['codigo'] == 200, "Cierra la sesión");

$r = pedir("GET", $api . "/usuarios", null, "admin");
verificar($r['codigo'] == 401, "Después de cerrar sesión ya no puede ver los usuarios");

$r = pedir("GET", $api . "/no-existe", null, "admin");
verificar($r['codigo'] == 404, "Un endpoint que no existe devuelve 404");

mostrarResumen();
