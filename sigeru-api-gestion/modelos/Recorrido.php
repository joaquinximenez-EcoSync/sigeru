<?php
// Modelo Recorrido: que ruta hace cada cuadrilla, con que camion y que contenedores le tocan.
// Un recorrido pasa por varias zonas (barrios) y recoge los contenedores de esas zonas.

class Recorrido
{
    private $conn;

    // Consulta que trae el recorrido con el nombre de la ruta, la cuadrilla y el camion
    private $consulta = "SELECT r.id_recorrido AS id, ru.nombre AS ruta, cu.nombre AS cuadrilla,
                                ca.matricula AS camion, r.fecha_asignacion, r.hora_inicio, r.hora_fin
                         FROM recorridos r
                         JOIN rutas ru ON r.id_ruta = ru.id_ruta
                         JOIN cuadrillas cu ON r.id_cuadrilla = cu.id_cuadrilla
                         LEFT JOIN camiones ca ON r.id_camion = ca.id_camion";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Todos los recorridos (para el administrador y el operario)
    public function obtenerTodos()
    {
        $result = mysqli_query($this->conn, $this->consulta . " ORDER BY r.id_recorrido");

        $recorridos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $recorridos[] = $this->agregarZonasYContenedores($row);
        }
        return $recorridos;
    }

    // Solo los recorridos de la cuadrilla del recolector
    public function obtenerDelRecolector($idUsuario)
    {
        $stmt = $this->conn->prepare($this->consulta . " JOIN recolectores re ON re.id_cuadrilla = r.id_cuadrilla
                                                        WHERE re.id_usuario = ? ORDER BY r.id_recorrido");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $recorridos = [];
        while ($row = $result->fetch_assoc()) {
            $recorridos[] = $this->agregarZonasYContenedores($row);
        }
        return $recorridos;
    }

    // Le agrega al recorrido la lista de zonas y los contenedores que hay en esas zonas
    private function agregarZonasYContenedores($recorrido)
    {
        $stmt = $this->conn->prepare("SELECT nombre FROM zonas WHERE id_recorrido = ? ORDER BY id_zona");
        $stmt->bind_param("i", $recorrido['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $recorrido['zonas'] = [];
        while ($row = $result->fetch_assoc()) {
            $recorrido['zonas'][] = $row['nombre'];
        }

        $stmt = $this->conn->prepare("SELECT c.id_contenedor AS id, c.calle, c.zona, c.estado, c.latitud, c.longitud
                                      FROM contenedores c
                                      JOIN zonas z ON c.zona = z.nombre
                                      WHERE z.id_recorrido = ? AND c.latitud IS NOT NULL
                                      ORDER BY z.id_zona, c.id_contenedor");
        $stmt->bind_param("i", $recorrido['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $recorrido['contenedores'] = [];
        while ($row = $result->fetch_assoc()) {
            $recorrido['contenedores'][] = $row;
        }
        return $recorrido;
    }
}
