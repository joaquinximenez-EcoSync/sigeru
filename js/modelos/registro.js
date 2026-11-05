// Modelo: se comunica con la API de Usuarios

async function enviarRegistro(datos) {
    const respuesta = await fetch(API_USUARIOS + "/registro", {
        method: "POST",
        // Manda los datos de la sesion, porque en esta entrega los usuarios se guardan ahi
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(datos)
    });
    const json = await respuesta.json();

    // Si la API devolvio un error, lo pasamos para mostrarlo en pantalla
    if (!respuesta.ok) {
        throw new Error(json.error);
    }
    return json;
}
