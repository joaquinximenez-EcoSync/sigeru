<?php
// Modelo Notificacion: avisos que hacen los vecinos sobre un contenedor.
// En el MER es una entidad debil de Vecino: su clave es (id_usuario, id_notificacion).
// Cada aviso genera una incidencia (relacion Genera2).

class Notificacion
{
    private $conn;

    // Consulta que trae el aviso con el estado de su incidencia y los datos del contenedor
    private $consulta = "SELECT n.id_usuario, n.id_notificacion AS id, n.fecha, n.descripcion,
                                i.estado AS estado_incidencia, c.id_contenedor, c.calle, c.zona,
                                CONCAT(u.primer_nombre, ' ', u.primer_apellido) AS vecino
                         FROM notificaciones n
                         JOIN usuarios u ON n.id_usuario = u.id_usuario
                         LEFT JOIN incidencias i ON n.id_incidencia = i.id_incidencia
                         LEFT JOIN contenedores c ON i.id_contenedor = c.id_contenedor";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Avisos de un vecino
    public function obtenerDelVecino($idUsuario)
    {
        $stmt = $this->conn->prepare($this->consulta . " WHERE n.id_usuario = ? ORDER BY n.fecha DESC, n.id_notificacion DESC");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $avisos = [];
        while ($row = $result->fetch_assoc()) {
            $avisos[] = $row;
        }
        return $avisos;
    }

    // Avisos de todos los vecinos (para el administrador y el operario)
    public function obtenerTodas()
    {
        $result = mysqli_query($this->conn, $this->consulta . " ORDER BY n.fecha DESC, n.id_notificacion DESC");

        $avisos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $avisos[] = $row;
        }
        return $avisos;
    }

    // Devuelve true si el vecino ya aviso de ese contenedor y todavia no se resolvio
    public function yaAviso($idUsuario, $idContenedor)
    {
        $stmt = $this->conn->prepare("SELECT n.id_notificacion FROM notificaciones n
                                      JOIN incidencias i ON n.id_incidencia = i.id_incidencia
                                      WHERE n.id_usuario = ? AND i.id_contenedor = ? AND i.estado <> 'Resuelta'");
        $stmt->bind_param("ii", $idUsuario, $idContenedor);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() != null;
    }

    // Cuando el personal cambia el estado de una incidencia, el aviso del vecino cambia igual
    // (Recibida -> En proceso -> Resuelta)
    public function cambiarEstado($idIncidencia, $estado)
    {
        if ($estado == "Abierta") {
            $estado = "Recibida";
        }
        $stmt = $this->conn->prepare("UPDATE notificaciones SET estado = ? WHERE id_incidencia = ?");
        $stmt->bind_param("si", $estado, $idIncidencia);
        $stmt->execute();
    }

    // Devuelve el id del vecino que aviso de esa incidencia (o null si la registro el personal)
    public function vecinoDeLaIncidencia($idIncidencia)
    {
        $stmt = $this->conn->prepare("SELECT id_usuario FROM notificaciones WHERE id_incidencia = ?");
        $stmt->bind_param("i", $idIncidencia);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();

        if ($fila == null) {
            return null;
        }
        return $fila['id_usuario'];
    }

    // Guarda el aviso. Como es entidad debil, su numero es el siguiente de ese vecino (1, 2, 3...).
    public function agregar($idUsuario, $idIncidencia, $descripcion)
    {
        // Buscamos el numero mas alto de los avisos de ese vecino
        $stmt = $this->conn->prepare("SELECT MAX(id_notificacion) AS ultimo FROM notificaciones WHERE id_usuario = ?");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $ultimo = $stmt->get_result()->fetch_assoc()['ultimo'];

        // Si es su primer aviso, el numero es 1
        if ($ultimo == null) {
            $numero = 1;
        } else {
            $numero = $ultimo + 1;
        }

        $stmt = $this->conn->prepare("INSERT INTO notificaciones (id_usuario, id_notificacion, estado, descripcion, fecha, id_incidencia)
                                      VALUES (?, ?, 'Recibida', ?, CURDATE(), ?)");
        $stmt->bind_param("iisi", $idUsuario, $numero, $descripcion, $idIncidencia);
        $stmt->execute();

        return $numero;
    }
}
