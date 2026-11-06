<?php
// Modelo Incidencia: problemas en los contenedores (roto, desbordado, falta de recoleccion)

class Incidencia
{
    private $conn;

    // Consulta que trae la incidencia con los datos y la ubicacion de su contenedor
    private $consulta = "SELECT i.id_incidencia AS id, i.id_contenedor, i.estado, i.prioridad, i.fecha,
                                i.descripcion, i.zona_incidencia, c.calle, c.municipio, c.latitud, c.longitud
                         FROM incidencias i
                         JOIN contenedores c ON i.id_contenedor = c.id_contenedor";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function obtenerTodas()
    {
        $result = mysqli_query($this->conn, $this->consulta . " ORDER BY i.fecha DESC, i.id_incidencia DESC");

        $incidencias = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $incidencias[] = $row;
        }
        return $incidencias;
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->conn->prepare($this->consulta . " WHERE i.id_incidencia = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Registra una incidencia nueva. Queda Abierta y con la fecha de hoy.
    public function agregar($idContenedor, $zona, $prioridad, $descripcion)
    {
        $stmt = $this->conn->prepare("INSERT INTO incidencias (id_contenedor, fecha_deteccion, zona_incidencia, estado, prioridad, fecha, descripcion)
                                      VALUES (?, CURDATE(), ?, 'Abierta', ?, CURDATE(), ?)");
        $stmt->bind_param("isss", $idContenedor, $zona, $prioridad, $descripcion);
        $stmt->execute();

        return $this->obtenerPorId($this->conn->insert_id);
    }
}
