<?php
// Tests de los endpoints de la API de Gestion.
// Le hacen pedidos a la API como si fueran el navegador y revisan las respuestas.
// Necesitan Apache y MySQL prendidos, con la base de datos y los datos de prueba importados.
// Lo que crean lo borran al final.
//
// Se corren con: php tests/test_api.php
// Si las APIs estan en otra direccion:
//   php tests/test_api.php http://localhost:8080/sigeru-api-gestion/api.php http://localhost:8080/sigeru-api-usuarios/api.php

require_once __DIR__ . "/funciones_test.php";

$api = "http://localhost/sigeru-api-gestion/api.php";
$apiUsuarios = "http://localhost/sigeru-api-usuarios/api.php";
if (isset($argv[1])) {
    $api = $argv[1];
}
if (isset($argv[2])) {
    $apiUsuarios = $argv[2];
}
echo "Probando la API en $api\n";

// La sesion se inicia en la API de Usuarios y la comparten las dos APIs
entrar($apiUsuarios, "admin", "admin@sigeru.uy", "admin123");
entrar($apiUsuarios, "operario", "operario@sigeru.uy", "123456");
entrar($apiUsuarios, "recolector", "recolector@sigeru.uy", "123456");
entrar($apiUsuarios, "vecino", "vecino@sigeru.uy", "123456");

// Matricula distinta cada vez, para que no choque con otros camiones
$matricula = "TST " . rand(1000, 9999);

// ---------------------------------------------------------------
titulo("Permisos por rol");

$r = pedir("GET", $api . "/contenedores", null, "nadie");
verificar($r['codigo'] == 401, "Sin sesión no se pueden ver los contenedores (401)");

$r = pedir("GET", $api . "/contenedores", null, "vecino");
verificar($r['codigo'] == 200, "El vecino puede ver los contenedores");

$r = pedir("POST", $api . "/contenedores", ["calle" => "x", "zona" => "y", "estado" => "Funcional", "id_residuo" => 1], "vecino");
verificar($r['codigo'] == 403, "El vecino no puede crear contenedores (403)");

$r = pedir("GET", $api . "/camiones", null, "vecino");
verificar($r['codigo'] == 403, "El vecino no puede ver los camiones (403)");

$r = pedir("GET", $api . "/camiones", null, "recolector");
verificar($r['codigo'] == 200, "El recolector puede ver los camiones");

$r = pedir("POST", $api . "/camiones", ["matricula" => $matricula, "estado" => "Operativo", "disponibilidad" => "Disponible"], "recolector");
verificar($r['codigo'] == 403, "El recolector no puede crear camiones (403)");

$r = pedir("GET", $api . "/operarios", null, "recolector");
verificar($r['codigo'] == 403, "El recolector no puede ver la lista de operarios (403)");

$r = pedir("GET", $api . "/centros", null, "recolector");
verificar($r['codigo'] == 200, "El recolector puede ver los centros de acopio");

// ---------------------------------------------------------------
titulo("CRUD de contenedores (operario)");

$r = pedir("POST", $api . "/contenedores", ["calle" => "Calle de prueba 123", "zona" => "Centro", "municipio" => "B", "estado" => "Funcional", "id_residuo" => 2, "latitud" => "-34.9", "longitud" => "-56.19"], "operario");
verificar($r['codigo'] == 201, "Crea un contenedor");
$idContenedor = $r['json']['contenedor']['id'];

$r = pedir("GET", $api . "/contenedores?id=" . $idContenedor, null, "operario");
verificar($r['codigo'] == 200 && $r['json']['calle'] == "Calle de prueba 123", "Lo encuentra por su id");

$r = pedir("PUT", $api . "/contenedores?id=" . $idContenedor, ["calle" => "Calle de prueba 456", "zona" => "Centro", "estado" => "Desbordado", "id_residuo" => 3], "operario");
verificar($r['codigo'] == 200 && $r['json']['contenedor']['estado'] == "Desbordado", "Lo modifica");

$r = pedir("POST", $api . "/contenedores", ["calle" => "x", "zona" => "y", "estado" => "Lleno", "id_residuo" => 1], "operario");
verificar($r['codigo'] == 400, "Con un estado que no existe devuelve 400");

$r = pedir("POST", $api . "/contenedores", ["calle" => "x", "zona" => "y", "estado" => "Funcional", "id_residuo" => 99], "operario");
verificar($r['codigo'] == 400, "Con un tipo de residuo que no existe devuelve 400");

$r = pedir("POST", $api . "/contenedores", ["calle" => "x", "zona" => "y", "estado" => "Funcional", "id_residuo" => 1, "latitud" => "-30", "longitud" => "-56"], "operario");
verificar($r['codigo'] == 400, "Con una ubicación fuera de Montevideo devuelve 400");

$r = pedir("POST", $api . "/contenedores", ["calle" => "x", "zona" => "y", "estado" => "Funcional", "id_residuo" => 1, "municipio" => "Z"], "operario");
verificar($r['codigo'] == 400, "Con un municipio que no existe devuelve 400");

$r = pedir("DELETE", $api . "/contenedores?id=2", null, "operario");
verificar($r['codigo'] == 409, "No deja borrar un contenedor con incidencias (409)");

$r = pedir("DELETE", $api . "/contenedores?id=" . $idContenedor, null, "operario");
verificar($r['codigo'] == 200, "Borra el contenedor de prueba");

$r = pedir("GET", $api . "/contenedores?id=" . $idContenedor, null, "operario");
verificar($r['codigo'] == 404, "El contenedor borrado ya no existe (404)");

// ---------------------------------------------------------------
titulo("CRUD de camiones (administrador)");

$r = pedir("POST", $api . "/camiones", ["matricula" => $matricula, "estado" => "Operativo", "disponibilidad" => "Disponible", "capacidad_carga" => "9000"], "admin");
verificar($r['codigo'] == 201, "Crea un camión");
$idCamion = $r['json']['camion']['id'];

$r = pedir("POST", $api . "/camiones", ["matricula" => $matricula, "estado" => "Operativo", "disponibilidad" => "Disponible"], "admin");
verificar($r['codigo'] == 409, "No deja repetir la matrícula (409)");

$r = pedir("PUT", $api . "/camiones?id=" . $idCamion, ["matricula" => "SBA 1234", "estado" => "Operativo", "disponibilidad" => "Disponible"], "admin");
verificar($r['codigo'] == 409, "No deja poner la matrícula de otro camión (409)");

$r = pedir("PUT", $api . "/camiones?id=" . $idCamion, ["matricula" => $matricula, "estado" => "En mantenimiento", "disponibilidad" => "No disponible", "capacidad_carga" => "mucho"], "admin");
verificar($r['codigo'] == 400, "Con una capacidad que no es un número devuelve 400");

$r = pedir("PUT", $api . "/camiones?id=" . $idCamion, ["matricula" => $matricula, "estado" => "En mantenimiento", "disponibilidad" => "No disponible"], "admin");
verificar($r['codigo'] == 200 && $r['json']['camion']['estado'] == "En mantenimiento", "Modifica el camión");

$r = pedir("DELETE", $api . "/camiones?id=" . $idCamion, null, "admin");
verificar($r['codigo'] == 200, "Borra el camión de prueba");

// ---------------------------------------------------------------
titulo("CRUD de centros de acopio (administrador)");

$r = pedir("POST", $api . "/centros", ["nombre" => "Centro de prueba", "capacidad_actual" => "100", "capacidad_max" => "500", "id_operario" => 2, "residuos" => [1, 2]], "admin");
verificar($r['codigo'] == 201 && count($r['json']['centro']['residuos']) == 2, "Crea un centro con 2 tipos de residuo");
$idCentro = $r['json']['centro']['id'];

$r = pedir("PUT", $api . "/centros?id=" . $idCentro, ["nombre" => "Centro de prueba", "capacidad_actual" => "900", "capacidad_max" => "500"], "admin");
verificar($r['codigo'] == 400, "No deja que la capacidad actual sea mayor que la máxima");

$r = pedir("PUT", $api . "/centros?id=" . $idCentro, ["nombre" => "Centro de prueba", "id_operario" => 1], "admin");
verificar($r['codigo'] == 400, "No deja asignar un usuario que no es operario");

$r = pedir("PUT", $api . "/centros?id=" . $idCentro, ["nombre" => "Centro de prueba 2", "residuos" => [3]], "admin");
verificar($r['codigo'] == 200 && $r['json']['centro']['nombre'] == "Centro de prueba 2", "Modifica el centro");

$r = pedir("DELETE", $api . "/centros?id=" . $idCentro, null, "admin");
verificar($r['codigo'] == 200, "Borra el centro de prueba");

// ---------------------------------------------------------------
titulo("CRUD de maquinaria (operario)");

$r = pedir("POST", $api . "/maquinaria", ["nombre" => "Máquina de prueba", "tipo" => "Prensa", "estado" => "Operativa", "id_instalacion" => 1], "operario");
verificar($r['codigo'] == 201, "Crea una máquina");
$idMaquina = $r['json']['maquina']['id'];

$r = pedir("POST", $api . "/maquinaria", ["nombre" => "X", "estado" => "Rota"], "operario");
verificar($r['codigo'] == 400, "Con un estado que no existe devuelve 400");

$r = pedir("PUT", $api . "/maquinaria?id=" . $idMaquina, ["nombre" => "Máquina de prueba", "estado" => "En reparación", "id_instalacion" => 99], "operario");
verificar($r['codigo'] == 400, "Con una instalación que no existe devuelve 400");

$r = pedir("PUT", $api . "/maquinaria?id=" . $idMaquina, ["nombre" => "Máquina de prueba", "estado" => "En reparación", "id_instalacion" => ""], "operario");
verificar($r['codigo'] == 200 && $r['json']['maquina']['estado'] == "En reparación", "Modifica la máquina");

$r = pedir("DELETE", $api . "/maquinaria?id=" . $idMaquina, null, "operario");
verificar($r['codigo'] == 200, "Borra la máquina de prueba");

// ---------------------------------------------------------------
titulo("Mapa: recorridos, incidencias y avisos");

$r = pedir("GET", $api . "/recorridos", null, "recolector");
verificar($r['codigo'] == 200 && count($r['json']) == 1, "El recolector ve solo el recorrido de su cuadrilla");

$r = pedir("GET", $api . "/recorridos", null, "admin");
verificar($r['codigo'] == 200 && count($r['json']) == 2, "El administrador ve todos los recorridos");

$r = pedir("GET", $api . "/incidencias", null, "vecino");
verificar($r['codigo'] == 403, "El vecino no puede ver las incidencias (403)");

$r = pedir("POST", $api . "/incidencias", ["id_contenedor" => 5, "problema" => "Raro", "prioridad" => "Media"], "recolector");
verificar($r['codigo'] == 400, "Reportar un problema que no existe devuelve 400");

$r = pedir("POST", $api . "/notificaciones", ["id_contenedor" => 5, "problema" => "Roto"], "admin");
verificar($r['codigo'] == 403, "Solo los vecinos pueden avisar problemas (403)");

$r = pedir("GET", $api . "/notificaciones", null, "vecino");
verificar($r['codigo'] == 200, "El vecino ve sus avisos");

$r = pedir("POST", $api . "/vaciar?id=99999", null, "recolector");
verificar($r['codigo'] == 404, "Vaciar un contenedor que no existe devuelve 404");

$r = pedir("GET", $api . "/no-existe", null, "admin");
verificar($r['codigo'] == 404, "Un endpoint que no existe devuelve 404");

mostrarResumen();
