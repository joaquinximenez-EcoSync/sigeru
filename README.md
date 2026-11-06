# SiGeRU - Sistema de Gestión de Residuos Urbanos

Proyecto de pasaje de grado de **EcoSync** - ESI 2026.

## Aplicaciones

| Carpeta | Qué es |
|---------|--------|
| `sigeru-landing` | Página principal y registro de vecinos |
| `sigeru-frontend` | Inicio de sesión, panel de control con estadísticas y alertas, mapa interactivo de Montevideo, seguimiento de incidencias, informes y CRUD de usuarios, contenedores, camiones, recorridos, centros de acopio y maquinaria |
| `sigeru-api-usuarios` | API de registro, inicio de sesión y usuarios |
| `sigeru-api-gestion` | API de contenedores, centros de acopio, maquinaria, inventario de repuestos, incidencias (las reportan el personal y los vecinos, con su seguimiento), volumen recolectado, estadísticas y alertas |
| `sigeru-api-recoleccion` | API de la flota de camiones, mantenimientos y recorridos (rutas asignadas a cuadrillas) |
| `sigeru-base-datos` | MER, modelo físico, DDL y dump de la base de datos |

## Cómo ejecutarlo (XAMPP)

1. Copiar las carpetas `sigeru-landing`, `sigeru-frontend`, `sigeru-api-usuarios`, `sigeru-api-gestion` y `sigeru-api-recoleccion` dentro de `C:\xampp\htdocs\`.
2. Prender **Apache** y **MySQL** en XAMPP.
3. Entrar a http://localhost/phpmyadmin → **Importar** → `sigeru-base-datos/ddl/sigeru_ddl.sql`.
4. Otra vez **Importar** → `sigeru-base-datos/datos/sigeru_datos_prueba.sql` (datos de prueba).
5. Abrir http://localhost/sigeru-landing/

## Cómo ejecutarlo con Docker

Con [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado, desde esta carpeta:

```
docker compose up -d --build
```

La primera vez descarga las imágenes y crea la base de datos con los datos de prueba (tarda unos minutos).

| Qué | Dirección |
|-----|-----------|
| Sistema | http://localhost:8080 |
| phpMyAdmin (usuario `root`, sin contraseña) | http://localhost:8081 |
| MySQL desde afuera | `localhost`, puerto `3307` |

Contenedores:

| Contenedor | Qué tiene |
|------------|-----------|
| `sigeru-mysql` | MySQL 8. La primera vez ejecuta el DDL y los datos de prueba de `sigeru-base-datos` |
| `sigeru-web` | Apache con PHP 8.2 (`docker/Dockerfile`) con las 5 aplicaciones |
| `sigeru-phpmyadmin` | phpMyAdmin para ver la base de datos |

Para apagarlo: `docker compose down`. Para borrar también la base de datos y empezar de cero: `docker compose down -v`.

## Tests

Cada API tiene dos tipos de tests en su carpeta `tests/`, hechos con PHP común (sin librerías):

| Archivo | Qué prueba |
|---------|------------|
| `test_modelos.php` | **Tests unitarios** de cada clase del modelo. Usan una base de datos aparte (`sigeru_test`), así no tocan los datos reales |
| `test_api.php` | **Tests de los endpoints**: le hacen pedidos a la API como el navegador y revisan las respuestas y los permisos de cada rol. Lo que crean lo borran al final |

Con XAMPP (Apache y MySQL prendidos, base de datos importada), desde esta carpeta:

```
C:\xampp\php\php.exe sigeru-api-usuarios/tests/test_modelos.php
C:\xampp\php\php.exe sigeru-api-usuarios/tests/test_api.php
C:\xampp\php\php.exe sigeru-api-gestion/tests/test_modelos.php
C:\xampp\php\php.exe sigeru-api-gestion/tests/test_api.php
C:\xampp\php\php.exe sigeru-api-recoleccion/tests/test_modelos.php
C:\xampp\php\php.exe sigeru-api-recoleccion/tests/test_api.php
```

Con Docker:

```
docker exec sigeru-web php /var/www/html/sigeru-api-usuarios/tests/test_modelos.php
docker exec sigeru-web php /var/www/html/sigeru-api-usuarios/tests/test_api.php
docker exec sigeru-web php /var/www/html/sigeru-api-gestion/tests/test_modelos.php
docker exec sigeru-web php /var/www/html/sigeru-api-gestion/tests/test_api.php
docker exec sigeru-web php /var/www/html/sigeru-api-recoleccion/tests/test_modelos.php
docker exec sigeru-web php /var/www/html/sigeru-api-recoleccion/tests/test_api.php
```

Cada test muestra `OK` o `FALLA` y al final cuántos pasaron:

| Tests | Cantidad |
|-------|----------|
| Modelos de usuarios (usuarios, verificación, parámetros, notificaciones) | 38 |
| Endpoints de la API de usuarios | 47 |
| Modelos de gestión (contenedores, centros, maquinaria, incidencias, estadísticas, alertas, repuestos, volumen) | 88 |
| Endpoints de la API de gestión | 81 |
| Modelos de recolección (camiones, mantenimientos, recorridos, notificaciones) | 35 |
| Endpoints de la API de recolección | 35 |

## Registro con verificación por correo (Gmail)

Cuando un vecino se registra en la landing:

1. Le llega un **correo con un enlace** para verificar que el correo es suyo.
2. Al abrir el enlace ve **"¡Te registraste correctamente!"** y le llega otro correo confirmándolo.
3. Un **administrador aprueba** su cuenta (le llega una notificación y lo hace desde Usuarios). Al vecino le llega un correo avisando.
   Esto se puede apagar en **Parámetros** → "aprobar registros".

Para que se manden los correos hay que poner una cuenta de Gmail en `sigeru-api-usuarios/config_correo.php`
(los pasos están en ese archivo: hace falta una "contraseña de aplicación" de Google).
Si no se configura, no se pueden registrar vecinos (la API avisa que no pudo mandar el correo).

## Notificaciones

La campana 🔔 del menú muestra las notificaciones de cada usuario y se actualiza sola cada 15 segundos
(cuando llega una nueva aparece un cartel abajo a la derecha). Por ejemplo:

| Qué pasa | A quién le llega |
|----------|------------------|
| Un vecino reporta una incidencia | A todo el personal (administradores, operarios y recolectores) y la confirmación al vecino |
| El personal registra una incidencia | A los administradores y operarios |
| Se asigna una incidencia a una cuadrilla | A los recolectores de esa cuadrilla |
| La incidencia de un vecino pasa a En proceso o Resuelta | Al vecino que la reportó |
| Un recolector resuelve una incidencia | A los administradores y operarios |
| Se asigna o cambia un recorrido (ruta) de una cuadrilla | A los recolectores de esa cuadrilla |
| Un vecino verifica su correo y espera aprobación | A los administradores |
| Un repuesto queda en la cantidad mínima | A los administradores y operarios |

## Datos de prueba

`sigeru-base-datos/datos/sigeru_datos_prueba.sql` trae usuarios, contenedores, camiones, recorridos, centros,
maquinaria, repuestos y recolecciones simuladas. **No trae incidencias**: se cargan desde el sistema.

## Usuarios de prueba

| Correo | Contraseña | Rol |
|--------|------------|-----|
| admin@sigeru.uy | admin123 | Administrador |
| operario@sigeru.uy | 123456 | Operario |
| recolector@sigeru.uy | 123456 | Recolector |
| vecino@sigeru.uy | 123456 | Vecino |

Cada carpeta tiene su propio README con más detalles.

## Integrantes

- Valentín Ramírez (Coordinador)
- Joaquín Ximénez (Subcoordinador)
- Juan Cuadro
