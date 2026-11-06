# Modelo físico de la base de datos - SiGeRU

Primera versión del modelo físico, obtenida a partir del MER (`mer/sigeru_mer.drawio`).

- **Motor:** MySQL 8
- **Base de datos:** `sigeru`
- **Juego de caracteres:** utf8mb4

## Reglas de negocio (RNE)

| Código | Regla | Cómo se cumple en el modelo |
|--------|-------|-----------------------------|
| RNE01 | Cada contenedor solo puede guardar un tipo de residuo | `contenedores.id_residuo` es una sola FK y `NOT NULL` |
| RNE02 | Una incidencia solo puede estar en un estado a la vez | `incidencias.estado` es un `ENUM('Abierta','En proceso','Resuelta')` |
| RNE03 | Un usuario solo puede tener un único rol | `usuarios.rol` es un solo `ENUM` |
| RNE04 | Solo un administrador puede crear, modificar o eliminar usuarios | Se controla en la API de Usuarios |
| RNE05 | Un centro de acopio no puede recibir un tipo de residuo que no gestiona | Tabla `centro_residuo` (se controla en la API de Gestión) |
| RNE06 | Una cuadrilla solo puede ser asignada a rutas si tiene camión disponible | Se controla en la API de Recolección |

## Tablas

PK = clave primaria, FK = clave foránea, UQ = único.

### Usuarios y roles

**usuarios** (supertipo)

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id_usuario | INT | PK, AUTO_INCREMENT |
| primer_nombre | VARCHAR(50) | NOT NULL |
| segundo_nombre | VARCHAR(50) | |
| primer_apellido | VARCHAR(50) | NOT NULL |
| segundo_apellido | VARCHAR(50) | |
| email | VARCHAR(120) | NOT NULL, UQ |
| telefono | VARCHAR(30) | (opcional) |
| contrasena | VARCHAR(255) | NOT NULL (se guarda encriptada) |
| rol | ENUM('Administrador','Operario','Recolector','Vecino') | NOT NULL |

**vecinos** (subtipo) — `id_usuario` PK y FK → usuarios, `calle` VARCHAR(100), `zona` VARCHAR(100)

**operarios** (subtipo) — `id_usuario` PK y FK → usuarios, `especialidad` VARCHAR(100)

**administradores** (subtipo) — `id_usuario` PK y FK → usuarios

**recolectores** (subtipo + relación *Integra*) — `id_usuario` PK y FK → usuarios, `id_cuadrilla` FK → cuadrillas, `fecha_ingreso` DATE

### Cuadrillas, rutas y camiones

**cuadrillas** — `id_cuadrilla` PK, `nombre` VARCHAR(100) NOT NULL

**rutas** — `id_ruta` PK, `nombre` VARCHAR(100) NOT NULL, `descripcion` VARCHAR(255), `distancia` DECIMAL(10,2)

**camiones**

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id_camion | INT | PK, AUTO_INCREMENT |
| matricula | VARCHAR(20) | NOT NULL, UQ |
| estado | ENUM('Operativo','En mantenimiento','Fuera de servicio') | NOT NULL |
| disponibilidad | ENUM('Disponible','En ruta','No disponible') | NOT NULL |
| capacidad_carga | DECIMAL(10,2) | |
| latitud | DECIMAL(9,6) | Ubicación en el mapa (simulada) |
| longitud | DECIMAL(9,6) | Ubicación en el mapa (simulada) |

**recorridos** (agregación Cuadrilla *recorre* Ruta + relación *Utilizan* con Camión)

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id_recorrido | INT | PK, AUTO_INCREMENT |
| id_cuadrilla | INT | FK → cuadrillas, NOT NULL |
| id_ruta | INT | FK → rutas, NOT NULL |
| id_camion | INT | FK → camiones |
| fecha_asignacion | DATE | |
| hora_inicio | TIME | |
| hora_fin | TIME | |

UQ (id_cuadrilla, id_ruta)

**zonas** (relación *Recorre*) — `id_zona` PK, `nombre` VARCHAR(100) NOT NULL, `zona_asignada` VARCHAR(100), `id_recorrido` FK → recorridos

**mantenimientos** (entidad débil de Camión, relación *Tiene*)

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id_camion | INT | PK, FK → camiones |
| nro_mantenimiento | INT | PK |
| tipo | VARCHAR(80) | |
| costo | DECIMAL(10,2) | |
| descripcion | TEXT | |
| fecha | DATE | |

### Contenedores e incidencias

**tipos_residuo** — `id_residuo` PK, `nombre` VARCHAR(80) NOT NULL, `descripcion` TEXT

**contenedores** (relación *Almacena*)

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id_contenedor | INT | PK, AUTO_INCREMENT |
| calle | VARCHAR(100) | NOT NULL |
| zona | VARCHAR(100) | NOT NULL (barrio) |
| municipio | ENUM('A','B','C','CH','D','E','F','G') | Municipio de Montevideo |
| estado | ENUM('Funcional','Roto','Desbordado') | NOT NULL |
| capacidad | DECIMAL(10,2) | |
| id_residuo | INT | FK → tipos_residuo, NOT NULL |
| latitud | DECIMAL(9,6) | Ubicación en el mapa |
| longitud | DECIMAL(9,6) | Ubicación en el mapa |
| ultima_recoleccion | DATETIME | Cuándo se vació por última vez |

**incidencias** (relaciones *Genera1* y *Resuelven*)

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id_incidencia | INT | PK, AUTO_INCREMENT |
| id_contenedor | INT | FK → contenedores, NOT NULL |
| fecha_deteccion | DATE | |
| zona_incidencia | VARCHAR(100) | |
| tipo | ENUM('Desbordado','Roto','Falta de recolección') | NOT NULL (agregado en la 3ra entrega) |
| estado | ENUM('Abierta','En proceso','Resuelta') | NOT NULL |
| prioridad | ENUM('Baja','Media','Alta') | NOT NULL |
| fecha | DATE | Último cambio de estado |
| fecha_resolucion | DATE | Día en que quedó Resuelta (agregado en la 3ra entrega) |
| descripcion | TEXT | |
| id_recorrido | INT | FK → recorridos (cuadrilla que la resuelve) |

**notificaciones** (entidad débil de Vecino, relaciones *Realizan* y *Genera2*)

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id_usuario | INT | PK, FK → vecinos |
| id_notificacion | INT | PK |
| estado | VARCHAR(30) | |
| descripcion | TEXT | |
| fecha | DATE | |
| id_incidencia | INT | FK → incidencias, UQ (1:1) |

### Instalaciones

**instalaciones** (supertipo, relación *Centro_Asignado*) — `id_instalacion` PK, `nombre` VARCHAR(100) NOT NULL, `calle`, `zona`, `capacidad_actual` DECIMAL(10,2), `capacidad_max` DECIMAL(10,2), `id_operario` FK → operarios, `latitud` y `longitud` DECIMAL(9,6) (ubicación en el mapa)

**centros_acopio** (subtipo) — `id_instalacion` PK y FK → instalaciones

**vertederos** (subtipo) — `id_instalacion` PK y FK → instalaciones

**maquinaria** — `id_maquinaria` PK, `nombre` VARCHAR(100) NOT NULL, `tipo` VARCHAR(80), `estado` VARCHAR(30)

**instalacion_maquinaria** (relación *Posee*, N:N) — PK (`id_instalacion`, `id_maquinaria`), ambas FK

**centro_residuo** (relación *Recibe*, N:N) — PK (`id_instalacion`, `id_residuo`), FK → centros_acopio y tipos_residuo

**centro_vertedero** (relación *Viaja2*, N:N) — PK (`id_centro`, `id_vertedero`), FK → centros_acopio y vertederos

**plantas_procesamiento** (relación *Viaja*) — `id_planta` PK, `nombre` VARCHAR(100) NOT NULL, `calle`, `zona`, `id_centro` FK → centros_acopio

## Cambios de la 3ra entrega

- `incidencias.tipo`: guarda qué problema tiene el contenedor. Antes solo estaba escrito en la descripción
  y así no se podía contar bien, por ejemplo para el top de contenedores desbordados del mes.
- `incidencias.fecha_resolucion`: el día en que la incidencia quedó Resuelta. Sirve para los informes
  y para calcular cuántos días se tarda en resolver una incidencia.

## Pasaje del MER a tablas (resumen)

| Elemento del MER | Cómo se pasó a tablas |
|------------------|------------------------|
| Atributos compuestos (Nombre completo, Ubicación, Dirección) | Se separan en columnas simples (`primer_nombre`, `calle`, `zona`...) |
| Jerarquía Usuarios → Rol | Tabla para el supertipo + una tabla por subtipo con la misma PK |
| Jerarquía Instalación | Tabla `instalaciones` + `centros_acopio` y `vertederos` |
| Entidades débiles (Mantenimiento, Notificaciones) | PK compuesta con la PK de la entidad fuerte |
| Relaciones 1:N | FK del lado N |
| Relaciones N:N | Tabla intermedia con PK compuesta |
| Agregación Cuadrilla–Ruta | Tabla `recorridos`, que participa en otras relaciones |

## Archivos

- `ddl/sigeru_ddl.sql`: DDL de creación de la base de datos y sus tablas.
- `dump/sigeru_dump_estructura.sql`: dump de la estructura (sin datos).
- `mer/sigeru_mer.drawio`: diagrama entidad-relación (se abre en https://app.diagrams.net).
