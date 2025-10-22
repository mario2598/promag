-- Script para agregar campos de autorización a la tabla bit_usuario_proyecto
-- Ejecutar si la tabla ya existe sin estos campos

ALTER TABLE `bit_usuario_proyecto` 
ADD COLUMN `usuario_autoriza` int(11) DEFAULT NULL AFTER `estado`,
ADD COLUMN `fecha_autorizacion` datetime DEFAULT NULL AFTER `usuario_autoriza`;

-- Agregar foreign key para usuario_autoriza (opcional, solo si la tabla usuario existe)
ALTER TABLE `bit_usuario_proyecto`
ADD CONSTRAINT `bit_usuario_proyecto_usuario_autoriza_fk` 
FOREIGN KEY (`usuario_autoriza`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

