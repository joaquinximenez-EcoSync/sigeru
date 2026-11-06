# SiGeRU - Sistema de Gestión de Residuos Urbanos

Proyecto de pasaje de grado de **EcoSync** - ESI 2026.

## Aplicaciones

| Carpeta | Qué es |
|---------|--------|
| `sigeru-landing` | Página principal y registro de vecinos |
| `sigeru-frontend` | Inicio de sesión, panel, mapa interactivo de Montevideo y CRUD de usuarios, contenedores, camiones, centros de acopio y maquinaria |
| `sigeru-api-usuarios` | API de registro, inicio de sesión y usuarios |
| `sigeru-api-gestion` | API de contenedores, camiones, centros de acopio y maquinaria |
| `sigeru-base-datos` | MER, modelo físico, DDL y dump de la base de datos |

## Cómo ejecutarlo (XAMPP)

1. Copiar las carpetas `sigeru-landing`, `sigeru-frontend`, `sigeru-api-usuarios` y `sigeru-api-gestion` dentro de `C:\xampp\htdocs\`.
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
| `sigeru-web` | Apache con PHP 8.2 (`docker/Dockerfile`) con las 4 aplicaciones |
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
```

Con Docker:

```
docker exec sigeru-web php /var/www/html/sigeru-api-usuarios/tests/test_modelos.php
docker exec sigeru-web php /var/www/html/sigeru-api-usuarios/tests/test_api.php
docker exec sigeru-web php /var/www/html/sigeru-api-gestion/tests/test_modelos.php
docker exec sigeru-web php /var/www/html/sigeru-api-gestion/tests/test_api.php
```

Cada test muestra `OK` o `FALLA` y al final cuántos pasaron:

| Tests | Cantidad |
|-------|----------|
| Modelo de usuarios | 21 |
| Endpoints de la API de usuarios | 26 |
| Modelos de gestión (contenedores, camiones, centros, maquinaria, incidencias, avisos, recorridos) | 48 |
| Endpoints de la API de gestión | 42 |

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
