-- Script para crear tabla de rubros extra salariales
-- Ejecutar si la base de datos ya existe

-- Crear tabla
CREATE TABLE IF NOT EXISTS `rubro_extra_salario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `multiplicador` decimal(10,2) NOT NULL DEFAULT 1.00,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar rubros por defecto
INSERT INTO `rubro_extra_salario` (`id`, `nombre`, `descripcion`, `multiplicador`, `estado`) VALUES
(1, 'Hora Normal', 'Pago por hora regular de trabajo', 1.00, 'A'),
(2, 'Hora Extra', 'Horas trabajadas fuera del horario normal (50% adicional)', 1.50, 'A'),
(3, 'Hora Extra Doble', 'Horas trabajadas en días feriados o nocturnas', 2.00, 'A'),
(4, 'Día Feriado', 'Trabajo en días feriados (pago doble)', 2.00, 'A'),
(5, 'Turno Nocturno', 'Trabajo en horario nocturno (25% adicional)', 1.25, 'A')
ON DUPLICATE KEY UPDATE 
  `nombre` = VALUES(`nombre`),
  `descripcion` = VALUES(`descripcion`),
  `multiplicador` = VALUES(`multiplicador`);

