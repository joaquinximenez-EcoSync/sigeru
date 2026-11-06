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

## Mapa interactivo (mapa.html)

Mapa de Montevideo hecho con [Leaflet](https://leafletjs.com) (guardado en `js/libs/leaflet`) y las imágenes de OpenStreetMap.

| Función | Quién la ve |
|---------|-------------|
| Contenedores con colores según su estado (verde, naranja, rojo) | Todos |
| Filtros por estado, tipo de residuo y municipio | Todos |
| 📍 Estoy acá: muestra tu ubicación y el contenedor más cercano | Todos |
| Incidencias abiertas, según su prioridad | Administrador, Operario, Recolector |
| Centros de acopio y vertederos | Administrador, Operario, Recolector |
| Camiones (posición simulada, los que están "En ruta" se mueven) | Administrador, Operario, Recolector |
| Recorridos: contenedores numerados en orden y la línea del recorrido | Administrador y Operario ven todos, el Recolector ve el de su cuadrilla |
| Botones "Vaciado" y "Reportar problema" en cada contenedor | Administrador, Operario, Recolector |
| Zonas críticas: municipios pintados según sus incidencias abiertas, y resumen por municipio | Administrador, Operario, Recolector |
| ➕ Agregar un contenedor tocando el mapa (el municipio se elige solo) | Administrador y Operario |
| 📣 Avisar un problema en un contenedor (desbordado, roto o falta de recolección) y ver "Mis avisos" con su estado | Vecino |
| Tabla "Avisos de vecinos" con todos los avisos | Administrador y Operario |

Cada aviso de un vecino se guarda como **notificación** y genera una **incidencia** abierta (relaciones
*Realizan* y *Genera2* del MER). Un vecino no puede avisar dos veces del mismo contenedor mientras no se resuelva.

### Límites de los municipios

Para que las zonas críticas pinten los límites reales de cada municipio, hay que guardar el archivo
de límites de la Intendencia de Montevideo en `js/datos/municipios.json` (formato GeoJSON).
Si el archivo no está, el mapa funciona igual y marca cada municipio con un círculo en su centro.

## Estructura (MVC)

```
index.html                      Inicio de sesión
panel.html                      Inicio del panel
mapa.html                       Mapa interactivo de Montevideo
usuarios.html                   Usuarios
contenedores.html               Contenedores
camiones.html                   Camiones
centros.html                    Centros de acopio
maquinaria.html                 Maquinaria
css/styles.css
js/config.js                    Direcciones de las APIs
js/modelos/                     Pedidos a las APIs (servidor, usuarios, gestion)
js/vistas/                      Funciones que muestran datos en pantalla (comun, tablas, mapa)
js/libs/leaflet/                Libreria del mapa (Leaflet 1.9.4)
js/datos/                       Límites de los municipios (municipios.json, opcional)
js/controladores/               Lógica de cada página
```

## Cómo ejecutarlo (XAMPP)

1. Copiar la carpeta `sigeru-frontend` dentro de `C:\xampp\htdocs\`.
2. Prender **Apache** y **MySQL** en el panel de XAMPP (la base de datos tiene que estar creada).
3. Abrir http://localhost/sigeru-frontend/

Necesita la API de Usuarios y la API de Gestión en `htdocs/sigeru-api-usuarios` y `htdocs/sigeru-api-gestion`.
Las direcciones se configuran en `js/config.js`.

Usuario de prueba: **admin@sigeru.uy** / **admin123**

