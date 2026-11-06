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

// ---------------------------------------------------------------
titulo("Permisos por rol");

$r = pedir("GET", $api . "/contenedores", null, "nadie");
verificar($r['codigo'] == 401, "Sin sesión no se pueden ver los contenedores (401)");

$r = pedir("GET", $api . "/contenedores", null, "vecino");
verificar($r['codigo'] == 200, "El vecino puede ver los contenedores");

$r = pedir("POST", $api . "/contenedores", ["calle" => "x", "zona" => "y", "estado" => "Funcional", "id_residuo" => 1], "vecino");
verificar($r['codigo'] == 403, "El vecino no puede crear contenedores (403)");

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
titulo("Mapa: incidencias y avisos");

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

// ---------------------------------------------------------------
titulo("Seguimiento de incidencias");

$r = pedir("POST", $api . "/incidencias", ["id_contenedor" => 5, "problema" => "Roto", "prioridad" => "Baja", "descripcion" => "Prueba del test"], "operario");
verificar($r['codigo'] == 201 && $r['json']['incidencia']['tipo'] == "Roto", "Registra una incidencia con su tipo");
$idIncidencia = $r['json']['incidencia']['id'];

$r = pedir("GET", $api . "/contenedores?id=5", null, "operario");
verificar($r['json']['estado'] == "Roto", "El contenedor queda Roto");

$r = pedir("GET", $api . "/incidencias?id=" . $idIncidencia, null, "recolector");
verificar($r['codigo'] == 200 && $r['json']['estado'] == "Abierta", "La busca por su id");

$r = pedir("GET", $api . "/incidencias?contenedor=5", null, "operario");
verificar($r['codigo'] == 200 && count($r['json']) == 1, "Historial del contenedor 5");

$r = pedir("GET", $api . "/incidencias?estado=Cualquiera", null, "operario");
verificar($r['codigo'] == 400, "Con un estado que no existe devuelve 400");

$r = pedir("GET", $api . "/incidencias?desde=2026-11-30&hasta=2026-11-01", null, "operario");
verificar($r['codigo'] == 400, "Si la fecha desde es después de la fecha hasta devuelve 400");

$r = pedir("PUT", $api . "/incidencias?id=" . $idIncidencia, ["estado" => "En proceso"], "recolector");
verificar($r['codigo'] == 403, "El recolector no puede cambiar una incidencia que no es de su cuadrilla (403)");

$r = pedir("PUT", $api . "/incidencias?id=" . $idIncidencia, ["estado" => "En proceso", "prioridad" => "Alta", "id_recorrido" => 999], "operario");
verificar($r['codigo'] == 400, "Con un recorrido que no existe devuelve 400");

$r = pedir("PUT", $api . "/incidencias?id=" . $idIncidencia, ["estado" => "En proceso", "prioridad" => "Alta", "id_recorrido" => 1], "operario");
verificar($r['codigo'] == 200 && $r['json']['incidencia']['estado'] == "En proceso" && $r['json']['incidencia']['prioridad'] == "Alta", "El operario la pasa a En proceso y le asigna una cuadrilla");

$r = pedir("GET", $api . "/alertas", null, "recolector");
$encontrada = false;
foreach ($r['json'] as $a) {
    if (str_contains($a['texto'], "#" . $idIncidencia)) $encontrada = true;
}
verificar($r['codigo'] == 200 && $encontrada, "Al recolector le aparece la alerta de la incidencia de su cuadrilla");

$r = pedir("PUT", $api . "/incidencias?id=" . $idIncidencia, ["estado" => "Resuelta", "prioridad" => "Baja"], "recolector");
verificar($r['codigo'] == 200 && $r['json']['incidencia']['estado'] == "Resuelta" && $r['json']['incidencia']['prioridad'] == "Alta", "Ahora el recolector la resuelve (pero no le cambia la prioridad)");
verificar($r['json']['incidencia']['fecha_resolucion'] == date("Y-m-d"), "Guarda la fecha de resolución");

$r = pedir("GET", $api . "/contenedores?id=5", null, "operario");
verificar($r['json']['estado'] == "Funcional", "El contenedor vuelve a estar Funcional");

$r = pedir("PUT", $api . "/incidencias?id=" . $idIncidencia, ["estado" => "Abierta", "prioridad" => "Alta"], "operario");
verificar($r['codigo'] == 409, "Una incidencia resuelta ya no se puede cambiar (409)");

$r = pedir("PUT", $api . "/incidencias?id=" . $idIncidencia, ["estado" => "Abierta"], "vecino");
verificar($r['codigo'] == 403, "El vecino no puede hacer el seguimiento (403)");

// Borramos la incidencia de prueba (la API no tiene para borrar incidencias)
$base = conectarBaseReal();
mysqli_query($base, "DELETE FROM incidencias WHERE id_incidencia = " . $idIncidencia);

// ---------------------------------------------------------------
titulo("Estadísticas y alertas");

$r = pedir("GET", $api . "/estadisticas?mes=2026-11", null, "operario");
verificar($r['codigo'] == 200 && isset($r['json']['top_desbordados']) && isset($r['json']['por_estado']), "El operario ve las estadísticas");

$r = pedir("GET", $api . "/estadisticas?mes=noviembre", null, "operario");
verificar($r['codigo'] == 400, "Con un mes mal escrito devuelve 400");

$r = pedir("GET", $api . "/estadisticas", null, "vecino");
verificar($r['codigo'] == 403, "El vecino no ve las estadísticas (403)");

$r = pedir("GET", $api . "/alertas", null, "admin");
verificar($r['codigo'] == 200 && count($r['json']) > 0, "El administrador tiene alertas");

$r = pedir("GET", $api . "/alertas", null, "vecino");
verificar($r['codigo'] == 200, "El vecino puede pedir sus alertas");

$r = pedir("GET", $api . "/alertas", null, "nadie");
verificar($r['codigo'] == 401, "Sin sesión no hay alertas (401)");

$r = pedir("GET", $api . "/no-existe", null, "admin");
verificar($r['codigo'] == 404, "Un endpoint que no existe devuelve 404");

mostrarResumen();
