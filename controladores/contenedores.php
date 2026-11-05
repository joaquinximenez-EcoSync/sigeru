<?php
// Controlador de contenedores: valida los datos y usa el modelo Contenedor

$estadosContenedor = ["Funcional", "Roto", "Desbordado"];

// Devuelve la lista de todos los contenedores (GET contenedores)
function listarContenedores() {
    $contenedor = new Contenedor();
    responder($contenedor->obtenerTodos());
}

// Busca un contenedor por su numero de id (GET contenedores?id=1)
function obtenerContenedor($id) {
    $contenedor = new Contenedor();
    $encontrado = $contenedor->obtenerPorId($id);

    if ($encontrado == null) {
        responder(["error" => "Contenedor no encontrado"], 404);
    }
    responder($encontrado);
}

// Devuelve la lista de tipos de residuo (GET tipos_residuo)
function listarTiposResiduo() {
    $contenedor = new Contenedor();
    responder($contenedor->obtenerTiposResiduo());
}

// Registra un contenedor nuevo (POST contenedores)
function registrarContenedor($datos) {
    global $estadosContenedor;
    $contenedor = new Contenedor();

    if (empty($datos['calle']) || empty($datos['zona'])) {
        responder(["error" => "La calle y la zona son obligatorias"], 400);
    }
    if (!in_array($datos['estado'] ?? "", $estadosContenedor)) {
        responder(["error" => "El estado no es válido"], 400);
    }
    if ($contenedor->nombreResiduo($datos['id_residuo'] ?? "") == null) {
        responder(["error" => "Elige un tipo de residuo"], 400);
    }
    if (!empty($datos['capacidad']) && !is_numeric($datos['capacidad'])) {
        responder(["error" => "La capacidad debe ser un número"], 400);
    }

    $nuevo = $contenedor->agregar($datos);
    responder(["mensaje" => "Contenedor registrado correctamente", "contenedor" => $nuevo], 201);
}
