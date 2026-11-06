// Controlador de la pagina de informes: listado de incidencias abiertas y cerradas entre dos fechas

async function iniciar() {
    const haySesion = await revisarSesion();
    if (!haySesion) return;

    // Por defecto se muestra el mes actual: desde el dia 1 hasta hoy
    document.getElementById("desde").value = mesActual() + "-01";
    document.getElementById("hasta").value = fechaDeHoy();
    await verInforme();
}

async function verInforme() {
    const desde = document.getElementById("desde").value;
    const hasta = document.getElementById("hasta").value;
    const cuales = document.getElementById("cuales").value;

    if (desde == "" || hasta == "") {
        mostrarMensaje("Elige las dos fechas", true);
        return;
    }

    try {
        const todas = await obtenerIncidencias("desde=" + desde + "&hasta=" + hasta);

        // Separamos las abiertas (sin resolver) de las cerradas (resueltas)
        const abiertas = [];
        const cerradas = [];
        for (const i of todas) {
            if (i.estado == "Resuelta") {
                cerradas.push(i);
            } else {
                abiertas.push(i);
            }
        }

        let lista = todas;
        let titulo = "Todas las incidencias";
        if (cuales == "abiertas") {
            lista = abiertas;
            titulo = "Incidencias abiertas (sin resolver)";
        }
        if (cuales == "cerradas") {
            lista = cerradas;
            titulo = "Incidencias cerradas (resueltas)";
        }

        // Promedio de dias que tardaron en resolverse las cerradas
        let suma = 0;
        for (const i of cerradas) {
            suma = suma + diasEntre(i.fecha_deteccion, i.fecha_resolucion);
        }
        let promedio = "-";
        if (cerradas.length > 0) {
            promedio = (suma / cerradas.length).toFixed(1) + " días";
        }

        document.getElementById("tituloInforme").textContent = titulo + " del " + desde + " al " + hasta;
        document.getElementById("totalInforme").textContent = todas.length;
        document.getElementById("abiertasInforme").textContent = abiertas.length;
        document.getElementById("cerradasInforme").textContent = cerradas.length;
        document.getElementById("promedioInforme").textContent = promedio;
        document.getElementById("fechaImpresion").textContent = "Generado el " + fechaDeHoy() + " por " + usuarioLogueado.nombre;
        mostrarTablaInforme(lista);
        document.getElementById("mensaje").className = "mensaje";
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

// Abre la ventana de imprimir del navegador (tambien se puede guardar como PDF)
function imprimir() {
    window.print();
}

iniciar();
