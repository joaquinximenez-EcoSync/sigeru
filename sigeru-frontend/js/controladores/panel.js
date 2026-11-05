// Controlador de la pagina de inicio del panel

async function iniciar() {
    const haySesion = await revisarSesion();
    if (!haySesion) return;

    document.getElementById("saludo").textContent = usuarioLogueado.nombre;

    // Totales que se muestran en las tarjetas
    try {
        const contenedores = await obtenerContenedores();
        document.getElementById("totalContenedores").textContent = contenedores.length;

        let conProblemas = 0;
        for (const c of contenedores) {
            if (c.estado != "Funcional") conProblemas++;
        }
        document.getElementById("contenedoresProblema").textContent = conProblemas;

        if (usuarioLogueado.rol != "Vecino") {
            const camiones = await obtenerCamiones();
            document.getElementById("totalCamiones").textContent = camiones.length;
        }

        if (usuarioLogueado.rol == "Administrador") {
            const usuarios = await obtenerUsuarios();
            document.getElementById("totalUsuarios").textContent = usuarios.length;
        }
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

iniciar();
