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

// Un vecino avisa que hay un problema en un contenedor (POST notificaciones)
// Se guarda el aviso del vecino y se crea una incidencia abierta para que la vea el personal.
// El estado del contenedor no se cambia hasta que el personal lo revise.
function avisarProblema($notificacion, $incidencia, $contenedor, $datos) {
    $problemas = ["Desbordado", "Roto", "Falta de recolección"];
    $idVecino = $_SESSION['usuario_logueado']['id'];

    $encontrado = $contenedor->obtenerPorId($datos['id_contenedor'] ?? 0);
    if ($encontrado == null) {
        responder(["error" => "Contenedor no encontrado"], 404);
    }
    if (!in_array($datos['problema'] ?? "", $problemas)) {
        responder(["error" => "Elige qué problema tiene el contenedor"], 400);
    }
    $comentario = trim($datos['comentario'] ?? "");
    if (strlen($comentario) > 200) {
        responder(["error" => "El comentario puede tener hasta 200 caracteres"], 400);
    }
    if ($notificacion->yaAviso($idVecino, $encontrado['id'])) {
        responder(["error" => "Ya avisaste de este contenedor. Puedes ver cómo va en Mis avisos."], 409);
    }

    $descripcion = $datos['problema'];
    if ($comentario != "") {
        $descripcion = $descripcion . ": " . $comentario;
    }

    $nueva = $incidencia->agregar($encontrado['id'], $encontrado['zona'], "Media", "Aviso de un vecino - " . $descripcion);
    $notificacion->agregar($idVecino, $nueva['id'], $descripcion);

    responder(["mensaje" => "¡Gracias por avisar! El equipo de recolección ya lo puede ver."], 201);
}

// Avisos de vecinos (GET notificaciones): el vecino ve los suyos, el resto ve todos
function listarAvisos($notificacion) {
    $usuario = $_SESSION['usuario_logueado'];

    if ($usuario['rol'] == "Vecino") {
        responder($notificacion->obtenerDelVecino($usuario['id']));
    }
    responder($notificacion->obtenerTodas());
}
