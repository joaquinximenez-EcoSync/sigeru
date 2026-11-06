<?php
// Modelo Alerta: busca las cosas que necesitan atencion, segun el rol de cada usuario

class Alerta
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    private function hacerLista($result)
    {
        $lista = [];
        while ($row = $result->fetch_assoc()) {
            $lista[] = $row;
        }
        return $lista;
    }

    // Incidencias de prioridad Alta que todavia estan Abiertas (nadie las tomo).
    // No se incluyen los desbordados de 2 dias o mas, porque esos ya salen en desbordadosSinAtender.
    public function prioridadAltaAbiertas()
    {
        $result = mysqli_query($this->conn, "SELECT i.id_incidencia AS id, c.calle, c.zona
                                             FROM incidencias i
                                             JOIN contenedores c ON i.id_contenedor = c.id_contenedor
                                             WHERE i.prioridad = 'Alta' AND i.estado = 'Abierta'
                                               AND NOT (i.tipo = 'Desbordado' AND DATEDIFF(CURDATE(), i.fecha_deteccion) >= 2)
                                             ORDER BY i.fecha_deteccion");
        return $this->hacerLista($result);
    }

    // Contenedores desbordados que llevan 2 dias o mas sin que nadie los atienda
    public function desbordadosSinAtender()
    {
        $result = mysqli_query($this->conn, "SELECT i.id_incidencia AS id, c.calle, c.zona,
                                                    DATEDIFF(CURDATE(), i.fecha_deteccion) AS dias
                                             FROM incidencias i
                                             JOIN contenedores c ON i.id_contenedor = c.id_contenedor
                                             WHERE i.tipo = 'Desbordado' AND i.estado = 'Abierta'
                                               AND DATEDIFF(CURDATE(), i.fecha_deteccion) >= 2
                                             ORDER BY dias DESC");
        return $this->hacerLista($result);
    }

    // Centros de acopio y vertederos que estan al 80% o mas de su capacidad
    public function instalacionesCasiLlenas()
    {
        $result = mysqli_query($this->conn, "SELECT id_instalacion AS id, nombre,
                                                    ROUND(capacidad_actual * 100 / capacidad_max) AS porcentaje
                                             FROM instalaciones
                                             WHERE capacidad_max > 0 AND capacidad_actual >= capacidad_max * 0.8");
        return $this->hacerLista($result);
    }

    // Avisos de vecinos cuya incidencia sigue Abierta
    public function avisosSinAtender()
    {
        $result = mysqli_query($this->conn, "SELECT n.id_notificacion AS id, c.calle
                                             FROM notificaciones n
                                             JOIN incidencias i ON n.id_incidencia = i.id_incidencia
                                             JOIN contenedores c ON i.id_contenedor = c.id_contenedor
                                             WHERE i.estado = 'Abierta'");
        return $this->hacerLista($result);
    }

    // Incidencias sin resolver que tiene asignadas la cuadrilla del recolector
    public function delRecolector($idUsuario)
    {
        $stmt = $this->conn->prepare("SELECT i.id_incidencia AS id, i.estado, i.prioridad, c.calle, c.zona
                                      FROM incidencias i
                                      JOIN contenedores c ON i.id_contenedor = c.id_contenedor
                                      JOIN recorridos r ON i.id_recorrido = r.id_recorrido
                                      JOIN recolectores re ON r.id_cuadrilla = re.id_cuadrilla
                                      WHERE re.id_usuario = ? AND i.estado <> 'Resuelta'
                                      ORDER BY i.fecha_deteccion");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        return $this->hacerLista($stmt->get_result());
    }

    // Avisos del vecino que cambiaron de estado en los ultimos 7 dias (en proceso o resueltos)
    public function avisosDelVecino($idUsuario)
    {
        $stmt = $this->conn->prepare("SELECT n.id_notificacion AS id, i.estado, c.calle
                                      FROM notificaciones n
                                      JOIN incidencias i ON n.id_incidencia = i.id_incidencia
                                      JOIN contenedores c ON i.id_contenedor = c.id_contenedor
                                      WHERE n.id_usuario = ? AND i.estado <> 'Abierta'
                                        AND i.fecha >= CURDATE() - INTERVAL 7 DAY");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        return $this->hacerLista($stmt->get_result());
    }
}
