# SiGeRU - API de Gestión

API RESTful que actúa como orquestador operativo del Sistema de Gestión de Residuos Urbanos (SiGeRU),
desarrollado por **EcoSync**: contenedores, centros de acopio, maquinaria, incidencias y avisos de vecinos.
Los camiones y los recorridos están en la **API de Recolección**.

Los datos se guardan en la base de datos MySQL `sigeru` (ver `sigeru-base-datos`).

## Tecnologías

PHP 8.

## Estructura (MVC)

```
api.php                          Punto de entrada: recibe la solicitud, revisa el rol y llama al controlador
config.php                       Sesión y formato de respuesta
conexion.php                     Conexión a la base de datos MySQL
seguridad.php                    Revisa que haya sesión iniciada y que el rol tenga permiso
modelos/Contenedor.php           Modelo (clase) de contenedores y tipos de residuo
modelos/Centro.php               Modelo (clase) de centros de acopio
modelos/Maquinaria.php           Modelo (clase) de maquinaria
modelos/Incidencia.php           Modelo (clase) de incidencias
modelos/Notificacion.php         Modelo (clase) de avisos de los vecinos
controladores/contenedores.php   Controlador de contenedores
controladores/centros.php        Controlador de centros de acopio
controladores/maquinaria.php     Controlador de maquinaria
controladores/mapa.php           Controlador de incidencias y avisos de vecinos (para el mapa)
vistas/respuesta.php             Vista: devuelve la respuesta en JSON
```

## Endpoints

Todos necesitan la sesión iniciada (se inicia con la API de Usuarios).
Para ver, modificar o borrar uno solo se agrega `?id=1` a la dirección.

| Endpoint | GET (ver) | POST (agregar) / PUT (modificar) / DELETE (borrar) |
|----------|-----------|----------------------------------------------------|
| `api.php/contenedores` | Todos los roles | Administrador y Operario |
| `api.php/tipos_residuo` | Todos los roles | - |
| `api.php/centros` | Administrador, Operario y Recolector | Administrador y Operario |
| `api.php/maquinaria` | Administrador, Operario y Recolector | Administrador y Operario |
| `api.php/operarios` | Administrador y Operario | - |
| `api.php/instalaciones` | Administrador, Operario y Recolector | - |
| `api.php/incidencias` | Administrador, Operario y Recolector | POST (reportar un problema): Administrador, Operario y Recolector |
| `api.php/vaciar?id=1` | - | POST (marcar un contenedor como vaciado): Administrador, Operario y Recolector |
| `api.php/notificaciones` | Vecino (sus avisos), Administrador y Operario (todos) | POST (un vecino avisa un problema): Vecino |

Respuestas de error:

| Código | Cuándo |
|--------|--------|
| 400 | Faltan datos o están mal |
| 401 | No hay sesión iniciada |
| 403 | El rol no tiene permiso |
| 404 | No existe el registro o el endpoint |
| 409 | Contenedor con incidencias que no se puede borrar, o aviso repetido de un vecino |

## Cómo ejecutarla (XAMPP)

1. Copiar la carpeta `sigeru-api-gestion` dentro de `C:\xampp\htdocs\`.
2. Prender **Apache** y **MySQL** en el panel de XAMPP.
3. Crear la base de datos importando en phpMyAdmin `sigeru-base-datos/ddl/sigeru_ddl.sql` y después `sigeru-base-datos/datos/sigeru_datos_prueba.sql`.
4. La API queda en http://localhost/sigeru-api-gestion/api.php


## Tests

En la carpeta `tests/`:

- `test_modelos.php`: tests unitarios de los modelos (usan la base de datos de prueba `sigeru_test`).
- `test_api.php`: tests de los endpoints (necesitan Apache y MySQL prendidos).

Se corren con `php tests/test_modelos.php` y `php tests/test_api.php`. Ver el README general para más detalles.
