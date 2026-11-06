// Vista: arma las tablas de los listados

// Botones de Editar y Borrar de una fila. "tipo" es Contenedor, Camion, Usuario, Centro o Maquina,
// y se usa para llamar a la funcion que corresponde (por ejemplo editarCamion(3)).
function botonesAcciones(tipo, id) {
    return `<td class="acciones">
        <button class="btn btn-secondary btn-sm" onclick="editar${tipo}(${id})">Editar</button>
        <button class="btn btn-danger btn-sm" onclick="borrar${tipo}Elegido(${id})">Borrar</button>
    </td>`;
}

// Fila que se muestra cuando la lista esta vacia
function filaVacia(columnas, texto) {
    return `<tr><td colspan="${columnas}" class="vacio">${texto}</td></tr>`;
}

// conAcciones es true si el usuario puede editar y borrar
function mostrarTablaUsuarios(lista, conAcciones) {
    const tabla = document.getElementById("tablaUsuarios");

    if (lista.length == 0) {
        tabla.innerHTML = filaVacia(7, "No hay usuarios");
        return;
    }

    let filas = "";
    for (const u of lista) {
        filas += `<tr>
            <td>${u.id}</td>
            <td>${limpiarTexto(u.primer_nombre)} ${limpiarTexto(u.primer_apellido)}</td>
            <td>${limpiarTexto(u.email)}</td>
            <td>${limpiarTexto(u.telefono)}</td>
            <td>${limpiarTexto(u.zona)}</td>
            <td><span class="etiqueta">${limpiarTexto(u.rol)}</span></td>`;
        if (conAcciones) {
            filas += botonesAcciones("Usuario", u.id);
        }
        filas += `</tr>`;
    }
    tabla.innerHTML = filas;
}

function mostrarTablaContenedores(lista, conAcciones) {
    const tabla = document.getElementById("tablaContenedores");

    if (lista.length == 0) {
        tabla.innerHTML = filaVacia(8, "No hay contenedores");
        return;
    }

    let filas = "";
    for (const c of lista) {
        filas += `<tr>
            <td>${c.id}</td>
            <td class="ajustar">${limpiarTexto(c.calle)}</td>
            <td>${limpiarTexto(c.zona)}</td>
            <td>${limpiarTexto(c.municipio)}</td>
            <td>${limpiarTexto(c.residuo)}</td>
            <td>${c.capacidad ? Number(c.capacidad) + " L" : ""}</td>
            <td><span class="etiqueta ${colorEstado(c.estado)}">${limpiarTexto(c.estado)}</span></td>`;
        if (conAcciones) {
            filas += botonesAcciones("Contenedor", c.id);
        }
        filas += `</tr>`;
    }
    tabla.innerHTML = filas;
}

function mostrarTablaCamiones(lista, conAcciones) {
    const tabla = document.getElementById("tablaCamiones");

    if (lista.length == 0) {
        tabla.innerHTML = filaVacia(6, "No hay camiones");
        return;
    }

    let filas = "";
    for (const c of lista) {
        filas += `<tr>
            <td>${c.id}</td>
            <td>${limpiarTexto(c.matricula)}</td>
            <td>${c.capacidad_carga ? Number(c.capacidad_carga) + " kg" : ""}</td>
            <td><span class="etiqueta ${colorEstado(c.estado)}">${limpiarTexto(c.estado)}</span></td>
            <td><span class="etiqueta ${colorEstado(c.disponibilidad)}">${limpiarTexto(c.disponibilidad)}</span></td>`;
        if (conAcciones) {
            filas += botonesAcciones("Camion", c.id);
        }
        filas += `</tr>`;
    }
    tabla.innerHTML = filas;
}

function mostrarTablaCentros(lista, conAcciones) {
    const tabla = document.getElementById("tablaCentros");

    if (lista.length == 0) {
        tabla.innerHTML = filaVacia(7, "No hay centros de acopio");
        return;
    }

    let filas = "";
    for (const c of lista) {
        // Nombres de los tipos de residuo que recibe, separados por coma
        let residuos = "";
        for (const r of c.residuos) {
            if (residuos != "") residuos += ", ";
            residuos += r.nombre;
        }

        // Capacidad usada, por ejemplo "3500 / 10000 kg"
        let capacidad = "";
        if (c.capacidad_actual || c.capacidad_max) {
            capacidad = Number(c.capacidad_actual) + " / " + Number(c.capacidad_max) + " kg";
        }

        filas += `<tr>
            <td>${c.id}</td>
            <td class="ajustar">${limpiarTexto(c.nombre)}</td>
            <td class="ajustar">${limpiarTexto(c.calle)}${c.zona ? ", " + limpiarTexto(c.zona) : ""}</td>
            <td>${capacidad}</td>
            <td class="ajustar">${limpiarTexto(residuos)}</td>
            <td>${limpiarTexto(c.operario)}</td>`;
        if (conAcciones) {
            filas += botonesAcciones("Centro", c.id);
        }
        filas += `</tr>`;
    }
    tabla.innerHTML = filas;
}

function mostrarTablaMaquinaria(lista, conAcciones) {
    const tabla = document.getElementById("tablaMaquinaria");

    if (lista.length == 0) {
        tabla.innerHTML = filaVacia(6, "No hay maquinaria");
        return;
    }

    let filas = "";
    for (const m of lista) {
        filas += `<tr>
            <td>${m.id}</td>
            <td>${limpiarTexto(m.nombre)}</td>
            <td>${limpiarTexto(m.tipo)}</td>
            <td><span class="etiqueta ${colorEstado(m.estado)}">${limpiarTexto(m.estado)}</span></td>
            <td>${limpiarTexto(m.instalacion)}</td>`;
        if (conAcciones) {
            filas += botonesAcciones("Maquina", m.id);
        }
        filas += `</tr>`;
    }
    tabla.innerHTML = filas;
}

// Carga opciones en una lista desplegable (select).
// "lista" tiene objetos con id y nombre.
function llenarSelect(idSelect, lista, campoId) {
    const select = document.getElementById(idSelect);
    for (const item of lista) {
        select.innerHTML += `<option value="${item[campoId]}">${limpiarTexto(item.nombre)}</option>`;
    }
}

// Carga los tipos de residuo como casillas para marcar (en el formulario de centros)
function llenarCasillasResiduos(tipos) {
    const caja = document.getElementById("casillasResiduos");
    for (const tipo of tipos) {
        caja.innerHTML += `<label class="casilla">
            <input type="checkbox" name="residuo" value="${tipo.id_residuo}"> ${limpiarTexto(tipo.nombre)}
        </label>`;
    }
}
