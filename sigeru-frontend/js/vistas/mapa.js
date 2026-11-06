// Vista: funciones que dibujan cosas en el mapa (usa la libreria Leaflet)

// Centro aproximado de cada municipio de Montevideo.
// Se usa para las zonas criticas cuando no esta el archivo con los limites de los municipios.
const CENTROS_MUNICIPIOS = {
    "A": [-34.862, -56.272],
    "B": [-34.905, -56.195],
    "C": [-34.865, -56.193],
    "CH": [-34.900, -56.155],
    "D": [-34.828, -56.160],
    "E": [-34.885, -56.090],
    "F": [-34.840, -56.095],
    "G": [-34.790, -56.230]
};

// Crea el mapa centrado en Montevideo con las imagenes de OpenStreetMap
function crearMapa(idDiv) {
    const mapa = L.map(idDiv).setView([-34.875, -56.17], 12);

    L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: "&copy; OpenStreetMap"
    }).addTo(mapa);

    return mapa;
}

// Crea un icono redondo con un texto o emoji adentro. "clase" elige el color (ver styles.css).
function crearIcono(clase, texto) {
    return L.divIcon({
        className: "",
        html: `<div class="marca ${clase}">${texto}</div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15],
        popupAnchor: [0, -14]
    });
}

// Clase de color del contenedor segun su estado
function claseContenedor(estado) {
    if (estado == "Desbordado") return "marca-roja";
    if (estado == "Roto") return "marca-naranja";
    return "marca-verde";
}

// Ventanita que aparece al tocar un contenedor.
// "botones" puede ser "personal" (vaciar y reportar), "vecino" (avisar un problema) o "" (sin botones).
function textoContenedor(c, botones) {
    let texto = `<strong>${limpiarTexto(c.calle)}</strong><br>
        ${limpiarTexto(c.zona)}${c.municipio ? " - Municipio " + c.municipio : ""}<br>
        Residuo: ${limpiarTexto(c.residuo)}<br>
        Estado: <span class="etiqueta ${colorEstado(c.estado)}">${limpiarTexto(c.estado)}</span><br>`;

    if (c.capacidad) {
        texto += `Capacidad: ${Number(c.capacidad)} L<br>`;
    }
    if (c.ultima_recoleccion) {
        texto += `Última recolección: ${limpiarTexto(c.ultima_recoleccion.substring(0, 16))}<br>`;
    }
    if (botones == "personal") {
        texto += `<div class="popup-botones">
            <button class="btn btn-primary btn-sm" onclick="marcarVaciado(${c.id})">✔ Vaciado</button>
            <button class="btn btn-danger btn-sm" onclick="abrirReporte(${c.id})">⚠ Reportar problema</button>
        </div>`;
    }
    if (botones == "vecino") {
        texto += `<div class="popup-botones">
            <button class="btn btn-danger btn-sm" onclick="abrirAviso(${c.id})">📣 Avisar un problema</button>
        </div>`;
    }
    return texto;
}

function textoIncidencia(i) {
    return `<strong>⚠ Incidencia #${i.id}</strong><br>
        ${limpiarTexto(i.descripcion)}<br>
        ${limpiarTexto(i.calle)}${i.municipio ? " - Municipio " + i.municipio : ""}<br>
        Prioridad: ${limpiarTexto(i.prioridad)} | Estado: ${limpiarTexto(i.estado)}<br>
        Detectada: ${limpiarTexto(i.fecha_deteccion)}`;
}

function textoInstalacion(ins) {
    let texto = `<strong>${limpiarTexto(ins.nombre)}</strong><br>
        ${limpiarTexto(ins.tipo)}<br>
        ${limpiarTexto(ins.calle)}${ins.zona ? ", " + limpiarTexto(ins.zona) : ""}<br>`;
    if (ins.capacidad_max) {
        texto += `Capacidad usada: ${Number(ins.capacidad_actual)} / ${Number(ins.capacidad_max)} kg`;
    }
    return texto;
}

function textoCamion(c) {
    return `<strong>🚛 ${limpiarTexto(c.matricula)}</strong><br>
        ${limpiarTexto(c.estado)} - ${limpiarTexto(c.disponibilidad)}<br>
        <small>Posición simulada</small>`;
}

// Color de un municipio segun cuantas incidencias abiertas tiene (zonas criticas)
function colorPorIncidencias(cantidad) {
    if (cantidad >= 3) return "#c0392b";
    if (cantidad == 2) return "#e67e22";
    if (cantidad == 1) return "#f1c40f";
    return "#2e9d5b";
}

// Busca de que municipio es un limite del archivo GeoJSON.
// Revisa los datos del limite hasta encontrar una de las letras A, B, C, CH, D, E, F o G.
function municipioDelLimite(limite) {
    const letras = ["CH", "A", "B", "C", "D", "E", "F", "G"];
    for (const clave in limite.properties) {
        const valor = String(limite.properties[clave]).toUpperCase().replace("MUNICIPIO", "").trim();
        if (letras.includes(valor)) {
            return valor;
        }
    }
    return null;
}

// Devuelve true si el punto esta adentro del poligono.
// Se traza una linea horizontal desde el punto y se cuenta cuantas veces cruza el borde:
// si lo cruza una cantidad impar de veces, el punto esta adentro.
function puntoEnPoligono(lat, lng, poligono) {
    const borde = poligono[0];
    let adentro = false;

    // Recorremos cada lado del borde: va desde el punto anterior hasta el punto actual
    let anterior = borde.length - 1;
    for (let actual = 0; actual < borde.length; actual++) {
        const lng1 = borde[actual][0];
        const lat1 = borde[actual][1];
        const lng2 = borde[anterior][0];
        const lat2 = borde[anterior][1];

        // El lado tiene un extremo arriba del punto y otro abajo
        const pasaPorLaAltura = (lat1 > lat) != (lat2 > lat);

        if (pasaPorLaAltura) {
            // Donde el lado corta la altura del punto
            const lngDelCorte = lng1 + (lng2 - lng1) * (lat - lat1) / (lat2 - lat1);
            if (lng < lngDelCorte) {
                adentro = !adentro;
            }
        }
        anterior = actual;
    }
    return adentro;
}

// Distancia en metros entre dos puntos (formula de Haversine)
function distanciaEnMetros(lat1, lng1, lat2, lng2) {
    const radioTierra = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return radioTierra * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

// Arma la tabla de resumen por municipio
function mostrarResumenMunicipios(resumen) {
    const tabla = document.getElementById("tablaMunicipios");
    let filas = "";
    for (const m of resumen) {
        filas += `<tr>
            <td><span class="punto-color" style="background:${colorPorIncidencias(m.incidencias)}"></span> Municipio ${m.municipio}</td>
            <td>${m.contenedores}</td>
            <td>${m.conProblemas}</td>
            <td>${m.incidencias}</td>
        </tr>`;
    }
    tabla.innerHTML = filas;
}

// Arma la lista de recorridos debajo del mapa
function mostrarListaRecorridos(recorridos, colores) {
    const lista = document.getElementById("listaRecorridos");
    if (recorridos.length == 0) {
        lista.innerHTML = "<p>No hay recorridos asignados.</p>";
        return;
    }

    let html = "";
    for (let i = 0; i < recorridos.length; i++) {
        const r = recorridos[i];
        html += `<div class="recorrido">
            <span class="punto-color" style="background:${colores[i % colores.length]}"></span>
            <strong>${limpiarTexto(r.ruta)}</strong> - ${limpiarTexto(r.cuadrilla)}<br>
            <small>Camión ${limpiarTexto(r.camion)} | ${limpiarTexto(r.hora_inicio.substring(0, 5))} a ${limpiarTexto(r.hora_fin.substring(0, 5))}
            | Zonas: ${limpiarTexto(r.zonas.join(", "))} | ${r.contenedores.length} contenedores</small>
        </div>`;
    }
    lista.innerHTML = html;
}

// Como va el aviso de un vecino, segun el estado de su incidencia
function estadoDelAviso(estadoIncidencia) {
    if (estadoIncidencia == "Resuelta") return "Resuelto";
    if (estadoIncidencia == "En proceso") return "En proceso";
    return "Recibido";
}

// Tabla de avisos. Si conVecino es true se agrega la columna con el nombre del vecino.
function mostrarTablaAvisos(idTabla, lista, conVecino) {
    const tabla = document.getElementById(idTabla);
    let columnas = 4;
    if (conVecino) columnas = 5;

    if (lista.length == 0) {
        tabla.innerHTML = `<tr><td colspan="${columnas}" class="vacio">Todavía no hay avisos</td></tr>`;
        return;
    }

    let filas = "";
    for (const a of lista) {
        const estado = estadoDelAviso(a.estado_incidencia);
        let color = "alerta";
        if (estado == "Resuelto") color = "";

        filas += `<tr>
            <td>${limpiarTexto(a.fecha)}</td>`;
        if (conVecino) {
            filas += `<td>${limpiarTexto(a.vecino)}</td>`;
        }
        filas += `<td class="ajustar">${limpiarTexto(a.calle)}${a.zona ? ", " + limpiarTexto(a.zona) : ""}</td>
            <td class="ajustar">${limpiarTexto(a.descripcion)}</td>
            <td><span class="etiqueta ${color}">${estado}</span></td>
        </tr>`;
    }
    tabla.innerHTML = filas;
}
