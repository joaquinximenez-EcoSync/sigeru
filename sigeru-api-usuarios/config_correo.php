<?php
// Configuracion del correo (Gmail) que usa la API para mandar los mails de verificacion.
//
// Como se configura:
//   1. Entrar a la cuenta de Gmail de la empresa -> Gestionar tu cuenta de Google -> Seguridad.
//   2. Activar la "Verificación en dos pasos".
//   3. Buscar "Contraseñas de aplicaciones", crear una (por ejemplo "SiGeRU") y copiar las 16 letras.
//   4. Escribir abajo el correo y esas 16 letras (sin espacios).
//
// Si se deja vacio, la API funciona igual pero no manda correos: en ese caso devuelve el enlace
// de verificacion en la respuesta (modo prueba), para poder probar el registro sin Gmail.
// IMPORTANTE: no subir a GitHub este archivo con la contraseña escrita.

$correoGmail = "";       // por ejemplo "ecosync.sigeru@gmail.com"
$claveAppGmail = "";     // la contraseña de aplicacion de 16 letras

// Direccion donde esta publicada la pagina (para armar el enlace del correo)
$direccionSitio = "http://localhost";

// En Docker estos datos se pueden pasar como variables de entorno
if (getenv("GMAIL_USUARIO")) {
    $correoGmail = getenv("GMAIL_USUARIO");
}
if (getenv("GMAIL_CLAVE")) {
    $claveAppGmail = getenv("GMAIL_CLAVE");
}
if (getenv("DIRECCION_SITIO")) {
    $direccionSitio = getenv("DIRECCION_SITIO");
}
