<?php
// Controlador de camiones: valida los datos y usa el modelo Camion

// Devuelve la lista de todos los camiones (GET camiones)
function listarCamiones($camion) {
    responder($camion->obtenerTodos());
}

// Busca un camion por su numero de id (GET camiones?id=1)
function obtenerCamion($camion, $id) {
    $encontrado = $camion->obtenerPorId($id);

    if ($encontrado == null) {
        responder(["error" => "Camión no encontrado"], 404);
    }
    responder($encontrado);
}

// Registra un camion nuevo (POST camiones)
function registrarCamion($camion, $datos) {
    $estadosCamion = ["Operativo", "En mantenimiento", "Fuera de servicio"];
    $disponibilidades = ["Disponible", "En ruta", "No disponible"];

    if (empty($datos['matricula'])) {
        responder(["error" => "La matrícula es obligatoria"], 400);
    }
    if ($camion->buscarPorMatricula(strtoupper($datos['matricula'])) != null) {
        responder(["error" => "Ya existe un camión con esa matrícula"], 409);
    }
    if (!in_array($datos['estado'] ?? "", $estadosCamion)) {
        responder(["error" => "El estado no es válido"], 400);
    }
    if (!in_array($datos['disponibilidad'] ?? "", $disponibilidades)) {
        responder(["error" => "La disponibilidad no es válida"], 400);
    }
    if (!empty($datos['capacidad_carga']) && !is_numeric($datos['capacidad_carga'])) {
        responder(["error" => "La capacidad de carga debe ser un número"], 400);
    }

    $nuevo = $camion->agregar($datos);
    responder(["mensaje" => "Camión registrado correctamente", "camion" => $nuevo], 201);
}
