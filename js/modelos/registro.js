// Modelo: se comunica con la API de Usuarios

async function enviarRegistro(datos) {
    const respuesta = await fetch(API_USUARIOS + "/registro", {
        method: "POST",
        // Manda la cookie de la sesion: en esta entrega los usuarios se guardan en la sesion de la API
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(datos)
    });
    const json = await respuesta.json();

    // Si la API devolvio un error, lo lanzamos para mostrarlo
    if (!respuesta.ok) {
        throw new Error(json.error);
    }
    return json;
}
