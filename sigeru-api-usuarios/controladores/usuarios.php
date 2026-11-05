<?php
// Controlador de usuarios: valida los datos y usa el modelo Usuario

// Revisa que los datos del formulario esten bien. Si hay un error lo devuelve y corta.
// $id es el usuario que se esta modificando (null si es un usuario nuevo).
function validarUsuario($usuario, $datos, $id) {
    $rolesValidos = ["Administrador", "Operario", "Recolector", "Vecino"];

    if (empty($datos['primer_nombre']) || empty($datos['primer_apellido']) || empty($datos['email'])) {
        responder(["error" => "Completa todos los campos obligatorios"], 400);
    }

    $pattern = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$/";
    if (!preg_match($pattern, $datos['email'])) {
        responder(["error" => "El formato del correo no es válido"], 400);
    }

    // Al crear un usuario la contraseña es obligatoria. Al modificar, solo si escribieron una nueva.
    $contrasena = $datos['contrasena'] ?? "";
    if ($id == null && $contrasena == "") {
        responder(["error" => "La contraseña es obligatoria"], 400);
    }
    if ($contrasena != "" && strlen($contrasena) < 6) {
        responder(["error" => "La contraseña debe tener al menos 6 caracteres"], 400);
    }

    if (!in_array($datos['rol'] ?? "", $rolesValidos)) {
        responder(["error" => "El rol no es válido"], 400);
    }

    // El correo no puede ser de otro usuario
    $otro = $usuario->buscarPorEmail($datos['email']);
    if ($otro != null && $otro['id_usuario'] != $id) {
        responder(["error" => "Ese correo ya está registrado"], 409);
    }
}

// Devuelve la lista de todos los usuarios (GET usuarios)
function listarUsuarios($usuario) {
    responder($usuario->obtenerTodos());
}

// Busca un usuario por su numero de id (GET usuarios?id=1)
function obtenerUsuario($usuario, $id) {
    $encontrado = $usuario->obtenerPorId($id);

    if ($encontrado == null) {
        responder(["error" => "Usuario no encontrado"], 404);
    }
    responder($encontrado);
}

// Registro desde la landing: las personas que se registran solas son vecinos (POST registro)
function registrarUsuario($usuario, $datos) {
    $datos['rol'] = "Vecino";
    validarUsuario($usuario, $datos, null);

    $nuevo = $usuario->agregar($datos);
    responder(["mensaje" => "Registro realizado correctamente", "usuario" => $nuevo], 201);
}

// Crea un usuario desde el formulario del frontend, con cualquier rol (POST usuarios)
function crearUsuario($usuario, $datos) {
    validarUsuario($usuario, $datos, null);

    $nuevo = $usuario->agregar($datos);
    responder(["mensaje" => "Usuario creado correctamente", "usuario" => $nuevo], 201);
}

// Modifica un usuario (PUT usuarios?id=1)
function modificarUsuario($usuario, $id, $datos) {
    if ($usuario->obtenerPorId($id) == null) {
        responder(["error" => "Usuario no encontrado"], 404);
    }
    validarUsuario($usuario, $datos, $id);

    $modificado = $usuario->modificar($id, $datos);
    responder(["mensaje" => "Usuario modificado correctamente", "usuario" => $modificado]);
}

// Borra un usuario (DELETE usuarios?id=1)
function eliminarUsuario($usuario, $id) {
    if ($usuario->obtenerPorId($id) == null) {
        responder(["error" => "Usuario no encontrado"], 404);
    }
    // Un administrador no se puede borrar a si mismo
    if ($id == $_SESSION['usuario_logueado']['id']) {
        responder(["error" => "No puedes borrar tu propio usuario"], 400);
    }

    $usuario->eliminar($id);
    responder(["mensaje" => "Usuario eliminado correctamente"]);
}

// Inicia la sesion si el correo y la contraseña son correctos (POST login)
function iniciarSesion($usuario, $datos) {
    $email = $datos['email'] ?? "";
    $contrasena = $datos['contrasena'] ?? "";

    if (empty($email) || empty($contrasena)) {
        responder(["error" => "Completa el correo y la contraseña"], 400);
    }

    $encontrado = $usuario->buscarPorEmail($email);

    // password_verify compara la contraseña escrita con la encriptada
    if ($encontrado == null || !password_verify($contrasena, $encontrado['contrasena'])) {
        responder(["error" => "Correo o contraseña incorrectos"], 401);
    }

    // Guardamos en la sesion quien inicio sesion
    $_SESSION['usuario_logueado'] = [
        "id" => $encontrado['id_usuario'],
        "nombre" => $encontrado['primer_nombre'] . " " . $encontrado['primer_apellido'],
        "rol" => $encontrado['rol']
    ];
    responder(["mensaje" => "Bienvenido", "usuario" => $_SESSION['usuario_logueado']]);
}

// Cierra la sesion (POST logout)
function cerrarSesion() {
    unset($_SESSION['usuario_logueado']);
    responder(["mensaje" => "Sesión cerrada"]);
}

// Devuelve quien inicio sesion (GET sesion)
function sesionActual() {
    if (!isset($_SESSION['usuario_logueado'])) {
        responder(["error" => "No hay ninguna sesión iniciada"], 401);
    }
    responder(["usuario" => $_SESSION['usuario_logueado']]);
}
