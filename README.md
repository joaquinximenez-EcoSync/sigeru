# SiGeRU - Sistema de Gestión de Residuos Urbanos

Proyecto de pasaje de grado de **EcoSync** - ESI 2026.

## Aplicaciones

| Carpeta | Qué es |
|---------|--------|
| `sigeru-landing` | Página principal y registro de vecinos |
| `sigeru-frontend` | Inicio de sesión, panel y listados/formularios de usuarios, contenedores y camiones |
| `sigeru-api-usuarios` | API de registro, inicio de sesión y usuarios |
| `sigeru-api-gestion` | API de contenedores y camiones |
| `sigeru-base-datos` | MER, modelo físico, DDL y dump de la base de datos |

## Cómo ejecutarlo (XAMPP)

1. Copiar las carpetas `sigeru-landing`, `sigeru-frontend`, `sigeru-api-usuarios` y `sigeru-api-gestion` dentro de `C:\xampp\htdocs\`.
2. Prender **Apache** y **MySQL** en XAMPP.
3. Entrar a http://localhost/phpmyadmin → **Importar** → `sigeru-base-datos/ddl/sigeru_ddl.sql`.
4. Otra vez **Importar** → `sigeru-base-datos/datos/sigeru_datos_prueba.sql` (datos de prueba).
5. Abrir http://localhost/sigeru-landing/

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
