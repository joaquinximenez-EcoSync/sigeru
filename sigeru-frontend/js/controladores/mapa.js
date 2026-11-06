// Controlador de la pagina del mapa

let mapa = null;

// Datos que vienen de la API
let contenedores = [];
let incidencias = [];
let instalaciones = [];
let camiones = [];
let recorridos = [];
let limitesMunicipios = null; // archivo de la Intendencia (puede no estar)

// Grupos de marcas del mapa. Cada grupo se puede mostrar u ocultar.
let capaContenedores, capaIncidencias, capaInstalaciones, capaCamiones, capaRecorridos, capaMunicipios, capaUbicacion;

// Marcas de los camiones, para poder moverlas
let marcasCamiones = [];

let modoAgregar = false;       // true cuando se esta eligiendo el lugar de un contenedor nuevo
let idParaReportar = null;     // contenedor al que se le reporta un problema
let idParaAvisar = null;       // contenedor del que avisa un vecino

const COLORES_RECORRIDOS = ["#1d6fd8", "#8e44ad", "#16a085", "#d35400"];
const MUNICIPIOS = ["A", "B", "C", "CH", "D", "E", "F", "G"];

async function iniciar() {
    const haySesion = await revisarSesion();
    if (!haySesion) return;

    mapa = crearMapa("mapa");
    capaContenedores = L.layerGroup().addTo(mapa);
    capaIncidencias = L.layerGroup().addTo(mapa);
    capaInstalaciones = L.layerGroup().addTo(mapa);
    capaCamiones = L.layerGroup().addTo(mapa);
    capaRecorridos = L.layerGroup().addTo(mapa);
    capaMunicipios = L.layerGroup().addTo(mapa);
    capaUbicacion = L.layerGroup().addTo(mapa);

    try {
        const tipos = await obtenerTiposResiduo();
        llenarSelect("filtroResiduo", tipos, "id_residuo");
        llenarSelect("id_residuo", tipos, "id_residuo");

        limitesMunicipios = await obtenerLimitesMunicipios();
        await cargarDatos();
    } catch (error) {
        mostrarMensaje(error.message, true);
    }

    // El administrador y el operario pueden agregar contenedores tocando el mapa
    if (puedeEditar()) {
        mapa.on("click", tocarMapa);
    }

    // Cada 3 segundos se mueven un poco los camiones que estan en ruta (posicion simulada)
    if (esPersonal()) {
        setInterval(moverCamiones, 3000);
    }
}

// Devuelve true si el usuario no es vecino (recolector, operario o administrador)
function esPersonal() {
    return usuarioLogueado.rol != "Vecino";
}

// Pide todos los datos a la API y dibuja el mapa
async function cargarDatos() {
    contenedores = await obtenerContenedores();

    if (esPersonal()) {
        incidencias = await obtenerIncidencias();
        instalaciones = await obtenerInstalaciones();
        camiones = await obtenerCamiones();
        recorridos = await obtenerRecorridos();
        mostrarListaRecorridos(recorridos, COLORES_RECORRIDOS);
    }

    // El vecino ve sus avisos; el administrador y el operario ven los de todos los vecinos
    if (usuarioLogueado.rol == "Vecino") {
        const avisos = await obtenerAvisos();
        mostrarTablaAvisos("tablaMisAvisos", avisos, false);
    }
    if (puedeEditar()) {
        const avisos = await obtenerAvisos();
        mostrarTablaAvisos("tablaAvisosVecinos", avisos, true);
    }
    dibujarTodo();
}

// Vuelve a dibujar todo segun los filtros y las casillas de capas
function dibujarTodo() {
    dibujarContenedores();
    if (esPersonal()) {
        dibujarIncidencias();
        dibujarInstalaciones();
        dibujarCamiones();
        dibujarRecorridos();
        dibujarMunicipios();
        calcularResumen();
    }
}

// Devuelve true si el contenedor pasa los filtros de estado, residuo y municipio
function pasaFiltros(c) {
    const estado = document.getElementById("filtroEstado").value;
    const residuo = document.getElementById("filtroResiduo").value;
    const municipio = document.getElementById("filtroMunicipio").value;

    if (estado != "" && c.estado != estado) return false;
    if (residuo != "" && c.id_residuo != residuo) return false;
    if (municipio != "" && c.municipio != municipio) return false;
    return true;
}

// Devuelve true si la casilla de esa capa esta marcada
function capaVisible(idCasilla) {
    return document.getElementById(idCasilla).checked;
}

function dibujarContenedores() {
    capaContenedores.clearLayers();
    if (!capaVisible("verContenedores")) return;

    // Botones de la ventanita: el personal vacia y reporta, el vecino avisa
    let botones = "vecino";
    if (esPersonal()) {
        botones = "personal";
    }

    let cantidad = 0;
    for (const c of contenedores) {
        if (c.latitud == null || !pasaFiltros(c)) continue;

        const icono = crearIcono(claseContenedor(c.estado), "🗑");
        L.marker([c.latitud, c.longitud], { icon: icono })
            .bindPopup(textoContenedor(c, botones))
            .addTo(capaContenedores);
        cantidad++;
    }
    document.getElementById("cantidadVisible").textContent = cantidad + " contenedores en el mapa";
}

function dibujarIncidencias() {
    capaIncidencias.clearLayers();
    if (!capaVisible("verIncidencias")) return;

    const municipio = document.getElementById("filtroMunicipio").value;
    for (const i of incidencias) {
        if (i.estado == "Resuelta" || i.latitud == null) continue;
        if (municipio != "" && i.municipio != municipio) continue;

        // Se corre un poquito hacia arriba para que no tape el contenedor
        const clase = "marca-alerta-" + i.prioridad.toLowerCase();
        L.marker([Number(i.latitud) + 0.0012, i.longitud], { icon: crearIcono(clase, "⚠") })
            .bindPopup(textoIncidencia(i))
            .addTo(capaIncidencias);
    }
}

function dibujarInstalaciones() {
    capaInstalaciones.clearLayers();
    if (!capaVisible("verInstalaciones")) return;

    for (const ins of instalaciones) {
        if (ins.latitud == null) continue;
        let texto = "🏭";
        if (ins.tipo == "Vertedero") {
            texto = "⛰";
        }
        L.marker([ins.latitud, ins.longitud], { icon: crearIcono("marca-gris", texto) })
            .bindPopup(textoInstalacion(ins))
            .addTo(capaInstalaciones);
    }
}

function dibujarCamiones() {
    capaCamiones.clearLayers();
    marcasCamiones = [];
    if (!capaVisible("verCamiones")) return;

    for (const c of camiones) {
        if (c.latitud == null) continue;
        const marca = L.marker([c.latitud, c.longitud], { icon: crearIcono("marca-azul", "🚛") })
            .bindPopup(textoCamion(c))
            .addTo(capaCamiones);
        marcasCamiones.push({ camion: c, marca: marca });
    }
}

// Mueve un poco los camiones que estan "En ruta" (es una simulacion, no es GPS real)
function moverCamiones() {
    for (const m of marcasCamiones) {
        if (m.camion.disponibilidad != "En ruta") continue;

        m.camion.latitud = Number(m.camion.latitud) + (Math.random() - 0.5) * 0.002;
        m.camion.longitud = Number(m.camion.longitud) + (Math.random() - 0.5) * 0.002;
        m.marca.setLatLng([m.camion.latitud, m.camion.longitud]);
    }
}

// Dibuja cada recorrido: los contenedores numerados en orden y una linea que los une
function dibujarRecorridos() {
    capaRecorridos.clearLayers();
    if (!capaVisible("verRecorridos")) return;

    for (let r = 0; r < recorridos.length; r++) {
        const color = COLORES_RECORRIDOS[r % COLORES_RECORRIDOS.length];
        const ordenados = ordenarPorCercania(recorridos[r].contenedores);

        const puntos = [];
        for (let i = 0; i < ordenados.length; i++) {
            const c = ordenados[i];
            puntos.push([c.latitud, c.longitud]);

            L.marker([c.latitud, c.longitud], { icon: crearIcono("marca-numero", i + 1) })
                .bindPopup(`<strong>${i + 1}. ${limpiarTexto(c.calle)}</strong><br>${limpiarTexto(recorridos[r].ruta)}`)
                .addTo(capaRecorridos);
        }
        L.polyline(puntos, { color: color, weight: 4, opacity: 0.8 }).addTo(capaRecorridos);
    }
}

// Ordena los contenedores para recorrerlos: empieza por el primero y
// siempre va al mas cercano que todavia no visito
function ordenarPorCercania(lista) {
    const ordenados = [];
    if (lista.length == 0) return ordenados;

    // visitado[i] es true cuando el contenedor i ya esta en el recorrido
    const visitado = [];
    for (let i = 0; i < lista.length; i++) {
        visitado.push(false);
    }

    // Empezamos por el primero
    let actual = 0;
    visitado[0] = true;
    ordenados.push(lista[0]);

    while (ordenados.length < lista.length) {
        // Buscamos el mas cercano al actual que todavia no visitamos
        let masCercano = -1;
        let menorDistancia = 0;
        for (let i = 0; i < lista.length; i++) {
            if (visitado[i]) continue;

            const d = distanciaEnMetros(lista[actual].latitud, lista[actual].longitud, lista[i].latitud, lista[i].longitud);
            if (masCercano == -1 || d < menorDistancia) {
                menorDistancia = d;
                masCercano = i;
            }
        }

        visitado[masCercano] = true;
        ordenados.push(lista[masCercano]);
        actual = masCercano;
    }
    return ordenados;
}

// Cuenta las incidencias abiertas de cada municipio
function incidenciasPorMunicipio() {
    const cuenta = {};
    for (const m of MUNICIPIOS) {
        cuenta[m] = 0;
    }
    for (const i of incidencias) {
        if (i.estado != "Resuelta" && i.municipio != null) {
            cuenta[i.municipio]++;
        }
    }
    return cuenta;
}

// Zonas criticas: pinta cada municipio segun cuantas incidencias abiertas tiene
function dibujarMunicipios() {
    capaMunicipios.clearLayers();
    if (!capaVisible("verZonasCriticas")) return;

    const cuenta = incidenciasPorMunicipio();

    if (limitesMunicipios != null) {
        // Con el archivo de la Intendencia se pintan los limites reales
        L.geoJSON(limitesMunicipios, {
            style: function (limite) {
                const municipio = municipioDelLimite(limite);
                return { color: "#333", weight: 1, fillColor: colorPorIncidencias(cuenta[municipio] || 0), fillOpacity: 0.3 };
            },
            onEachFeature: function (limite, capa) {
                const municipio = municipioDelLimite(limite);
                capa.bindTooltip("Municipio " + municipio + ": " + (cuenta[municipio] || 0) + " incidencias abiertas");
            }
        }).addTo(capaMunicipios);
    } else {
        // Sin el archivo, se dibuja un circulo en el centro de cada municipio
        for (const m of MUNICIPIOS) {
            L.circle(CENTROS_MUNICIPIOS[m], {
                radius: 1800,
                color: colorPorIncidencias(cuenta[m]),
                fillColor: colorPorIncidencias(cuenta[m]),
                fillOpacity: 0.25,
                weight: 2
            }).bindTooltip("Municipio " + m + ": " + cuenta[m] + " incidencias abiertas", { permanent: true, direction: "center", className: "etiqueta-municipio" })
              .addTo(capaMunicipios);
        }
    }
}

// Tabla de resumen por municipio, ordenada de mas incidencias a menos
function calcularResumen() {
    const cuenta = incidenciasPorMunicipio();
    const resumen = [];

    for (const m of MUNICIPIOS) {
        let total = 0;
        let conProblemas = 0;
        for (const c of contenedores) {
            if (c.municipio == m) {
                total++;
                if (c.estado != "Funcional") conProblemas++;
            }
        }
        resumen.push({ municipio: m, contenedores: total, conProblemas: conProblemas, incidencias: cuenta[m] });
    }

    resumen.sort(function (a, b) {
        return b.incidencias - a.incidencias;
    });
    mostrarResumenMunicipios(resumen);
}

// Muestra un municipio en el mapa (al elegirlo en el filtro)
function cambiarMunicipio() {
    const municipio = document.getElementById("filtroMunicipio").value;
    if (municipio == "") {
        mapa.setView([-34.875, -56.17], 12);
    } else {
        mapa.setView(CENTROS_MUNICIPIOS[municipio], 13);
    }
    dibujarTodo();
}

// ----- Estoy aca: ubicacion del usuario y contenedor mas cercano -----
function buscarMiUbicacion() {
    if (!navigator.geolocation) {
        mostrarMensaje("Tu navegador no permite saber la ubicación", true);
        return;
    }
    mostrarMensaje("Buscando tu ubicación...", false);
    navigator.geolocation.getCurrentPosition(mostrarMiUbicacion, function () {
        mostrarMensaje("No se pudo obtener tu ubicación. Revisa que el navegador tenga permiso.", true);
    });
}

function mostrarMiUbicacion(posicion) {
    const lat = posicion.coords.latitude;
    const lng = posicion.coords.longitude;
    capaUbicacion.clearLayers();

    L.marker([lat, lng], { icon: crearIcono("marca-yo", "📍") })
        .bindPopup("<strong>Estás acá</strong>")
        .addTo(capaUbicacion);

    // Buscamos el contenedor mas cercano de los que se ven en el mapa
    let cercano = null;
    let menorDistancia = Infinity;
    for (const c of contenedores) {
        if (c.latitud == null || !pasaFiltros(c)) continue;
        const d = distanciaEnMetros(lat, lng, c.latitud, c.longitud);
        if (d < menorDistancia) {
            menorDistancia = d;
            cercano = c;
        }
    }

    if (cercano == null) {
        mapa.setView([lat, lng], 15);
        mostrarMensaje("No hay contenedores para mostrar con estos filtros", true);
        return;
    }

    L.polyline([[lat, lng], [cercano.latitud, cercano.longitud]], { color: "#1d6fd8", dashArray: "6 8" }).addTo(capaUbicacion);
    mapa.fitBounds([[lat, lng], [cercano.latitud, cercano.longitud]], { padding: [60, 60] });

    let distancia = Math.round(menorDistancia) + " m";
    if (menorDistancia >= 1000) {
        distancia = (menorDistancia / 1000).toFixed(1) + " km";
    }
    mostrarMensaje("El contenedor más cercano está en " + cercano.calle + " (a " + distancia + ")", false);
}

// ----- Vaciar y reportar problemas (recolector, operario y administrador) -----
async function marcarVaciado(id) {
    try {
        const respuesta = await vaciarContenedor(id);
        mapa.closePopup();
        mostrarMensaje(respuesta.mensaje, false);
        await cargarDatos();
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

function abrirReporte(id) {
    idParaReportar = id;
    const c = buscarPorId(contenedores, id);
    document.getElementById("formReporte").reset();
    document.getElementById("reporteContenedor").textContent = c.calle;
    document.getElementById("reporteModal").showModal();
}

function cerrarReporte() {
    document.getElementById("reporteModal").close();
}

document.getElementById("formReporte").addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const datos = {
        id_contenedor: idParaReportar,
        problema: document.getElementById("problema").value,
        prioridad: document.getElementById("prioridad").value,
        descripcion: document.getElementById("descripcionProblema").value.trim()
    };

    try {
        const respuesta = await reportarProblema(datos);
        cerrarReporte();
        mapa.closePopup();
        mostrarMensaje(respuesta.mensaje, false);
        await cargarDatos();
    } catch (error) {
        alert(error.message);
    }
});

// ----- Avisar un problema (vecinos) -----
function abrirAviso(id) {
    idParaAvisar = id;
    const c = buscarPorId(contenedores, id);
    document.getElementById("formAviso").reset();
    document.getElementById("avisoContenedor").textContent = c.calle;
    document.getElementById("avisoModal").showModal();
}

function cerrarAviso() {
    document.getElementById("avisoModal").close();
}

document.getElementById("formAviso").addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const datos = {
        id_contenedor: idParaAvisar,
        problema: document.getElementById("problemaAviso").value,
        comentario: document.getElementById("comentarioAviso").value.trim()
    };

    try {
        const respuesta = await avisarProblema(datos);
        cerrarAviso();
        mapa.closePopup();
        mostrarMensaje(respuesta.mensaje, false);
        await cargarDatos();
    } catch (error) {
        // Si hay un error con la ventana abierta, el mensaje no se ve, por eso usamos alert
        alert(error.message);
    }
});

// ----- Agregar un contenedor tocando el mapa (operario y administrador) -----
function activarModoAgregar() {
    modoAgregar = !modoAgregar;
    const boton = document.getElementById("botonAgregar");
    if (modoAgregar) {
        boton.textContent = "✖ Cancelar";
        document.getElementById("mapa").classList.add("eligiendo");
        mostrarMensaje("Toca en el mapa el lugar donde está el contenedor nuevo", false);
    } else {
        boton.textContent = "➕ Agregar contenedor";
        document.getElementById("mapa").classList.remove("eligiendo");
    }
}

function tocarMapa(evento) {
    if (!modoAgregar) return;

    const lat = evento.latlng.lat.toFixed(6);
    const lng = evento.latlng.lng.toFixed(6);

    document.getElementById("formContenedor").reset();
    document.getElementById("latitud").value = lat;
    document.getElementById("longitud").value = lng;
    document.getElementById("municipio").value = adivinarMunicipio(Number(lat), Number(lng));
    document.getElementById("textoUbicacion").textContent = lat + ", " + lng;

    activarModoAgregar();
    abrirModal();
}

// Elige el municipio del punto: con los limites reales si estan, si no el centro mas cercano
function adivinarMunicipio(lat, lng) {
    if (limitesMunicipios != null) {
        for (const limite of limitesMunicipios.features) {
            let poligonos = [limite.geometry.coordinates];
            if (limite.geometry.type == "MultiPolygon") {
                poligonos = limite.geometry.coordinates;
            }
            for (const poligono of poligonos) {
                if (puntoEnPoligono(lat, lng, poligono)) {
                    return municipioDelLimite(limite);
                }
            }
        }
    }

    let elegido = "";
    let menorDistancia = Infinity;
    for (const m of MUNICIPIOS) {
        const d = distanciaEnMetros(lat, lng, CENTROS_MUNICIPIOS[m][0], CENTROS_MUNICIPIOS[m][1]);
        if (d < menorDistancia) {
            menorDistancia = d;
            elegido = m;
        }
    }
    return elegido;
}

document.getElementById("formContenedor").addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const datos = {
        calle: document.getElementById("calle").value.trim(),
        zona: document.getElementById("zona").value.trim(),
        municipio: document.getElementById("municipio").value,
        estado: document.getElementById("estado").value,
        capacidad: document.getElementById("capacidad").value,
        id_residuo: document.getElementById("id_residuo").value,
        latitud: document.getElementById("latitud").value,
        longitud: document.getElementById("longitud").value
    };

    try {
        const respuesta = await crearContenedor(datos);
        cerrarModal();
        mostrarMensaje(respuesta.mensaje, false);
        await cargarDatos();
    } catch (error) {
        // Si hay un error con la ventana abierta, el mensaje no se ve, por eso usamos alert
        alert(error.message);
    }
});

iniciar();
