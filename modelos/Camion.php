<?php
// Modelo Camion
// En esta entrega los camiones se guardan en $_SESSION['camiones'] (sin base de datos)

class Camion
{
    public function __construct()
    {
        // Si es la primera vez, cargamos algunos camiones de prueba
        if (!isset($_SESSION['camiones'])) {
            $_SESSION['camiones'] = [];
            $this->agregar(["matricula" => "SBA 1234", "estado" => "Operativo", "disponibilidad" => "Disponible", "capacidad_carga" => 10000]);
            $this->agregar(["matricula" => "SBC 5678", "estado" => "En mantenimiento", "disponibilidad" => "No disponible", "capacidad_carga" => 8000]);
        }
    }

    public function obtenerTodos()
    {
        return $_SESSION['camiones'];
    }

    public function obtenerPorId($id)
    {
        foreach ($_SESSION['camiones'] as $camion) {
            if ($camion['id'] == $id) {
                return $camion;
            }
        }
        return null;
    }

    public function buscarPorMatricula($matricula)
    {
        foreach ($_SESSION['camiones'] as $camion) {
            if ($camion['matricula'] == $matricula) {
                return $camion;
            }
        }
        return null;
    }

    public function agregar($datos)
    {
        $nuevo = [
            "id" => count($_SESSION['camiones']) + 1,
            "matricula" => strtoupper($datos['matricula']),
            "estado" => $datos['estado'],
            "disponibilidad" => $datos['disponibilidad'],
            "capacidad_carga" => $datos['capacidad_carga'] ?? ""
        ];

        $_SESSION['camiones'][] = $nuevo;
        return $nuevo;
    }
}
