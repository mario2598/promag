-- Script para agregar campo linea_presupuesto a bit_usuario_proyecto
-- Ejecutar si la tabla ya existe sin este campo

-- Agregar columna
ALTER TABLE `bit_usuario_proyecto` 
ADD COLUMN `linea_presupuesto` int(11) DEFAULT NULL AFTER `rubro_extra_salario`;

-- Agregar índice
ALTER TABLE `bit_usuario_proyecto`
ADD KEY `bit_usuario_proyecto_linea_fk` (`linea_presupuesto`);

-- Agregar foreign key
ALTER TABLE `bit_usuario_proyecto`
ADD CONSTRAINT `bit_usuario_proyecto_linea_fk` 
FOREIGN KEY (`linea_presupuesto`) REFERENCES `proyecto_linea_presupuesto` (`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

