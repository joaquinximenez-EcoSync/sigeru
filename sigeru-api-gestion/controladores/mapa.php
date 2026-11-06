<?php
// Controlador de los avisos de los vecinos (los hacen desde el mapa)

// Un vecino avisa que hay un problema en un contenedor (POST notificaciones)
// Se guarda el aviso del vecino y se crea una incidencia abierta para que la vea el personal.
// El estado del contenedor no se cambia hasta que el personal lo revise.
function avisarProblema($notificacion, $incidencia, $contenedor, $mensaje, $datos) {
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

    $nueva = $incidencia->agregar($encontrado['id'], $encontrado['zona'], $datos['problema'], "Media", "Aviso de un vecino - " . $descripcion);
    $notificacion->agregar($idVecino, $nueva['id'], $descripcion);

    // Le llega una notificacion a todo el personal, y al vecino la confirmacion
    $nombreVecino = $_SESSION['usuario_logueado']['nombre'];
    $texto = "📣 " . $nombreVecino . " avisó que el contenedor de " . $encontrado['calle'] . " está: " . $datos['problema'];
    $mensaje->enviarARol("Administrador", $texto, "incidencias.html");
    $mensaje->enviarARol("Operario", $texto, "incidencias.html");
    $mensaje->enviarARol("Recolector", $texto, "incidencias.html");
    $mensaje->enviarAUsuario($idVecino, "✅ Recibimos tu aviso sobre el contenedor de " . $encontrado['calle'] . ". Te avisamos cuando lo atiendan.", "mapa.html");

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
