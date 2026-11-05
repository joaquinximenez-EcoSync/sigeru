<?php
// Modelo Usuario: guarda y busca usuarios en la base de datos

class Usuario
{
    private $conn;

    // Consulta que trae los datos del usuario y los de su rol (vecino u operario)
    private $consulta = "SELECT u.id_usuario AS id, u.primer_nombre, u.segundo_nombre, u.primer_apellido,
                                u.segundo_apellido, u.email, u.telefono, u.rol,
                                v.calle, v.zona, o.especialidad
                         FROM usuarios u
                         LEFT JOIN vecinos v ON u.id_usuario = v.id_usuario
                         LEFT JOIN operarios o ON u.id_usuario = o.id_usuario";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Devuelve todos los usuarios (sin la contraseña)
    public function obtenerTodos()
    {
        $result = mysqli_query($this->conn, $this->consulta . " ORDER BY u.id_usuario");

        $usuarios = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $usuarios[] = $row;
        }
        return $usuarios;
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->conn->prepare($this->consulta . " WHERE u.id_usuario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Este si devuelve la contraseña, porque se usa para el login
    public function buscarPorEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Guarda un usuario nuevo y devuelve sus datos
    public function agregar($datos)
    {
        $nombre = $datos['primer_nombre'];
        $apellido = $datos['primer_apellido'];
        $email = $datos['email'];
        $telefono = $datos['telefono'] ?? "";
        $rol = $datos['rol'];
        // La contraseña se guarda encriptada
        $contrasena = password_hash($datos['contrasena'], PASSWORD_DEFAULT);

        if ($telefono == "") {
            $telefono = null;
        }

        $stmt = $this->conn->prepare("INSERT INTO usuarios (primer_nombre, primer_apellido, email, telefono, contrasena, rol) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $apellido, $email, $telefono, $contrasena, $rol);
        $stmt->execute();

        // Numero de id que le dio la base de datos al usuario nuevo
        $id = $this->conn->insert_id;

        // Segun el rol, tambien se guarda en su tabla
        if ($rol == "Vecino") {
            $calle = $datos['calle'] ?? "";
            $zona = $datos['zona'] ?? "";
            $stmt = $this->conn->prepare("INSERT INTO vecinos (id_usuario, calle, zona) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id, $calle, $zona);
            $stmt->execute();
        }
        if ($rol == "Operario") {
            $especialidad = $datos['especialidad'] ?? "";
            $stmt = $this->conn->prepare("INSERT INTO operarios (id_usuario, especialidad) VALUES (?, ?)");
            $stmt->bind_param("is", $id, $especialidad);
            $stmt->execute();
        }
        if ($rol == "Administrador") {
            $stmt = $this->conn->prepare("INSERT INTO administradores (id_usuario) VALUES (?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        if ($rol == "Recolector") {
            $stmt = $this->conn->prepare("INSERT INTO recolectores (id_usuario) VALUES (?)");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }

        return $this->obtenerPorId($id);
    }
}
