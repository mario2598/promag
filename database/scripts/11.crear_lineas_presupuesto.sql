-- Script para crear tabla de líneas de presupuesto de proyectos
-- Ejecutar si la base de datos ya existe

-- Crear tabla
CREATE TABLE IF NOT EXISTS `proyecto_linea_presupuesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proyecto` int(11) NOT NULL,
  `numero_linea` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `monto_autorizado` decimal(15,2) NOT NULL DEFAULT 0.00,
  `monto_consumido` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_proyecto_linea` (`proyecto`, `numero_linea`),
  KEY `proyecto_linea_proyecto_fk` (`proyecto`),
  CONSTRAINT `proyecto_linea_proyecto_fk` FOREIGN KEY (`proyecto`) REFERENCES `proyecto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

