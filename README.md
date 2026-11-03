# SiGeRU - API de Gestión

API RESTful encargada de la gestión de contenedores y camiones del
Sistema de Gestión de Residuos Urbanos (SiGeRU), desarrollado por **EcoSync**.

> **Primera entrega:** los datos se guardan en la sesión de PHP (`$_SESSION`), sin base de datos.

## Tecnologías

PHP 8.

## Estructura (MVC)

```
api.php                          Punto de entrada: recibe la solicitud y llama al controlador
config.php                       CORS y sesión
modelos/Contenedor.php           Modelo (clase) de contenedores y tipos de residuo
modelos/Camion.php               Modelo (clase) de camiones
controladores/contenedores.php   Controlador de contenedores
controladores/camiones.php       Controlador de camiones
vistas/respuesta.php             Vista: devuelve la respuesta en JSON
```

## Endpoints

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `api.php/contenedores` | Lista de contenedores |
| GET | `api.php/contenedores?id=1` | Un contenedor |
| POST | `api.php/contenedores` | Registrar un contenedor |
| GET | `api.php/tipos_residuo` | Lista de tipos de residuo |
| GET | `api.php/camiones` | Lista de camiones |
| GET | `api.php/camiones?id=1` | Un camión |
| POST | `api.php/camiones` | Registrar un camión |

Ejemplo:

```
curl -X POST http://localhost/sigeru-api-gestion/api.php/camiones -H "Content-Type: application/json" -d "{\"matricula\":\"SBA 1234\",\"estado\":\"Operativo\",\"disponibilidad\":\"Disponible\"}"
```

## Cómo ejecutarla (XAMPP)

1. Copiar la carpeta `sigeru-api-gestion` dentro de `C:\xampp\htdocs\`.
2. Prender **Apache** en el panel de XAMPP.
3. La API queda en http://localhost/sigeru-api-gestion/api.php

