<?php
// Tests unitarios de los modelos de la API de Gestion.
// Usan la base de datos de prueba "sigeru_test", asi no se tocan los datos reales.
// Se corren con: php tests/test_modelos.php

require_once __DIR__ . "/funciones_test.php";
require_once __DIR__ . "/../modelos/Contenedor.php";
require_once __DIR__ . "/../modelos/Centro.php";
require_once __DIR__ . "/../modelos/Maquinaria.php";
require_once __DIR__ . "/../modelos/Incidencia.php";
require_once __DIR__ . "/../modelos/Notificacion.php";
require_once __DIR__ . "/../modelos/Estadistica.php";
require_once __DIR__ . "/../modelos/Alerta.php";
require_once __DIR__ . "/../modelos/Repuesto.php";
require_once __DIR__ . "/../modelos/Parametro.php";
require_once __DIR__ . "/../modelos/Mensaje.php";

$conn = crearBaseDePrueba();

// ---------------------------------------------------------------
titulo("Contenedores");

$contenedor = new Contenedor($conn);
verificar(count($contenedor->obtenerTodos()) == 16, "Hay 16 contenedores de prueba");
verificar(count($contenedor->obtenerTiposResiduo()) == 4, "Hay 4 tipos de residuo");
verificar($contenedor->existeResiduo(2) && !$contenedor->existeResiduo(99), "existeResiduo distingue un tipo que existe de uno que no");
verificar($contenedor->obtenerPorId(1)['residuo'] == "Reciclable", "Trae el nombre del tipo de residuo");
verificar($contenedor->tieneIncidencias(2) && !$contenedor->tieneIncidencias(4), "tieneIncidencias detecta los que tienen incidencias");

$nuevo = $contenedor->agregar([
    "calle" => "Av. Larrañaga 2000",
    "zona" => "La Blanqueada",
    "municipio" => "CH",
    "estado" => "Funcional",
    "capacidad" => "",
    "id_residuo" => 2,
    "latitud" => "-34.885",
    "longitud" => "-56.155"
]);
$id = $nuevo['id'];
verificar($id == 17 && $nuevo['municipio'] == "CH", "Agrega un contenedor con municipio");
verificar($nuevo['capacidad'] == null, "Una capacidad vacía se guarda como NULL");
verificar($nuevo['latitud'] == "-34.885000", "Guarda la ubicación en el mapa");

$modificado = $contenedor->modificar($id, [
    "calle" => "Av. Larrañaga 2100",
    "zona" => "La Blanqueada",
    "municipio" => "",
    "estado" => "Desbordado",
    "capacidad" => "900",
    "id_residuo" => 3
]);
verificar($modificado['estado'] == "Desbordado" && $modificado['residuo'] == "No reciclable", "Modifica el estado y el tipo de residuo");
verificar($modificado['municipio'] == null && $modificado['latitud'] == null, "Sin municipio ni ubicación se guardan como NULL");

$vaciado = $contenedor->vaciar($id);
verificar($vaciado['estado'] == "Funcional" && $vaciado['ultima_recoleccion'] != null, "Al vaciarlo queda Funcional y con la fecha de recolección");

$contenedor->cambiarEstado($id, "Roto");
verificar($contenedor->obtenerPorId($id)['estado'] == "Roto", "cambiarEstado cambia solo el estado");

$contenedor->eliminar($id);
verificar($contenedor->obtenerPorId($id) == null, "Borra el contenedor");

// ---------------------------------------------------------------
titulo("Centros de acopio");

$centro = new Centro($conn);
verificar(count($centro->obtenerTodos()) == 2, "Hay 2 centros de acopio (el vertedero no se cuenta)");
verificar(count($centro->obtenerResiduos(1)) == 2, "El Centro de Acopio Norte recibe 2 tipos de residuo");
verificar($centro->esOperario(2) && !$centro->esOperario(1), "esOperario distingue un operario de un administrador");
verificar(count($centro->obtenerOperarios()) == 1, "Lista los operarios");

$nuevo = $centro->agregar(["nombre" => "Centro Sur", "calle" => "Rambla 1", "zona" => "Sur", "capacidad_actual" => "100", "capacidad_max" => "5000", "id_operario" => 2, "residuos" => [2, 4]]);
$id = $nuevo['id'];
verificar(count($nuevo['residuos']) == 2 && $nuevo['operario'] == "Martín Pereira", "Agrega un centro con sus residuos y su operario");
verificar(contarFilas($conn, "SELECT * FROM centros_acopio WHERE id_instalacion = $id") == 1, "También se guarda en la tabla centros_acopio");

$modificado = $centro->modificar($id, ["nombre" => "Centro Sur 2", "capacidad_actual" => "", "capacidad_max" => "", "id_operario" => "", "residuos" => [1]]);
verificar(count($modificado['residuos']) == 1 && $modificado['residuos'][0]['nombre'] == "Orgánico", "Al modificarlo cambia los residuos que recibe");
verificar($modificado['id_operario'] == null, "Se le puede sacar el operario");

$centro->eliminar($id);
verificar($centro->obtenerPorId($id) == null, "Borra el centro");
verificar(contarFilas($conn, "SELECT * FROM centro_residuo WHERE id_instalacion = $id") == 0, "Sus residuos se borran solos (ON DELETE CASCADE)");

// ---------------------------------------------------------------
titulo("Maquinaria");

$maquinaria = new Maquinaria($conn);
verificar(count($maquinaria->obtenerTodos()) == 2, "Hay 2 máquinas de prueba");
verificar(count($maquinaria->obtenerInstalaciones()) == 3, "Hay 3 instalaciones");
verificar($maquinaria->existeInstalacion(2) && !$maquinaria->existeInstalacion(99), "existeInstalacion funciona");

$nueva = $maquinaria->agregar(["nombre" => "Excavadora", "tipo" => "Movimiento de tierra", "estado" => "Operativa", "id_instalacion" => 2]);
$id = $nueva['id'];
verificar($nueva['instalacion'] == "Vertedero Felipe Cardoso", "Agrega una máquina en el vertedero");

$modificada = $maquinaria->modificar($id, ["nombre" => "Excavadora", "tipo" => "", "estado" => "En reparación", "id_instalacion" => ""]);
verificar($modificada['estado'] == "En reparación" && $modificada['id_instalacion'] == null, "Modifica la máquina y la saca de la instalación");

$maquinaria->eliminar($id);
verificar($maquinaria->obtenerPorId($id) == null, "Borra la máquina");

// ---------------------------------------------------------------
titulo("Incidencias y avisos de vecinos");

$incidencia = new Incidencia($conn);
verificar(count($incidencia->obtenerTodas()) == 9, "Hay 9 incidencias de prueba");

$nueva = $incidencia->agregar(7, "Punta Carretas", "Desbordado", "Alta", "Desbordado");
verificar($nueva['estado'] == "Abierta" && $nueva['fecha'] == date("Y-m-d"), "Una incidencia nueva queda Abierta y con la fecha de hoy");
verificar($nueva['municipio'] == "CH" && $nueva['tipo'] == "Desbordado", "Trae el tipo y el municipio de su contenedor");

$notificacion = new Notificacion($conn);
verificar(count($notificacion->obtenerDelVecino(4)) == 1, "La vecina de prueba tiene 1 aviso");
$numero = $notificacion->agregar(4, $nueva['id'], "Desbordado");
verificar($numero == 2, "El aviso nuevo de la vecina es el número 2 (entidad débil)");
verificar($notificacion->yaAviso(4, 7), "Detecta que ya avisó de ese contenedor");
verificar(!$notificacion->yaAviso(4, 8), "En otro contenedor todavía no avisó");

$incidencia->modificarSeguimiento($nueva['id'], "Resuelta", "Alta", null);
verificar(!$notificacion->yaAviso(4, 7), "Cuando la incidencia se resuelve puede volver a avisar");

// ---------------------------------------------------------------
titulo("Seguimiento de incidencias");

verificar(count($incidencia->obtenerTodas("Resuelta")) == 4, "Filtra por estado: 4 resueltas");
verificar(count($incidencia->obtenerTodas("", 2)) == 3, "Historial del contenedor 2: 3 incidencias");
verificar(count($incidencia->obtenerTodas("", "", "2026-10-01", "2026-10-31")) == 4, "Filtra por fechas: 4 en octubre");

$modificada = $incidencia->modificarSeguimiento(5, "En proceso", "Alta", 1);
verificar($modificada['estado'] == "En proceso" && $modificada['prioridad'] == "Alta" && $modificada['cuadrilla'] != null, "Cambia estado, prioridad y cuadrilla");
verificar($modificada['fecha_resolucion'] == null, "Mientras no se resuelve no tiene fecha de resolución");

$modificada = $incidencia->modificarSeguimiento(5, "Resuelta", "Alta", 1);
verificar($modificada['fecha_resolucion'] == date("Y-m-d"), "Al resolverla guarda la fecha de resolución");
verificar($incidencia->contarSinResolver(10) == 0 && $incidencia->contarSinResolver(11) == 1, "Cuenta las incidencias sin resolver de un contenedor");

verificar($incidencia->esRecorridoDelRecolector(1, 3) && !$incidencia->esRecorridoDelRecolector(2, 3), "Sabe qué recorridos son de la cuadrilla del recolector");
verificar($incidencia->existeRecorrido(1) && !$incidencia->existeRecorrido(999), "Sabe si un recorrido existe");

$notificacion->cambiarEstado(1, "En proceso");
$resultado = mysqli_query($conn, "SELECT estado FROM notificaciones WHERE id_incidencia = 1");
verificar(mysqli_fetch_assoc($resultado)['estado'] == "En proceso", "El aviso del vecino cambia de estado con su incidencia");

// ---------------------------------------------------------------
titulo("Estadísticas");

$estadistica = new Estadistica($conn);
$top = $estadistica->topDesbordados("2026-11");
verificar($top[0]['id'] == 2 && $top[0]['cantidad'] == 2, "El contenedor que más se desbordó en noviembre es el 2 (2 veces)");
verificar(count($estadistica->topDesbordados("2020-01")) == 0, "En un mes sin datos el top está vacío");

$porEstado = $estadistica->incidenciasPorEstado();
$total = 0;
foreach ($porEstado as $fila) {
    $total = $total + $fila['cantidad'];
}
verificar($total == 10, "Las incidencias por estado suman el total (10)");
verificar($estadistica->promedioDiasResolucion() !== null, "Calcula el promedio de días de resolución");
verificar(count($estadistica->sinResolverPorMunicipio()) > 0 && count($estadistica->porTipo("2026-11")) > 0, "Cuenta por municipio y por tipo");

// ---------------------------------------------------------------
titulo("Alertas");

$alerta = new Alerta($conn);
$llenas = $alerta->instalacionesCasiLlenas(80);
verificar(count($llenas) == 1 && $llenas[0]['porcentaje'] == 85, "Avisa que el Centro de Acopio Oeste está al 85%");
verificar(count($alerta->instalacionesCasiLlenas(90)) == 0, "Si el límite es 90% ya no avisa (es un parámetro)");
verificar(count($alerta->desbordadosSinAtender(2)) >= 1, "Encuentra contenedores desbordados sin atender");
verificar(count($alerta->desbordadosSinAtender(2)) > count($alerta->desbordadosSinAtender(500)), "Con más días de límite avisa de menos contenedores");
verificar(count($alerta->repuestosConPocoStock()) == 2, "Avisa de los 2 repuestos que están en el mínimo o menos");
verificar(count($alerta->delRecolector(3)) == 1, "El recolector tiene 1 incidencia pendiente de su cuadrilla");
verificar(count($alerta->avisosDelVecino(4)) == 1, "La vecina tiene novedades de su aviso");

// ---------------------------------------------------------------
titulo("Repuestos");

$repuesto = new Repuesto($conn);
verificar(count($repuesto->obtenerTodos()) == 5, "Hay 5 repuestos de prueba");
verificar(count($repuesto->obtenerConPocoStock()) == 2, "2 tienen poco stock");
$nuevo = $repuesto->agregar(["nombre" => "Contenedor de prueba", "tipo" => "Contenedor", "id_residuo" => "2", "cantidad" => 4, "cantidad_minima" => 2, "id_instalacion" => ""]);
verificar($nuevo['residuo'] == "Reciclable" && $nuevo['instalacion'] == null, "Agrega un repuesto (sin instalación queda NULL)");
$nuevo = $repuesto->cambiarCantidad($nuevo['id'], -2);
verificar($nuevo['cantidad'] == 2, "Usar 2 deja 2");
verificar(count($repuesto->obtenerConPocoStock()) == 3, "Ahora está en el mínimo y aparece con poco stock");
$nuevo = $repuesto->modificar($nuevo['id'], ["nombre" => "Contenedor de prueba", "tipo" => "Contenedor", "id_residuo" => "", "cantidad" => 10, "cantidad_minima" => 2, "id_instalacion" => "1"]);
verificar($nuevo['cantidad'] == 10 && $nuevo['residuo'] == null && $nuevo['instalacion'] == "Centro de Acopio Norte", "Lo modifica");
$repuesto->eliminar($nuevo['id']);
verificar($repuesto->obtenerPorId($nuevo['id']) == null, "Lo borra");

// ---------------------------------------------------------------
titulo("Volumen recolectado");

verificar(contarFilas($conn, "SELECT * FROM recolecciones") == 192, "Hay 192 recolecciones simuladas");
$total = $estadistica->totalRecolectado("2026-11-01", "2026-11-30");
verificar($total['vaciados'] == 26 && $total['contenedores'] == 16 && $total['kilos'] > 0, "En noviembre se vaciaron contenedores 26 veces");
$total = $estadistica->totalRecolectado("2020-01-01", "2020-01-31");
verificar($total['vaciados'] == 0 && $total['kilos'] == 0, "En un mes sin datos da 0");
verificar(count($estadistica->kilosPorMunicipio("2026-10-01", "2026-11-30")) == 8, "Kilos de los 8 municipios");
verificar(count($estadistica->kilosPorResiduo("2026-10-01", "2026-11-30")) > 0, "Kilos por tipo de residuo");
verificar(count($estadistica->vaciadosPorContenedor("2026-11-01", "2026-11-30")) == 16, "Cuántas veces se vació cada uno de los 16 contenedores");
$contenedor->registrarRecoleccion(1, 150.5);
verificar(contarFilas($conn, "SELECT * FROM recolecciones WHERE kilos = 150.5") == 1, "Guarda una recolección nueva");

// ---------------------------------------------------------------
titulo("Parámetros y mensajes");

$parametro = new Parametro($conn);
verificar($parametro->obtener("dias_alerta_desborde", "0") == "2", "Lee un parámetro");
verificar($parametro->obtener("no_existe", "7") == "7", "Si no existe usa el valor por defecto");

$mensaje = new Mensaje($conn);
$mensaje->enviarACuadrilla(1, "Prueba a la cuadrilla", "recorridos.html");
verificar(contarFilas($conn, "SELECT * FROM mensajes WHERE texto = 'Prueba a la cuadrilla' AND id_usuario = 3") == 1, "Mandar a una cuadrilla le llega a su recolectora");
$mensaje->enviarARol("Operario", "Prueba al operario", "panel.html", 2);
verificar(contarFilas($conn, "SELECT * FROM mensajes WHERE texto = 'Prueba al operario'") == 0, "El que hizo la acción no se manda el mensaje a sí mismo");

mostrarResumen();
