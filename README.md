# SiGeRU - Frontend de Usuarios

Interfaz para los usuarios del Sistema de Gestión de Residuos Urbanos (SiGeRU), desarrollado por **EcoSync**.

Permite iniciar sesión y, según el rol, navegar por los listados y formularios de usuarios, contenedores y camiones.
No guarda datos ni accede a la base de datos: todo se hace a través de las APIs.

## Tecnologías

HTML5, CSS y JavaScript.

## Roles y navegación

| Rol           | Contenedores    | Camiones        | Usuarios        |
|---------------|-----------------|-----------------|-----------------|
| Administrador | Ver y registrar | Ver y registrar | Ver y registrar |
| Operario      | Ver y registrar | Ver y registrar | -               |
| Recolector    | Ver             | Ver             | -               |
| Vecino        | Ver             | -               | -               |

## Estructura (MVC)

```
index.html                      Inicio de sesión
panel.html                      Inicio del panel
usuarios.html                   Listado y formulario de usuarios
contenedores.html               Listado y formulario de contenedores
camiones.html                   Listado y formulario de camiones
css/styles.css
js/config.js                    Direcciones de las APIs
js/modelos/                     Pedidos a las APIs (servidor, usuarios, gestion)
js/vistas/                      Funciones que muestran datos en pantalla (comun, tablas)
js/controladores/               Lógica de cada página
```

## Cómo ejecutarlo

```
php -S localhost:8001
```

Abrir http://localhost:8001. Necesita la API de Usuarios (puerto 8002) y la API de Gestión (puerto 8003).

Usuario de prueba: **admin@sigeru.uy** / **admin123**
