INSERT INTO `vista` (`id`, `titulo`, `ruta`, `tipo`, `codigo_grupo`, `orden`, `peso_general`, `codigo_pantalla`, `icon`, `inactivo`) VALUES

(29, 'Cotizaciones', 'proyectos/cotizaciones', 'M', 'proyectos', 4, 5, 'proyCot', '', 0);

INSERT INTO `sis_estado` (`id`, `nombre`, `clase`, `cod_general`) VALUES
(20, 'Proyecto en Cotización', 8, 'PROY_COTIZACION');

ALTER TABLE `proyecto` CHANGE `usuario_encargado` `usuario_encargado` INT(11) NULL;