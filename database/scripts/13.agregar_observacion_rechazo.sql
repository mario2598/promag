-- Script para agregar campo observacion_rechazo a bit_usuario_proyecto
-- Ejecutar si la tabla ya existe sin este campo

ALTER TABLE `bit_usuario_proyecto` 
ADD COLUMN `observacion_rechazo` text DEFAULT NULL AFTER `fecha_autorizacion`;

