-- Script para agregar estados de bitácora de proyectos
-- Ejecutar solo si aún no existen

-- Verificar si la clase ya existe, si no, agregarla
INSERT IGNORE INTO `sis_clase` (`id`, `nombre`, `cod_general`) VALUES
(9, 'Estados de Bitácora Proyecto', 'EST_BITACORA_PROY');

-- Agregar los estados de bitácora
INSERT IGNORE INTO `sis_estado` (`id`, `nombre`, `clase`, `cod_general`) VALUES
(13, 'Bitácora Pendiente', 9, 'BIT_PROY_PENDIENTE'),
(14, 'Bitácora Aprobada', 9, 'BIT_PROY_APROBADA'),
(15, 'Bitácora Rechazada', 9, 'BIT_PROY_RECHAZADA');

