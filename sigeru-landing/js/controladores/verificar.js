// Controlador de la pagina que se abre desde el enlace del correo

const enlacesIngresar = document.querySelectorAll(".link-ingresar");
for (const enlace of enlacesIngresar) {
    enlace.href = URL_FRONTEND;
}

async function verificar() {
    // El codigo viene en la direccion: verificar.html?codigo=abc123
    const parametros = new URLSearchParams(window.location.search);
    const codigo = parametros.get("codigo") ?? "";
    const titulo = document.querySelector("h2");

    try {
        const respuesta = await enviarVerificacion(codigo);
        document.getElementById("icono").textContent = "✅";
        titulo.textContent = "¡Te registraste correctamente!";
        mostrarMensaje(respuesta.mensaje, false);

        // Si la cuenta ya quedo activa, mostramos el boton para entrar
        if (respuesta.estado == "Activo") {
            document.getElementById("botonIngresar").style.display = "inline-block";
        }
    } catch (error) {
        document.getElementById("icono").textContent = "❌";
        titulo.textContent = "No pudimos verificar tu correo";
        mostrarMensaje(error.message, true);
    }
}

verificar();
