# SiGeRU - API de Usuarios

API RESTful encargada del registro, la autenticación y la gestión de usuarios del
Sistema de Gestión de Residuos Urbanos (SiGeRU), desarrollado por **EcoSync**.

> **Primera entrega:** los datos se guardan en la sesión de PHP (`$_SESSION`), sin base de datos.

## Tecnologías

PHP 8.

## Estructura (MVC)

```
api.php                       Punto de entrada: recibe la solicitud y llama al controlador
config.php                    CORS, roles y sesión
modelos/Usuario.php           Modelo (clase): guarda y busca usuarios
controladores/usuarios.php    Controlador: valida los datos y usa el modelo
vistas/respuesta.php          Vista: devuelve la respuesta en JSON
```

## Endpoints

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `api.php/registro` | Registro de un vecino (desde la landing) |
| POST | `api.php/login` | Inicio de sesión |
| POST | `api.php/logout` | Cierre de sesión |
| GET | `api.php/sesion` | Usuario que inició sesión |
| GET | `api.php/usuarios` | Lista de usuarios |
| GET | `api.php/usuarios?id=1` | Un usuario |
| POST | `api.php/usuarios` | Crear un usuario con cualquier rol |

Ejemplo:

```
curl -X POST http://localhost:8002/api.php/login -H "Content-Type: application/json" -d "{\"email\":\"admin@sigeru.uy\",\"contrasena\":\"admin123\"}"
```

## Cómo ejecutarla

```
php -S localhost:8002
```

Usuario de prueba: **admin@sigeru.uy** / **admin123**
