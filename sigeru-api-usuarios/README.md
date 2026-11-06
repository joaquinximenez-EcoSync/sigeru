# SiGeRU - API de Usuarios

API RESTful encargada del registro, la autenticación y la gestión de usuarios del
Sistema de Gestión de Residuos Urbanos (SiGeRU), desarrollado por **EcoSync**.

Los datos se guardan en la base de datos MySQL `sigeru` (ver `sigeru-base-datos`).

## Tecnologías

PHP 8.

## Estructura (MVC)

```
api.php                       Punto de entrada: recibe la solicitud, revisa el rol y llama al controlador
config.php                    Sesión y formato de respuesta
conexion.php                  Conexión a la base de datos MySQL
seguridad.php                 Revisa que haya sesión iniciada y que el rol tenga permiso
modelos/Usuario.php           Modelo (clase): guarda, busca, modifica y borra usuarios
controladores/usuarios.php    Controlador: valida los datos y usa el modelo
vistas/respuesta.php          Vista: devuelve la respuesta en JSON
```

## Endpoints

| Método | Endpoint | Descripción | Quién puede |
|--------|----------|-------------|-------------|
| POST | `api.php/registro` | Registro de un vecino (desde la landing) | Cualquiera |
| POST | `api.php/login` | Inicio de sesión | Cualquiera |
| POST | `api.php/logout` | Cierre de sesión | Cualquiera |
| GET | `api.php/sesion` | Usuario que inició sesión | Con sesión iniciada |
| GET | `api.php/usuarios` | Lista de usuarios | Administrador |
| GET | `api.php/usuarios?id=1` | Un usuario | Administrador |
| POST | `api.php/usuarios` | Crear un usuario con cualquier rol | Administrador |
| PUT | `api.php/usuarios?id=1` | Modificar un usuario (la contraseña es opcional) | Administrador |
| DELETE | `api.php/usuarios?id=1` | Borrar un usuario (no se puede borrar a sí mismo) | Administrador |

Según la RNE04, solo el administrador puede crear, modificar o eliminar usuarios.

Ejemplo:

```
curl -X POST http://localhost/sigeru-api-usuarios/api.php/login -H "Content-Type: application/json" -d "{\"email\":\"admin@sigeru.uy\",\"contrasena\":\"admin123\"}"
```

## Cómo ejecutarla (XAMPP)

1. Copiar la carpeta `sigeru-api-usuarios` dentro de `C:\xampp\htdocs\`.
2. Prender **Apache** y **MySQL** en el panel de XAMPP.
3. Crear la base de datos importando en phpMyAdmin `sigeru-base-datos/ddl/sigeru_ddl.sql` y después `sigeru-base-datos/datos/sigeru_datos_prueba.sql`.
4. La API queda en http://localhost/sigeru-api-usuarios/api.php

Usuario de prueba: **admin@sigeru.uy** / **admin123**


## Tests

En la carpeta `tests/`:

- `test_modelos.php`: tests unitarios de los modelos (usan la base de datos de prueba `sigeru_test`).
- `test_api.php`: tests de los endpoints (necesitan Apache y MySQL prendidos).

Se corren con `php tests/test_modelos.php` y `php tests/test_api.php`. Ver el README general para más detalles.
