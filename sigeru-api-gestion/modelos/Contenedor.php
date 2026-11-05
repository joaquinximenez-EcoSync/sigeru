<?php
// Modelo Contenedor
// En esta entrega los contenedores se guardan en $_SESSION['contenedores'] (sin base de datos)

class Contenedor
{
    // Tipos de residuo (regla RNE01 del MER: cada contenedor guarda un solo tipo)
    private $tiposResiduo = [
        ["id_residuo" => 1, "nombre" => "Orgánico"],
        ["id_residuo" => 2, "nombre" => "Reciclable"],
        ["id_residuo" => 3, "nombre" => "No reciclable"],
        ["id_residuo" => 4, "nombre" => "Peligroso"]
    ];

    public function __construct()
    {
        // Si es la primera vez, cargamos algunos contenedores de prueba
        if (!isset($_SESSION['contenedores'])) {
            $_SESSION['contenedores'] = [];
            $this->agregar(["calle" => "Av. 18 de Julio 1200", "zona" => "Centro", "estado" => "Funcional", "capacidad" => 1000, "id_residuo" => 2]);
            $this->agregar(["calle" => "Rambla República de México 5500", "zona" => "Carrasco", "estado" => "Desbordado", "capacidad" => 1200, "id_residuo" => 3]);
            $this->agregar(["calle" => "Bv. Artigas 1500", "zona" => "Tres Cruces", "estado" => "Roto", "capacidad" => 800, "id_residuo" => 1]);
        }
    }

    public function obtenerTodos()
    {
        return $_SESSION['contenedores'];
    }

    public function obtenerPorId($id)
    {
        foreach ($_SESSION['contenedores'] as $contenedor) {
            if ($contenedor['id'] == $id) {
                return $contenedor;
            }
        }
        return null;
    }

    public function obtenerTiposResiduo()
    {
        return $this->tiposResiduo;
    }

    // Devuelve el nombre de un tipo de residuo, o nada (null) si no existe
    public function nombreResiduo($idResiduo)
    {
        foreach ($this->tiposResiduo as $tipo) {
            if ($tipo['id_residuo'] == $idResiduo) {
                return $tipo['nombre'];
            }
        }
        return null;
    }

    public function agregar($datos)
    {
        $nuevo = [
            "id" => count($_SESSION['contenedores']) + 1,
            "calle" => $datos['calle'],
            "zona" => $datos['zona'],
            "estado" => $datos['estado'],
            "capacidad" => $datos['capacidad'] ?? "",
            "id_residuo" => $datos['id_residuo'],
            "residuo" => $this->nombreResiduo($datos['id_residuo'])
        ];

        $_SESSION['contenedores'][] = $nuevo;
        return $nuevo;
    }
}
