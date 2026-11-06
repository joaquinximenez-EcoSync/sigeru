<?php
// Controlador de las incidencias: registro y seguimiento

// Lista de incidencias (GET incidencias). Se puede filtrar con
// ?estado=Abierta  ?contenedor=2  ?desde=2026-11-01&hasta=2026-11-30
function listarIncidencias($incidencia) {
    $estado = $_GET['estado'] ?? "";
    $contenedor = $_GET['contenedor'] ?? "";
    $desde = $_GET['desde'] ?? "";
    $hasta = $_GET['hasta'] ?? "";

    if ($estado != "" && !in_array($estado, ["Abierta", "En proceso", "Resuelta"])) {
        responder(["error" => "El estado no es válido"], 400);
    }
    // Las fechas tienen que venir como 2026-11-06
    if ($desde != "" && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $desde)) {
        responder(["error" => "La fecha desde no es válida"], 400);
    }
    if ($hasta != "" && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $hasta)) {
        responder(["error" => "La fecha hasta no es válida"], 400);
    }
    if ($desde != "" && $hasta != "" && $desde > $hasta) {
        responder(["error" => "La fecha desde no puede ser después de la fecha hasta"], 400);
    }

    responder($incidencia->obtenerTodas($estado, $contenedor, $desde, $hasta));
}

function obtenerIncidencia($incidencia, $id) {
    $encontrada = $incidencia->obtenerPorId($id);
    if ($encontrada == null) {
        responder(["error" => "Incidencia no encontrada"], 404);
    }
    responder($encontrada);
}

// Registrar un problema en un contenedor (POST incidencias), desde el mapa o desde la pagina de incidencias.
// Si el problema es "Roto" o "Desbordado", tambien se cambia el estado del contenedor.
function reportarIncidencia($incidencia, $contenedor, $mensaje, $datos) {
    $problemas = ["Desbordado", "Roto", "Falta de recolección"];
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

    $nueva = $incidencia->agregar($encontrado['id'], $encontrado['zona'], $datos['problema'], $datos['prioridad'], $descripcion);

    if ($datos['problema'] == "Roto" || $datos['problema'] == "Desbordado") {
        $contenedor->cambiarEstado($encontrado['id'], $datos['problema']);
    }

    // Le avisamos al administrador y a los operarios (menos al que la registro)
    $usuario = $_SESSION['usuario_logueado'];
    $texto = "⚠️ " . $usuario['nombre'] . " registró una incidencia: contenedor de " . $encontrado['calle'] . " (" . $datos['problema'] . ", prioridad " . $datos['prioridad'] . ")";
    $mensaje->enviarARol("Administrador", $texto, "incidencias.html", $usuario['id']);
    $mensaje->enviarARol("Operario", $texto, "incidencias.html", $usuario['id']);

    responder(["mensaje" => "Problema reportado correctamente", "incidencia" => $nueva], 201);
}

// Seguimiento de una incidencia (PUT incidencias?id=1)
// - El administrador y el operario cambian el estado, la prioridad y la cuadrilla (recorrido) que la resuelve.
// - El recolector solo cambia el estado, y solo de las incidencias que tiene asignadas su cuadrilla.
// - Una incidencia Resuelta ya no se puede cambiar.
// - Cuando se resuelve y el contenedor no tiene otras incidencias pendientes, el contenedor vuelve a Funcional.
function seguimientoIncidencia($incidencia, $contenedor, $notificacion, $mensaje, $id, $datos) {
    $estados = ["Abierta", "En proceso", "Resuelta"];
    $prioridades = ["Baja", "Media", "Alta"];
    $usuario = $_SESSION['usuario_logueado'];

    $actual = $incidencia->obtenerPorId($id);
    if ($actual == null) {
        responder(["error" => "Incidencia no encontrada"], 404);
    }
    if ($actual['estado'] == "Resuelta") {
        responder(["error" => "La incidencia ya está resuelta y no se puede cambiar"], 409);
    }

    $estado = $datos['estado'] ?? "";
    if (!in_array($estado, $estados)) {
        responder(["error" => "El estado no es válido"], 400);
    }

    if ($usuario['rol'] == "Recolector") {
        // El recolector no cambia la prioridad ni la cuadrilla: quedan como estaban
        $prioridad = $actual['prioridad'];
        $idRecorrido = $actual['id_recorrido'];

        if ($idRecorrido == null || !$incidencia->esRecorridoDelRecolector($idRecorrido, $usuario['id'])) {
            responder(["error" => "Solo puedes cambiar las incidencias asignadas a tu cuadrilla"], 403);
        }
    } else {
        $prioridad = $datos['prioridad'] ?? "";
        if (!in_array($prioridad, $prioridades)) {
            responder(["error" => "La prioridad no es válida"], 400);
        }

        // El recorrido puede quedar vacio (sin cuadrilla asignada)
        $idRecorrido = $datos['id_recorrido'] ?? "";
        if ($idRecorrido == "") {
            $idRecorrido = null;
        } else if (!$incidencia->existeRecorrido($idRecorrido)) {
            responder(["error" => "El recorrido elegido no existe"], 400);
        }
    }

    $modificada = $incidencia->modificarSeguimiento($id, $estado, $prioridad, $idRecorrido);

    // El aviso del vecino (si hay) cambia de estado igual que la incidencia, y le avisamos
    $notificacion->cambiarEstado($id, $estado);
    $idVecino = $notificacion->vecinoDeLaIncidencia($id);
    if ($idVecino != null && $estado != $actual['estado']) {
        if ($estado == "Resuelta") {
            $mensaje->enviarAUsuario($idVecino, "✅ ¡Listo! Se resolvió el problema que avisaste en el contenedor de " . $actual['calle'] . ". Gracias por avisar.", "mapa.html");
        } else if ($estado == "En proceso") {
            $mensaje->enviarAUsuario($idVecino, "🔧 Tu aviso sobre el contenedor de " . $actual['calle'] . " está en proceso: una cuadrilla ya lo está atendiendo.", "mapa.html");
        }
    }

    // Si le asignaron una cuadrilla nueva, les avisamos a sus recolectores
    if ($modificada['id_recorrido'] != null && $modificada['id_recorrido'] != $actual['id_recorrido']) {
        $mensaje->enviarACuadrilla($incidencia->cuadrillaDelRecorrido($modificada['id_recorrido']),
            "🚛 A tu cuadrilla le asignaron la incidencia #" . $id . ": contenedor de " . $actual['calle'] . " (" . $actual['tipo'] . ", prioridad " . $modificada['prioridad'] . ")", "incidencias.html");
    }

    // Si la resolvio un recolector, le avisamos al administrador y a los operarios
    if ($estado == "Resuelta" && $usuario['rol'] == "Recolector") {
        $texto = "✅ " . $usuario['nombre'] . " resolvió la incidencia #" . $id . " en " . $actual['calle'];
        $mensaje->enviarARol("Administrador", $texto, "incidencias.html");
        $mensaje->enviarARol("Operario", $texto, "incidencias.html");
    }

    // Si ya no le quedan problemas, el contenedor vuelve a estar Funcional
    if ($estado == "Resuelta" && $incidencia->contarSinResolver($actual['id_contenedor']) == 0) {
        $contenedor->cambiarEstado($actual['id_contenedor'], "Funcional");
    }

    responder(["mensaje" => "Incidencia actualizada correctamente", "incidencia" => $modificada]);
}

// Volumen recolectado entre dos fechas (GET volumen?desde=2026-11-01&hasta=2026-11-30)
function obtenerVolumen($estadistica) {
    $desde = $_GET['desde'] ?? "";
    $hasta = $_GET['hasta'] ?? "";
    if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $desde) || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $hasta)) {
        responder(["error" => "Elige las dos fechas"], 400);
    }
    if ($desde > $hasta) {
        responder(["error" => "La fecha desde no puede ser después de la fecha hasta"], 400);
    }

    responder([
        "total" => $estadistica->totalRecolectado($desde, $hasta),
        "por_municipio" => $estadistica->kilosPorMunicipio($desde, $hasta),
        "por_residuo" => $estadistica->kilosPorResiduo($desde, $hasta),
        "por_contenedor" => $estadistica->vaciadosPorContenedor($desde, $hasta)
    ]);
}

// Estadisticas para el panel de control (GET estadisticas). Con ?mes=2026-11 se elige el mes.
function obtenerEstadisticas($estadistica) {
    $mes = $_GET['mes'] ?? date("Y-m");
    if (!preg_match("/^[0-9]{4}-[0-9]{2}$/", $mes)) {
        responder(["error" => "El mes no es válido"], 400);
    }

    responder([
        "mes" => $mes,
        "por_estado" => $estadistica->incidenciasPorEstado(),
        "top_desbordados" => $estadistica->topDesbordados($mes),
        "por_municipio" => $estadistica->sinResolverPorMunicipio(),
        "por_tipo" => $estadistica->porTipo($mes),
        "promedio_dias" => $estadistica->promedioDiasResolucion(),
        "resueltas_mes" => $estadistica->resueltasDelMes($mes)
    ]);
}
