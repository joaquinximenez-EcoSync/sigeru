<?php
// Modelo Contenedor: guarda, busca, modifica y borra contenedores en la base de datos

class Contenedor
{
    private $conn;

    // Consulta que trae el contenedor con el nombre de su tipo de residuo
    private $consulta = "SELECT c.id_contenedor AS id, c.calle, c.zona, c.estado, c.capacidad,
                                c.id_residuo, t.nombre AS residuo
                         FROM contenedores c
                         LEFT JOIN tipos_residuo t ON c.id_residuo = t.id_residuo";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function obtenerTodos()
    {
        $result = mysqli_query($this->conn, $this->consulta . " ORDER BY c.id_contenedor");

        $contenedores = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $contenedores[] = $row;
        }
        return $contenedores;
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->conn->prepare($this->consulta . " WHERE c.id_contenedor = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Tipos de residuo (regla RNE01 del MER: cada contenedor guarda un solo tipo)
    public function obtenerTiposResiduo()
    {
        $result = mysqli_query($this->conn, "SELECT id_residuo, nombre FROM tipos_residuo ORDER BY nombre");

        $tipos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $tipos[] = $row;
        }
        return $tipos;
    }

    // Devuelve true si existe ese tipo de residuo
    public function existeResiduo($idResiduo)
    {
        $stmt = $this->conn->prepare("SELECT id_residuo FROM tipos_residuo WHERE id_residuo = ?");
        $stmt->bind_param("i", $idResiduo);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() != null;
    }

    // Devuelve true si el contenedor tiene incidencias registradas
    public function tieneIncidencias($id)
    {
        $stmt = $this->conn->prepare("SELECT id_incidencia FROM incidencias WHERE id_contenedor = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() != null;
    }

    public function agregar($datos)
    {
        $calle = $datos['calle'];
        $zona = $datos['zona'];
        $estado = $datos['estado'];
        $capacidad = $datos['capacidad'] ?? "";
        $idResiduo = $datos['id_residuo'];

        // Si no escribieron capacidad, se guarda vacio (NULL)
        if ($capacidad == "") {
            $capacidad = null;
        }

        $stmt = $this->conn->prepare("INSERT INTO contenedores (calle, zona, estado, capacidad, id_residuo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $calle, $zona, $estado, $capacidad, $idResiduo);
        $stmt->execute();

        return $this->obtenerPorId($this->conn->insert_id);
    }

    public function modificar($id, $datos)
    {
        $calle = $datos['calle'];
        $zona = $datos['zona'];
        $estado = $datos['estado'];
        $capacidad = $datos['capacidad'] ?? "";
        $idResiduo = $datos['id_residuo'];

        if ($capacidad == "") {
            $capacidad = null;
        }

        $stmt = $this->conn->prepare("UPDATE contenedores SET calle = ?, zona = ?, estado = ?, capacidad = ?, id_residuo = ? WHERE id_contenedor = ?");
        $stmt->bind_param("ssssii", $calle, $zona, $estado, $capacidad, $idResiduo, $id);
        $stmt->execute();

        return $this->obtenerPorId($id);
    }

    public function eliminar($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM contenedores WHERE id_contenedor = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}
