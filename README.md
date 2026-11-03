# SiGeRU - Landing Page

Página principal del Sistema de Gestión de Residuos Urbanos (SiGeRU), desarrollado por **EcoSync**.

Brinda información al público y recibe las solicitudes de registro de usuarios (vecinos).
No guarda datos: el registro se envía a la **API de Usuarios**.

## Tecnologías

HTML5, CSS y JavaScript.

## Estructura (MVC)

```
index.html                 Vista principal
css/styles.css
js/config.js               Direcciones de la API y del frontend
js/modelos/registro.js     Modelo: envía el registro a la API de Usuarios
js/vistas/mensajes.js      Vista: muestra mensajes en pantalla
js/controladores/registro.js  Controlador: valida y maneja el formulario
```

## Cómo ejecutarla

```
php -S localhost:8000
```

Abrir http://localhost:8000. Necesita la API de Usuarios corriendo en el puerto 8002.
