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
(1, 'Ruta Centro', 'Centro y Ciudad Vieja', 12.50),
(2, 'Ruta Costa', 'Rambla desde Pocitos hasta Carrasco', 18.00);

-- ---------- Camiones ----------
INSERT INTO camiones (id_camion, matricula, estado, disponibilidad, capacidad_carga) VALUES
(1, 'SBA 1234', 'Operativo', 'Disponible', 10000),
(2, 'SBC 5678', 'En mantenimiento', 'No disponible', 8000),
(3, 'SBD 9012', 'Operativo', 'En ruta', 12000);

INSERT INTO mantenimientos (id_camion, nro_mantenimiento, tipo, costo, descripcion, fecha) VALUES
(2, 1, 'Preventivo', 15000, 'Cambio de aceite y filtros', '2026-10-20');

INSERT INTO recorridos (id_recorrido, id_cuadrilla, id_ruta, id_camion, fecha_asignacion, hora_inicio, hora_fin) VALUES
(1, 1, 1, 1, '2026-11-02', '06:00', '12:00'),
(2, 2, 2, 3, '2026-11-02', '07:00', '13:00');

INSERT INTO zonas (id_zona, nombre, zona_asignada, id_recorrido) VALUES
(1, 'Centro', 'Municipio B', 1),
(2, 'Pocitos', 'Municipio CH', 2),
(3, 'Carrasco', 'Municipio E', 2);

-- ---------- Tipos de residuo y contenedores ----------
INSERT INTO tipos_residuo (id_residuo, nombre, descripcion) VALUES
(1, 'Orgánico', 'Restos de comida y residuos biodegradables'),
(2, 'Reciclable', 'Plástico, papel, cartón, vidrio y metal'),
(3, 'No reciclable', 'Residuos generales sin clasificar'),
(4, 'Peligroso', 'Pilas, baterías y productos químicos');

INSERT INTO contenedores (id_contenedor, calle, zona, estado, capacidad, id_residuo) VALUES
(1, 'Av. 18 de Julio 1200', 'Centro', 'Funcional', 1000, 2),
(2, 'Rambla República de México 5500', 'Carrasco', 'Desbordado', 1200, 3),
(3, 'Bv. Artigas 1500', 'Tres Cruces', 'Roto', 800, 1);

INSERT INTO incidencias (id_incidencia, id_contenedor, fecha_deteccion, zona_incidencia, estado, prioridad, fecha, descripcion, id_recorrido) VALUES
(1, 2, '2026-11-01', 'Carrasco', 'Abierta', 'Alta', '2026-11-01', 'Contenedor desbordado, hay basura en la vereda', 2),
(2, 3, '2026-10-28', 'Tres Cruces', 'En proceso', 'Media', '2026-10-28', 'La tapa del contenedor está rota', 1);

INSERT INTO notificaciones (id_usuario, id_notificacion, estado, descripcion, fecha, id_incidencia) VALUES
(4, 1, 'Recibida', 'El contenedor de la rambla está lleno hace dos días', '2026-11-01', 1);

-- ---------- Instalaciones ----------
INSERT INTO instalaciones (id_instalacion, nombre, calle, zona, capacidad_actual, capacidad_max, id_operario) VALUES
(1, 'Centro de Acopio Norte', 'Camino Carrasco 4500', 'Piedras Blancas', 3500, 10000, 2),
(2, 'Vertedero Felipe Cardoso', 'Camino Felipe Cardoso', 'Punta de Rieles', 50000, 200000, 2);

INSERT INTO centros_acopio (id_instalacion) VALUES (1);
INSERT INTO vertederos (id_instalacion) VALUES (2);

INSERT INTO maquinaria (id_maquinaria, nombre, tipo, estado) VALUES
(1, 'Prensa compactadora', 'Compactadora', 'Operativa'),
(2, 'Cinta clasificadora', 'Clasificadora', 'En reparación');

INSERT INTO instalacion_maquinaria (id_instalacion, id_maquinaria) VALUES (1, 1), (1, 2);
INSERT INTO centro_residuo (id_instalacion, id_residuo) VALUES (1, 1), (1, 2);
INSERT INTO centro_vertedero (id_centro, id_vertedero) VALUES (1, 2);

INSERT INTO plantas_procesamiento (id_planta, nombre, calle, zona, id_centro) VALUES
(1, 'Planta de Reciclaje Oeste', 'Ruta 1 km 10', 'Paso de la Arena', 1);
