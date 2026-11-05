// Vista: arma las tablas de los listados

function mostrarTablaUsuarios(lista) {
    const tabla = document.getElementById("tablaUsuarios");

    if (lista.length == 0) {
        tabla.innerHTML = `<tr><td colspan="6" class="vacio">No hay usuarios</td></tr>`;
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
            <td><span class="etiqueta">${limpiarTexto(u.rol)}</span></td>
        </tr>`;
    }
    tabla.innerHTML = filas;
}

function mostrarTablaContenedores(lista) {
    const tabla = document.getElementById("tablaContenedores");

    if (lista.length == 0) {
        tabla.innerHTML = `<tr><td colspan="6" class="vacio">No hay contenedores</td></tr>`;
        return;
    }

    let filas = "";
    for (const c of lista) {
        filas += `<tr>
            <td>${c.id}</td>
            <td>${limpiarTexto(c.calle)}</td>
            <td>${limpiarTexto(c.zona)}</td>
            <td>${limpiarTexto(c.residuo)}</td>
            <td>${c.capacidad ? limpiarTexto(c.capacidad) + " L" : ""}</td>
            <td><span class="etiqueta ${colorEstado(c.estado)}">${limpiarTexto(c.estado)}</span></td>
        </tr>`;
    }
    tabla.innerHTML = filas;
}

function mostrarTablaCamiones(lista) {
    const tabla = document.getElementById("tablaCamiones");

    if (lista.length == 0) {
        tabla.innerHTML = `<tr><td colspan="5" class="vacio">No hay camiones</td></tr>`;
        return;
    }

    let filas = "";
    for (const c of lista) {
        filas += `<tr>
            <td>${c.id}</td>
            <td>${limpiarTexto(c.matricula)}</td>
            <td>${c.capacidad_carga ? limpiarTexto(c.capacidad_carga) + " kg" : ""}</td>
            <td><span class="etiqueta ${colorEstado(c.estado)}">${limpiarTexto(c.estado)}</span></td>
            <td><span class="etiqueta ${colorEstado(c.disponibilidad)}">${limpiarTexto(c.disponibilidad)}</span></td>
        </tr>`;
    }
    tabla.innerHTML = filas;
}

// Carga los tipos de residuo en la lista desplegable del formulario de contenedores
function llenarSelectResiduos(tipos) {
    const select = document.getElementById("id_residuo");
    for (const tipo of tipos) {
        select.innerHTML += `<option value="${tipo.id_residuo}">${limpiarTexto(tipo.nombre)}</option>`;
    }
}
