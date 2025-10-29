-- Agregar campo usuario_beneficiario a la tabla cxp
-- Este campo almacena el ID del usuario que recibe el pago (el que trabajó las horas)

ALTER TABLE `cxp` 
ADD COLUMN `usuario_beneficiario` int(11) DEFAULT NULL COMMENT 'Usuario que recibe el pago (ID del usuario que trabajó las horas)' 
AFTER `usuario_creacion`;

-- Agregar índice para mejorar consultas
ALTER TABLE `cxp` 
ADD KEY `cxp_usuario_beneficiario_fk` (`usuario_beneficiario`);

-- Agregar foreign key opcional (comentado por si causa problemas con datos existentes)
-- ALTER TABLE `cxp` 
-- ADD CONSTRAINT `cxp_usuario_beneficiario_fk` 
-- FOREIGN KEY (`usuario_beneficiario`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

INSERT INTO `sis_tipo` (`id`, `nombre`, `clase`, `cod_general`) VALUES (NULL, 'Transferencia', '7', 'TIPO_PAGO_TRANS')