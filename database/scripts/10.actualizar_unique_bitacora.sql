-- Script para actualizar la clave única de bit_usuario_proyecto
-- Permite múltiples registros por día con diferentes rubros

-- Eliminar la clave única anterior
ALTER TABLE `bit_usuario_proyecto` 
DROP INDEX `unique_usuario_proyecto_fecha`;

-- Agregar nueva clave única que incluye el rubro
ALTER TABLE `bit_usuario_proyecto`
ADD UNIQUE KEY `unique_usuario_proyecto_fecha_rubro` (`proyecto`, `usuario`, `fecha`, `rubro_extra_salario`);

