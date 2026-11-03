<?php
// Modelo Usuario
// En esta entrega los usuarios se guardan en $_SESSION['usuarios'] (sin base de datos)

class Usuario
{
    public function __construct()
    {
        // Si es la primera vez, cargamos un usuario administrador de prueba
        if (!isset($_SESSION['usuarios'])) {
            $_SESSION['usuarios'] = [];
            $this->agregar([
                "primer_nombre" => "Usuario",
                "primer_apellido" => "Administrador",
                "email" => "admin@sigeru.uy",
                "contrasena" => "admin123",
                "rol" => "Administrador"
            ]);
        }
    }

    // Devuelve todos los usuarios sin la contraseña
    public function obtenerTodos()
    {
        $lista = [];
        foreach ($_SESSION['usuarios'] as $usuario) {
            $lista[] = $this->sinContrasena($usuario);
        }
        return $lista;
    }

    public function obtenerPorId($id)
    {
        foreach ($_SESSION['usuarios'] as $usuario) {
            if ($usuario['id'] == $id) {
                return $this->sinContrasena($usuario);
            }
        }
        return null;
    }

    // Este si devuelve la contraseña, porque se usa para el login
    public function buscarPorEmail($email)
    {
        foreach ($_SESSION['usuarios'] as $usuario) {
            if ($usuario['email'] == $email) {
                return $usuario;
            }
        }
        return null;
    }

    // Guarda un usuario nuevo y devuelve sus datos
    public function agregar($datos)
    {
        $nuevo = [
            "id" => count($_SESSION['usuarios']) + 1,
            "primer_nombre" => $datos['primer_nombre'],
            "segundo_nombre" => $datos['segundo_nombre'] ?? "",
            "primer_apellido" => $datos['primer_apellido'],
            "segundo_apellido" => $datos['segundo_apellido'] ?? "",
            "email" => $datos['email'],
            "telefono" => $datos['telefono'] ?? "",
            // La contraseña se guarda encriptada
            "contrasena" => password_hash($datos['contrasena'], PASSWORD_DEFAULT),
            "rol" => $datos['rol'],
            // Datos de vecinos
            "calle" => $datos['calle'] ?? "",
            "zona" => $datos['zona'] ?? "",
            // Datos de operarios
            "especialidad" => $datos['especialidad'] ?? ""
        ];

        $_SESSION['usuarios'][] = $nuevo;
        return $this->sinContrasena($nuevo);
    }

    private function sinContrasena($usuario)
    {
        unset($usuario['contrasena']);
        return $usuario;
    }
}
