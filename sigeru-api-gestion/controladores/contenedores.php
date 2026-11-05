<?php
// Controlador de contenedores: valida los datos y usa el modelo Contenedor

// Devuelve la lista de todos los contenedores (GET contenedores)
function listarContenedores($contenedor) {
    responder($contenedor->obtenerTodos());
}

// Busca un contenedor por su numero de id (GET contenedores?id=1)
function obtenerContenedor($contenedor, $id) {
    $encontrado = $contenedor->obtenerPorId($id);

    if ($encontrado == null) {
        responder(["error" => "Contenedor no encontrado"], 404);
    }
    responder($encontrado);
}

// Devuelve la lista de tipos de residuo (GET tipos_residuo)
function listarTiposResiduo($contenedor) {
    responder($contenedor->obtenerTiposResiduo());
}

// Registra un contenedor nuevo (POST contenedores)
function registrarContenedor($contenedor, $datos) {
    $estadosContenedor = ["Funcional", "Roto", "Desbordado"];

    if (empty($datos['calle']) || empty($datos['zona'])) {
        responder(["error" => "La calle y la zona son obligatorias"], 400);
    }
    if (!in_array($datos['estado'] ?? "", $estadosContenedor)) {
        responder(["error" => "El estado no es válido"], 400);
    }
    if (empty($datos['id_residuo']) || !$contenedor->existeResiduo($datos['id_residuo'])) {
        responder(["error" => "Elige un tipo de residuo"], 400);
    }
    if (!empty($datos['capacidad']) && !is_numeric($datos['capacidad'])) {
        responder(["error" => "La capacidad debe ser un número"], 400);
    }

    $nuevo = $contenedor->agregar($datos);
    responder(["mensaje" => "Contenedor registrado correctamente", "contenedor" => $nuevo], 201);
}
