-- Script para agregar campo rubro_extra_salario a bit_usuario_proyecto
-- Ejecutar si la tabla ya existe sin este campo

-- Agregar columna
ALTER TABLE `bit_usuario_proyecto` 
ADD COLUMN `rubro_extra_salario` int(11) DEFAULT 1 AFTER `descripcion`;

-- Agregar índice
ALTER TABLE `bit_usuario_proyecto`
ADD KEY `bit_usuario_proyecto_rubro_fk` (`rubro_extra_salario`);

-- Agregar foreign key
ALTER TABLE `bit_usuario_proyecto`
ADD CONSTRAINT `bit_usuario_proyecto_rubro_fk` 
FOREIGN KEY (`rubro_extra_salario`) REFERENCES `rubro_extra_salario` (`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

