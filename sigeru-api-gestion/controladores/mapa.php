<?php
// Controlador de las cosas que usa el mapa: incidencias y recorridos

// Devuelve la lista de incidencias (GET incidencias)
function listarIncidencias($incidencia) {
    responder($incidencia->obtenerTodas());
}

// Reportar un problema en un contenedor desde el mapa (POST incidencias)
// Si el problema es "Roto" o "Desbordado", tambien se cambia el estado del contenedor.
function reportarIncidencia($incidencia, $contenedor, $datos) {
    $problemas = ["Roto", "Desbordado", "Falta de recolección"];
    $prioridades = ["Baja", "Media", "Alta"];

    $encontrado = $contenedor->obtenerPorId($datos['id_contenedor'] ?? 0);
    if ($encontrado == null) {
        responder(["error" => "Contenedor no encontrado"], 404);
    }
    if (!in_array($datos['problema'] ?? "", $problemas)) {
        responder(["error" => "Elige qué problema tiene el contenedor"], 400);
    }
    if (!in_array($datos['prioridad'] ?? "", $prioridades)) {
        responder(["error" => "La prioridad no es válida"], 400);
    }

    // La descripcion empieza con el problema, por ejemplo "Desbordado: hay bolsas en la vereda"
    $descripcion = $datos['problema'];
    if (!empty($datos['descripcion'])) {
        $descripcion = $descripcion . ": " . trim($datos['descripcion']);
    }

    $nueva = $incidencia->agregar($encontrado['id'], $encontrado['zona'], $datos['prioridad'], $descripcion);

    if ($datos['problema'] == "Roto" || $datos['problema'] == "Desbordado") {
        $contenedor->cambiarEstado($encontrado['id'], $datos['problema']);
    }

    responder(["mensaje" => "Problema reportado correctamente", "incidencia" => $nueva], 201);
}

// Recorridos (GET recorridos): el recolector ve solo los de su cuadrilla, el resto ve todos
function listarRecorridos($recorrido) {
    $usuario = $_SESSION['usuario_logueado'];

    if ($usuario['rol'] == "Recolector") {
        responder($recorrido->obtenerDelRecolector($usuario['id']));
    }
    responder($recorrido->obtenerTodos());
}
