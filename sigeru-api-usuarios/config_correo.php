<?php
// Configuracion del correo (Gmail) que usa la API para mandar los mails de verificacion.
//
// La contraseña NO se escribe aca (este archivo se sube a GitHub).
// Se escribe en un archivo aparte que esta FUERA de las carpetas del proyecto:
//
//     C:\xampp\sigeru_correo.php        (al lado de la carpeta htdocs, no adentro)
//
// Asi no se sube nunca a GitHub y no se borra cuando se copian las carpetas nuevas del proyecto.
// En cada computadora se crea una sola vez, con este contenido:
//
//     <?php
//     $correoGmail = "el correo de gmail";
//     $claveAppGmail = "la contraseña de aplicacion de 16 letras, sin espacios";
//
// Como se saca la contraseña de aplicacion:
//   1. Entrar a la cuenta de Gmail -> Gestionar tu cuenta de Google -> Seguridad.
//   2. Activar la "Verificación en dos pasos".
//   3. Entrar a https://myaccount.google.com/apppasswords, crear una (por ejemplo "SiGeRU") y copiar las 16 letras.
//
// Si no esta configurado no se mandan correos, y los vecinos no se pueden registrar desde la landing.

$correoGmail = "";
$claveAppGmail = "";

// Direccion donde esta publicada la pagina (para armar el enlace del correo)
$direccionSitio = "http://localhost";

// Leemos el archivo con la contraseña, que esta dos carpetas mas arriba:
// C:\xampp\htdocs\sigeru-api-usuarios  ->  C:\xampp\htdocs  ->  C:\xampp
$archivoPrivado = __DIR__ . "/../../sigeru_correo.php";
if (file_exists($archivoPrivado)) {
    require $archivoPrivado;
}

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
