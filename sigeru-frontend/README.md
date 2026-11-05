# SiGeRU - Frontend de Usuarios

Interfaz para los usuarios del Sistema de Gestión de Residuos Urbanos (SiGeRU), desarrollado por **EcoSync**.

Permite iniciar sesión y, según el rol, ver, agregar, modificar y borrar usuarios, contenedores,
camiones, centros de acopio y maquinaria.
No guarda datos ni accede a la base de datos: todo se hace a través de las APIs.

## Tecnologías

HTML5, CSS y JavaScript.

## Qué puede hacer cada rol

"Todo" = ver, agregar, modificar y borrar.

| Rol           | Contenedores | Camiones | Centros de acopio | Maquinaria | Usuarios |
|---------------|--------------|----------|-------------------|------------|----------|
| Administrador | Todo         | Todo     | Todo              | Todo       | Todo     |
| Operario      | Todo         | Todo     | Todo              | Todo       | -        |
| Recolector    | Ver          | Ver      | Ver               | Ver        | -        |
| Vecino        | Ver          | -        | -                 | -          | -        |

El menú y los botones se muestran según el rol, y además cada API revisa el rol antes de responder.

## Estructura (MVC)

```
index.html                      Inicio de sesión
panel.html                      Inicio del panel
usuarios.html                   Usuarios
contenedores.html               Contenedores
camiones.html                   Camiones
centros.html                    Centros de acopio
maquinaria.html                 Maquinaria
css/styles.css
js/config.js                    Direcciones de las APIs
js/modelos/                     Pedidos a las APIs (servidor, usuarios, gestion)
js/vistas/                      Funciones que muestran datos en pantalla (comun, tablas)
js/controladores/               Lógica de cada página
```

## Cómo ejecutarlo (XAMPP)

1. Copiar la carpeta `sigeru-frontend` dentro de `C:\xampp\htdocs\`.
2. Prender **Apache** y **MySQL** en el panel de XAMPP (la base de datos tiene que estar creada).
3. Abrir http://localhost/sigeru-frontend/

Necesita la API de Usuarios y la API de Gestión en `htdocs/sigeru-api-usuarios` y `htdocs/sigeru-api-gestion`.
Las direcciones se configuran en `js/config.js`.

Usuario de prueba: **admin@sigeru.uy** / **admin123**

