-- =====================================================================
-- SiGeRU - Datos de prueba
-- Importar DESPUES de ddl/sigeru_ddl.sql
--
-- Usuarios de prueba:
--   admin@sigeru.uy      / admin123  (Administrador)
--   operario@sigeru.uy   / 123456    (Operario)
--   recolector@sigeru.uy / 123456    (Recolector)
--   vecino@sigeru.uy     / 123456    (Vecino)
-- =====================================================================

USE sigeru;
SET NAMES utf8mb4;

-- ---------- Usuarios ----------
INSERT INTO usuarios (id_usuario, primer_nombre, primer_apellido, email, telefono, contrasena, rol) VALUES
(1, 'Usuario', 'Administrador', 'admin@sigeru.uy', '099111111', '$2y$10$eIUne.OuwuHmfbKqMtfv..EDwPH7ITgsi3PCJm4yns3BUxOrhR1b.', 'Administrador'),
(2, 'Martín', 'Pereira', 'operario@sigeru.uy', '099222222', '$2y$10$yTNFUbFLVtSgsCdnvGteAuECBj8uIGpJy4g1MoPYL2iK.8KFXEW8O', 'Operario'),
(3, 'Lucía', 'Fernández', 'recolector@sigeru.uy', '099333333', '$2y$10$yTNFUbFLVtSgsCdnvGteAuECBj8uIGpJy4g1MoPYL2iK.8KFXEW8O', 'Recolector'),
(4, 'Sofía', 'González', 'vecino@sigeru.uy', '099444444', '$2y$10$yTNFUbFLVtSgsCdnvGteAuECBj8uIGpJy4g1MoPYL2iK.8KFXEW8O', 'Vecino');

INSERT INTO administradores (id_usuario) VALUES (1);
INSERT INTO operarios (id_usuario, especialidad) VALUES (2, 'Mantenimiento de contenedores');
INSERT INTO vecinos (id_usuario, calle, zona) VALUES (4, 'Av. Brasil 2500', 'Pocitos');

-- ---------- Cuadrillas y rutas ----------
INSERT INTO cuadrillas (id_cuadrilla, nombre) VALUES
(1, 'Cuadrilla Centro'),
(2, 'Cuadrilla Este');

INSERT INTO recolectores (id_usuario, id_cuadrilla, fecha_ingreso) VALUES (3, 1, '2026-03-01');

INSERT INTO rutas (id_ruta, nombre, descripcion, distancia) VALUES
(1, 'Ruta Centro', 'Ciudad Vieja, Centro y Tres Cruces', 12.50),
(2, 'Ruta Costa', 'Punta Carretas, Pocitos, Malvín y Carrasco', 18.00);

-- ---------- Camiones (latitud y longitud: posicion simulada en el mapa) ----------
INSERT INTO camiones (id_camion, matricula, estado, disponibilidad, capacidad_carga, latitud, longitud) VALUES
(1, 'SBA 1234', 'Operativo', 'Disponible', 10000, -34.874000, -56.169000),
(2, 'SBC 5678', 'En mantenimiento', 'No disponible', 8000, -34.872000, -56.172000),
(3, 'SBD 9012', 'Operativo', 'En ruta', 12000, -34.901000, -56.118000),
(4, 'SBF 3344', 'Operativo', 'En ruta', 10000, -34.905500, -56.195000);

INSERT INTO mantenimientos (id_camion, nro_mantenimiento, tipo, costo, descripcion, fecha) VALUES
(2, 1, 'Preventivo', 15000, 'Cambio de aceite y filtros', '2026-10-20');

-- La cuadrilla Centro (donde esta la recolectora de prueba) hace la Ruta Centro con el camion SBF 3344
INSERT INTO recorridos (id_recorrido, id_cuadrilla, id_ruta, id_camion, fecha_asignacion, hora_inicio, hora_fin) VALUES
(1, 1, 1, 4, '2026-11-06', '06:00', '12:00'),
(2, 2, 2, 3, '2026-11-06', '07:00', '13:00');

-- Zonas (barrios) que recorre cada recorrido
INSERT INTO zonas (id_zona, nombre, zona_asignada, id_recorrido) VALUES
(1, 'Ciudad Vieja', 'Municipio B', 1),
(2, 'Centro', 'Municipio B', 1),
(3, 'Tres Cruces', 'Municipio CH', 1),
(4, 'Punta Carretas', 'Municipio CH', 2),
(5, 'Pocitos', 'Municipio CH', 2),
(6, 'Malvín', 'Municipio E', 2),
(7, 'Carrasco', 'Municipio E', 2);

-- ---------- Tipos de residuo y contenedores ----------
INSERT INTO tipos_residuo (id_residuo, nombre, descripcion) VALUES
(1, 'Orgánico', 'Restos de comida y residuos biodegradables'),
(2, 'Reciclable', 'Plástico, papel, cartón, vidrio y metal'),
(3, 'No reciclable', 'Residuos generales sin clasificar'),
(4, 'Peligroso', 'Pilas, baterías y productos químicos');

-- Contenedores en distintos barrios y municipios de Montevideo
INSERT INTO contenedores (id_contenedor, calle, zona, municipio, estado, capacidad, id_residuo, latitud, longitud, ultima_recoleccion) VALUES
(1, 'Av. 18 de Julio 1200', 'Centro', 'B', 'Funcional', 1000, 2, -34.905500, -56.188900, '2026-11-05 07:30'),
(2, 'Rambla República de México 5500', 'Carrasco', 'E', 'Desbordado', 1200, 3, -34.892800, -56.056500, '2026-11-01 08:00'),
(3, 'Bv. Artigas 1500', 'Tres Cruces', 'CH', 'Roto', 800, 1, -34.894400, -56.166900, '2026-11-04 09:10'),
(4, '25 de Mayo 500', 'Ciudad Vieja', 'B', 'Funcional', 1000, 3, -34.906700, -56.207400, '2026-11-05 06:45'),
(5, 'Plaza Independencia', 'Centro', 'B', 'Funcional', 1200, 2, -34.906900, -56.199600, '2026-11-05 07:00'),
(6, 'Av. Brasil 2500', 'Pocitos', 'CH', 'Desbordado', 1000, 3, -34.908300, -56.150800, '2026-11-02 10:00'),
(7, 'Rambla Gandhi 600', 'Punta Carretas', 'CH', 'Funcional', 800, 2, -34.922100, -56.158800, '2026-11-05 08:15'),
(8, 'Av. Italia 4500', 'Malvín', 'E', 'Funcional', 1000, 1, -34.885500, -56.111000, '2026-11-04 11:00'),
(9, 'Av. Agraciada 3000', 'Paso Molino', 'C', 'Funcional', 1000, 3, -34.867600, -56.206900, '2026-11-05 06:30'),
(10, 'Av. Millán 4000', 'Prado', 'C', 'Roto', 800, 1, -34.854500, -56.207800, '2026-11-03 07:20'),
(11, 'Av. Carlos María Ramírez 1500', 'Cerro', 'A', 'Desbordado', 1200, 3, -34.883100, -56.250300, '2026-10-31 09:00'),
(12, 'Av. Gral. Flores 4500', 'Piedras Blancas', 'D', 'Funcional', 1000, 3, -34.835900, -56.155300, '2026-11-05 10:30'),
(13, 'Camino Maldonado 6000', 'Punta de Rieles', 'F', 'Funcional', 1000, 2, -34.832600, -56.104100, '2026-11-04 12:00'),
(14, 'Av. Garzón 1500', 'Colón', 'G', 'Roto', 800, 3, -34.798700, -56.226000, '2026-11-02 08:40'),
(15, 'Av. 8 de Octubre 3500', 'Unión', 'E', 'Funcional', 1000, 4, -34.876800, -56.144900, '2026-11-05 09:00'),
(16, 'Av. Rivera 3000', 'Pocitos', 'CH', 'Funcional', 1000, 4, -34.897900, -56.159500, '2026-11-05 09:30');

INSERT INTO incidencias (id_incidencia, id_contenedor, fecha_deteccion, zona_incidencia, estado, prioridad, fecha, descripcion, id_recorrido) VALUES
(1, 2, '2026-11-01', 'Carrasco', 'Abierta', 'Alta', '2026-11-01', 'Contenedor desbordado, hay basura en la vereda', 2),
(2, 3, '2026-10-28', 'Tres Cruces', 'En proceso', 'Media', '2026-10-28', 'La tapa del contenedor está rota', 1),
(3, 6, '2026-11-02', 'Pocitos', 'Abierta', 'Alta', '2026-11-02', 'Contenedor desbordado desde el fin de semana', 2),
(4, 11, '2026-10-31', 'Cerro', 'Abierta', 'Alta', '2026-10-31', 'Contenedor desbordado, bolsas en la calle', NULL),
(5, 10, '2026-11-03', 'Prado', 'Abierta', 'Media', '2026-11-03', 'Tapa rota', NULL),
(6, 14, '2026-11-02', 'Colón', 'Abierta', 'Baja', '2026-11-02', 'Una rueda del contenedor está rota', NULL),
(7, 1, '2026-10-25', 'Centro', 'Resuelta', 'Baja', '2026-10-25', 'Falta de recolección', 1),
(8, 2, '2026-10-20', 'Carrasco', 'Resuelta', 'Media', '2026-10-20', 'Contenedor desbordado', 2);

INSERT INTO notificaciones (id_usuario, id_notificacion, estado, descripcion, fecha, id_incidencia) VALUES
(4, 1, 'Recibida', 'El contenedor de la rambla está lleno hace dos días', '2026-11-01', 1);

-- ---------- Instalaciones ----------
INSERT INTO instalaciones (id_instalacion, nombre, calle, zona, capacidad_actual, capacidad_max, id_operario, latitud, longitud) VALUES
(1, 'Centro de Acopio Norte', 'Camino Carrasco 4500', 'Malvín Norte', 3500, 10000, 2, -34.870900, -56.106600),
(2, 'Vertedero Felipe Cardoso', 'Camino Felipe Cardoso', 'Punta de Rieles', 50000, 200000, 2, -34.817600, -56.121400),
(3, 'Centro de Acopio Oeste', 'Ruta 1 km 8', 'Paso de la Arena', 1200, 8000, NULL, -34.838000, -56.290000);

INSERT INTO centros_acopio (id_instalacion) VALUES (1), (3);
INSERT INTO vertederos (id_instalacion) VALUES (2);

INSERT INTO maquinaria (id_maquinaria, nombre, tipo, estado) VALUES
(1, 'Prensa compactadora', 'Compactadora', 'Operativa'),
(2, 'Cinta clasificadora', 'Clasificadora', 'En reparación');

INSERT INTO instalacion_maquinaria (id_instalacion, id_maquinaria) VALUES (1, 1), (1, 2);
INSERT INTO centro_residuo (id_instalacion, id_residuo) VALUES (1, 1), (1, 2), (3, 2), (3, 3);
INSERT INTO centro_vertedero (id_centro, id_vertedero) VALUES (1, 2), (3, 2);

INSERT INTO plantas_procesamiento (id_planta, nombre, calle, zona, id_centro) VALUES
(1, 'Planta de Reciclaje Oeste', 'Ruta 1 km 10', 'Paso de la Arena', 1);
