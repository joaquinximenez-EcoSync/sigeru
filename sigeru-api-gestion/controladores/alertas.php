<?php
// Controlador de las alertas (GET alertas). Cada rol ve las alertas que le sirven.
// Cada alerta tiene: nivel (alta, media o info), texto y enlace (la pagina donde se resuelve).

function listarAlertas($alerta) {
    $usuario = $_SESSION['usuario_logueado'];
    $alertas = [];

    if ($usuario['rol'] == "Administrador" || $usuario['rol'] == "Operario") {
        foreach ($alerta->desbordadosSinAtender() as $a) {
            $alertas[] = ["nivel" => "alta", "texto" => "El contenedor de " . $a['calle'] . " (" . $a['zona'] . ") está desbordado hace " . $a['dias'] . " días y nadie lo atendió", "enlace" => "incidencias.html"];
        }
        foreach ($alerta->prioridadAltaAbiertas() as $a) {
            $alertas[] = ["nivel" => "alta", "texto" => "La incidencia #" . $a['id'] . " en " . $a['calle'] . " es de prioridad alta y sigue abierta", "enlace" => "incidencias.html"];
        }
        foreach ($alerta->avisosSinAtender() as $a) {
            $alertas[] = ["nivel" => "media", "texto" => "Hay un aviso de un vecino sin atender en " . $a['calle'], "enlace" => "incidencias.html"];
        }
        foreach ($alerta->instalacionesCasiLlenas() as $a) {
            $alertas[] = ["nivel" => "media", "texto" => $a['nombre'] . " está al " . $a['porcentaje'] . "% de su capacidad", "enlace" => "centros.html"];
        }
    }

    if ($usuario['rol'] == "Recolector") {
        foreach ($alerta->delRecolector($usuario['id']) as $a) {
            $nivel = "media";
            if ($a['prioridad'] == "Alta") {
                $nivel = "alta";
            }
            $alertas[] = ["nivel" => $nivel, "texto" => "Tu cuadrilla tiene que atender la incidencia #" . $a['id'] . " en " . $a['calle'] . " (" . $a['estado'] . ")", "enlace" => "incidencias.html"];
        }
    }

    if ($usuario['rol'] == "Vecino") {
        foreach ($alerta->avisosDelVecino($usuario['id']) as $a) {
            if ($a['estado'] == "Resuelta") {
                $texto = "¡Tu aviso sobre el contenedor de " . $a['calle'] . " ya fue resuelto!";
            } else {
                $texto = "Tu aviso sobre el contenedor de " . $a['calle'] . " está en proceso";
            }
            $alertas[] = ["nivel" => "info", "texto" => $texto, "enlace" => "mapa.html"];
        }
    }

    responder($alertas);
}
