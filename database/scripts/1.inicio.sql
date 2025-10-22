-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-08-2025 a las 17:14:23
-- Versión del servidor: 10.4.25-MariaDB
-- Versión de PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `promag_cr`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora_inicio_sesion`
--

CREATE TABLE `bitacora_inicio_sesion` (
  `id` int(11) NOT NULL,
  `usuario` varchar(25) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` int(11) NOT NULL,
  `rol` varchar(50) NOT NULL,
  `codigo` varchar(15) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `rol`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rubro_extra_salario`
--

CREATE TABLE `rubro_extra_salario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `multiplicador` decimal(10,2) NOT NULL DEFAULT 1.00,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `rubro_extra_salario`
--

INSERT INTO `rubro_extra_salario` (`id`, `nombre`, `descripcion`, `multiplicador`, `estado`) VALUES
(1, 'Hora Normal', 'Pago por hora regular de trabajo', 1.00, 'A'),
(2, 'Hora Extra', 'Horas trabajadas fuera del horario normal (50% adicional)', 1.50, 'A'),
(3, 'Hora Extra Doble', 'Horas trabajadas en días feriados o nocturnas', 2.00, 'A'),
(4, 'Día Feriado', 'Trabajo en días feriados (pago doble)', 2.00, 'A'),
(5, 'Turno Nocturno', 'Trabajo en horario nocturno (25% adicional)', 1.25, 'A');

-- --------------------------------------------------------

INSERT INTO `rol` (`id`, `rol`, `codigo`, `estado`) VALUES
(1, 'Super Administrador', 'SA', 'A'),
(2, 'Administrador', 'admin', 'A'),
(3, 'Supervisor', 'supervisor', 'A'),
(4, 'Chofer', 'CHOFER', 'A'),
(5, 'Ayudante Chofer', 'AYUD_CHOFER', 'A'),

-- Puestos operativos generales
(6, 'Operario', 'OPERARIO', 'A'),
(7, 'Ayudante', 'AYUDANTE', 'A'),
(8, 'Operario Avanzado', 'OPER_AVANZADO', 'A'),

-- Puestos especializados
(9, 'Pintor', 'PINTOR', 'A'),
(10, 'Operario Electrico', 'OPER_ELECTRICO', 'A'),
(11, 'Ayudante Electrico', 'AYUD_ELECTRICO', 'A'),
(12, 'Maestro de Obras', 'MAESTRO_OBRAS', 'A'),


(36, 'Fontanero', 'FONTANERO', 'A'),
(37, 'Ayudante Fontanero', 'AYUD_FONTANERO', 'A'),
(38, 'Soldador', 'SOLDADOR', 'A');
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursal`
--

CREATE TABLE `sucursal` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `cod_general` varchar(150) NOT NULL,
  `cont_ordenes` int(11) NOT NULL DEFAULT 0,
  `nombre_factura` varchar(250) DEFAULT NULL,
  `cedula_factura` varchar(50) DEFAULT NULL,
  `correo_factura` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sucursal`
--

INSERT INTO `sucursal` (`id`, `descripcion`, `estado`, `cod_general`, `cont_ordenes`, `nombre_factura`, `cedula_factura`, `correo_factura`) VALUES
(1, 'PROMAG', 'A', 'P', 34, 'PROMAG CR', '116390363', 'PROMAGCR@GMAIL.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(25) NOT NULL,
  `ape1` varchar(25) NOT NULL,
  `ape2` varchar(25) DEFAULT NULL,
  `cedula` varchar(15) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT current_timestamp(),
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `usuario` varchar(25) NOT NULL,
  `contra` varchar(150) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `rol` int(11) NOT NULL,
  `estado` int(11) DEFAULT NULL,
  `token_auth` varchar(100) DEFAULT NULL,
  `precio_hora` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `ape1`, `ape2`, `cedula`, `fecha_nacimiento`, `fecha_ingreso`, `correo`, `telefono`, `usuario`, `contra`, 
`sucursal`, `rol`, `estado`, `token_auth`) VALUES
(1, 'Mario', 'Flores', 'Solis', '116990433', '1998-01-25', '2020-09-13 05:31:34', 'mario.flores251998@gmail.com', 
'7056418', 'mflores', '81dc9bdb52d04dc20036dbd8313ed055', 1, 1, 1, 'RxnBo0E67MBCeipxnoCeWP6gwEhBtDMEwdoAAvnf6YtHhXYWye');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vista`
--

CREATE TABLE `vista` (
  `id` int(11) NOT NULL,
  `titulo` varchar(30) NOT NULL,
  `ruta` varchar(50) NOT NULL,
  `tipo` varchar(1) NOT NULL DEFAULT 'M',
  `codigo_grupo` varchar(15) NOT NULL,
  `orden` int(11) NOT NULL,
  `peso_general` int(11) NOT NULL,
  `codigo_pantalla` varchar(30) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `inactivo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cliente_fe_info` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL COMMENT 'ID del cliente',
  `codigo_actividad` varchar(10) NOT NULL DEFAULT '722003' COMMENT 'Código de actividad económica',
  `tipo_identificacion` varchar(2) NOT NULL DEFAULT '01' COMMENT '01: Cédula Física, 02: Cédula Jurídica',
  `nombre_comercial` varchar(200) DEFAULT NULL COMMENT 'Nombre comercial del cliente',
  `direccion` text DEFAULT NULL COMMENT 'Dirección completa del cliente',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `identificacion` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Información de clientes para Facturación Electrónica';

--
-- Volcado de datos para la tabla `vista`
--

INSERT INTO `vista` (`id`, `titulo`, `ruta`, `tipo`, `codigo_grupo`, `orden`, `peso_general`, `codigo_pantalla`, `icon`, `inactivo`) VALUES
(1, 'Mantenimientos', '', 'G', 'mant', 0, 1, 'mant', 'fas fa-cogs', 0),
(2, 'Usuarios', 'mant/usuarios', 'M', 'mant', 1, 1, 'mantUsu', '', 0),
(3, 'Roles', 'mant/roles', 'M', 'mant', 2, 1, 'mantRol', '', 0),
(4, 'Sucursales', 'mant/sucursales', 'M', 'mant', 3, 1, 'mantSuc', '', 0),
(5, 'Proveedores', 'mant/proveedores', 'M', 'mant', 4, 1, 'mantPro', '', 0),
(6, 'Impuestos', 'mant/impuestos', 'M', 'mant', 7, 1, 'mantImp', '', 0),
(7, 'Tipos de Gastos', 'mant/tiposgasto', 'M', 'mant', 9, 1, 'mantTipGast', '', 0),
(8, 'Tipos de Pagos', 'mant/tipospago', 'M', 'mant', 10, 1, 'mantTipPag', '', 0),
(9, 'Tipos de Ingreso', 'mant/tiposingreso', 'M', 'mant', 11, 1, 'mantTipIng', '', 0),
(10, 'Gastos', '', 'G', 'gastos', 0, 3, 'gastos', 'fas fa-file-export', 0),
(11, 'Registrar', 'gastos/nuevo', 'M', 'gastos', 1, 3, 'gastNue', '', 0),
(12, 'Todos los gastos', 'gastos/administracion', 'M', 'gastos', 3, 3, 'gastTodos', '', 0),
(13, 'Ingresos', '', 'G', 'ingresos', 0, 4, 'ingresos', 'fas fa-file-import', 0),
(14, 'Registrar', 'ingresos/nuevo', 'M', 'ingresos', 1, 4, 'ingNue', '', 0),
(15, 'Todos los ingresos', 'ingresos/administracion', 'M', 'ingresos', 3, 4, 'ingTodos', '', 0),
(16, 'Parámetros Generales', 'mant/parametrosgenerales', 'M', 'mant', 12, 1, 'mantParGen', '', 0),
(17, 'Resumen Contable', 'informes/resumencontable', 'M', 'informes', 1, 6, 'resCont', '', 0),
(18, 'Gestión Proyectos', '', 'G', 'proyectos', 0, 5, 'proyectos', 'fas fa-project-diagram', 0),
(19, 'Proyectos', 'proyectos/proyectos', 'M', 'proyectos', 1, 5, 'proyGen', '', 0),
(20, 'Proyectos Asignados', 'proyectos/proyectos_asignados', 'M', 'proyectos', 2, 5, 'proyAsig', '', 0),
(21, 'Autorizar horas', 'proyectos/autorizar_horas', 'M', 'proyectos', 3, 5, 'proyAut', '', 0),
(22, 'Rubros Extra Salario', 'mant/rubrosextrasalario', 'M', 'mant', 13, 1, 'mantRubExtSal', '', 0),
(23, 'Clientes', 'mant/clientes', 'M', 'mant', '14', '1', 'mantClientes', '', '0');
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto`
--

CREATE TABLE `proyecto` (
  `id` int(11) NOT NULL,
  `cliente` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `usuario_encargado` int(11) NOT NULL,
  `ubicacion` varchar(500) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto_usuario`
--

CREATE TABLE `proyecto_usuario` (
  `id` int(11) NOT NULL,
  `proyecto` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto_linea_presupuesto`
--

CREATE TABLE `proyecto_linea_presupuesto` (
  `id` int(11) NOT NULL,
  `proyecto` int(11) NOT NULL,
  `numero_linea` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `monto_autorizado` decimal(15,2) NOT NULL DEFAULT 0.00,
  `monto_consumido` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bit_usuario_proyecto`
--

CREATE TABLE `bit_usuario_proyecto` (
  `id` int(11) NOT NULL,
  `proyecto` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `descripcion` text NOT NULL,
  `rubro_extra_salario` int(11) DEFAULT 1,
  `linea_presupuesto` int(11) DEFAULT NULL,
  `usuario_registro` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` int(11) NOT NULL,
  `usuario_autoriza` int(11) DEFAULT NULL,
  `fecha_autorizacion` datetime DEFAULT NULL,
  `observacion_rechazo` text DEFAULT NULL,
  UNIQUE KEY `unique_usuario_proyecto_fecha_rubro` (`proyecto`, `usuario`, `fecha`, `rubro_extra_salario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(500) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `estado` int(11) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gasto`
--

CREATE TABLE `gasto` (
  `id` int(11) NOT NULL,
  `monto` double NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `num_factura` varchar(50) DEFAULT NULL,
  `usuario` int(11) NOT NULL,
  `proveedor` int(11) DEFAULT NULL,
  `tipo_pago` int(11) NOT NULL,
  `tipo_gasto` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` varchar(150) DEFAULT NULL,
  `ingreso` int(11) DEFAULT NULL,
  `aprobado` varchar(1) DEFAULT 'N',
  `sucursal` varchar(50) NOT NULL,
  `url_factura` varchar(300) DEFAULT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `impuesto`
--

CREATE TABLE `impuesto` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `impuesto` float NOT NULL DEFAULT 0,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingreso`
--

CREATE TABLE `ingreso` (
  `id` bigint(20) NOT NULL,
  `monto_efectivo` double NOT NULL DEFAULT 0,
  `monto_tarjeta` double NOT NULL DEFAULT 0,
  `monto_sinpe` double NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo` int(11) NOT NULL,
  `observacion` varchar(150) DEFAULT NULL,
  `usuario` int(11) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `cliente` int(11) DEFAULT NULL,
  `descripcion` varchar(300) DEFAULT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `rol` int(11) NOT NULL,
  `vista` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `menu`
--

INSERT INTO `menu` (`id`, `rol`, `vista`) VALUES
(1, 1, 1),
(2, 1, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `panel_configuraciones`
--

CREATE TABLE `panel_configuraciones` (
  `id` int(11) NOT NULL,
  `color_fondo` int(11) NOT NULL DEFAULT 1,
  `color_sidebar` int(11) NOT NULL DEFAULT 1,
  `color_tema` varchar(15) NOT NULL DEFAULT 'white',
  `mini_sidebar` int(11) NOT NULL DEFAULT 1,
  `sticky_topbar` int(11) NOT NULL DEFAULT 1,
  `usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `panel_configuraciones`
--

INSERT INTO `panel_configuraciones` (`id`, `color_fondo`, `color_sidebar`, `color_tema`, `mini_sidebar`, `sticky_topbar`, `usuario`) VALUES
(1, 1, 1, 'white', 1, 1, 1);

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `cedula` varchar(15) DEFAULT NULL,
  `telefono` varchar(14) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


--
-- Estructura de tabla para la tabla `sis_clase`
--

CREATE TABLE `sis_clase` (
  `id` int(11) NOT NULL,
  `nombre` varchar(1000) NOT NULL,
  `cod_general` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sis_clase`
--

INSERT INTO `sis_clase` (`id`, `nombre`, `cod_general`) VALUES
(1, 'Tipos Ingreso', 'GEN_INGRESOS'),
(2, 'Estado usuario cliente', 'CLI_EST_USUARIO'),
(3, 'Estado de usuario', 'est_user'),
(4, 'Estados de Gastos', 'EST_GASTOS_GEN'),
(5, 'Estados de Ingresos Contables', 'INGRESOS_EST'),
(6, 'Tipos Gastos', 'GEN_GASTOS'),
(7, 'Tipos Pagos', 'GEN_TIPOS_PAGOS'),
(8, 'Estados de Proyectos', 'EST_PROYECTOS'),
(9, 'Estados de Bitácora Proyecto', 'EST_BITACORA_PROY');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sis_estado`
--

CREATE TABLE `sis_estado` (
  `id` int(11) NOT NULL,
  `nombre` varchar(5000) NOT NULL,
  `clase` int(11) NOT NULL,
  `cod_general` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sis_estado`
--

INSERT INTO `sis_estado` (`id`, `nombre`, `clase`, `cod_general`) VALUES

(1, 'Usuario Activo', 3, 'USU_ACT'),
(2, 'Usuario Inactivo', 3, 'USU_INACTIVO'),
(3, 'Aprobado', 4, 'EST_GASTO_APB'),
(4, 'Eliminado', 4, 'EST_GASTO_ELIMINADO'),
(5, 'Aprobado', 5, 'ING_EST_APROBADO'),
(6, 'Rechazados', 5, 'ING_EST_RECHAZADO'),
(7, 'Eliminados', 5, 'ING_EST_ELIMINADO'),
(8, 'Pendiente Aprobar', 5, 'ING_PEND_APB'),
(9, 'Proyecto Activo', 8, 'PROY_ACTIVO'),
(10, 'Proyecto Pausado', 8, 'PROY_PAUSADO'),
(11, 'Proyecto Finalizado', 8, 'PROY_FINALIZADO'),
(12, 'Proyecto Cancelado', 8, 'PROY_CANCELADO'),
(13, 'Bitácora Pendiente', 9, 'BIT_PROY_PENDIENTE'),
(14, 'Bitácora Aprobada', 9, 'BIT_PROY_APROBADA'),
(15, 'Bitácora Rechazada', 9, 'BIT_PROY_RECHAZADA');

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `sis_parametro`
--

CREATE TABLE `sis_parametro` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(1500) NOT NULL,
  `valor` varchar(1500) NOT NULL,
  `cod_general` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sis_tipo`
--

CREATE TABLE `sis_tipo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(1500) NOT NULL,
  `clase` int(11) NOT NULL,
  `cod_general` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sis_tipo`
--

INSERT INTO `sis_tipo` (`id`, `nombre`, `clase`, `cod_general`) VALUES
(1, 'Efectivo', 1, 'TIPO_ING_EFEC'),
(2, 'Tarjeta', 1, 'TIPO_ING_TARJ'),
(3, 'Sinpe', 1, 'TIPO_ING_SINPE'),
(4, 'Administracion', 6, 'TIPO_GASTO_ADMIN'),
(5, 'Tarjeta', 7, 'TIPO_PAGO_TARJ'),
(6, 'Sinpe', 7, 'TIPO_PAGO_SINPE'),
(7, 'Efectivo', 7, 'TIPO_PAGO_EFEC');


-- --------------------------------------------------------

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bitacora_inicio_sesion`
--
ALTER TABLE `bitacora_inicio_sesion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `bit_usuario_proyecto`
--
ALTER TABLE `bit_usuario_proyecto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bit_usuario_proyecto_proyecto_fk` (`proyecto`),
  ADD KEY `bit_usuario_proyecto_usuario_fk` (`usuario`),
  ADD KEY `bit_usuario_proyecto_usuario_reg_fk` (`usuario_registro`),
  ADD KEY `bit_usuario_proyecto_estado_fk` (`estado`),
  ADD KEY `bit_usuario_proyecto_usuario_autoriza_fk` (`usuario_autoriza`),
  ADD KEY `bit_usuario_proyecto_rubro_fk` (`rubro_extra_salario`),
  ADD KEY `bit_usuario_proyecto_linea_fk` (`linea_presupuesto`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_estado_fk` (`estado`);

--
-- Indices de la tabla `cliente_fe_info`
--
ALTER TABLE `cliente_fe_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_fe_info_cliente_fk` (`cliente_id`);

--
-- Indices de la tabla `gasto`
--
ALTER TABLE `gasto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gas_usuario_fk1` (`usuario`),
  ADD KEY `gas_proveedor_fk1` (`proveedor`);

--
-- Indices de la tabla `impuesto`
--
ALTER TABLE `impuesto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ing_cliente_fk1` (`cliente`),
  ADD KEY `ingreso_fk01` (`estado`);

--
-- Indices de la tabla `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mnu_rol_fk01` (`rol`),
  ADD KEY `mnu_vista_fk01` (`vista`);

--
-- Indices de la tabla `panel_configuraciones`
--
ALTER TABLE `panel_configuraciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pc_usuario_fk01` (`usuario`);

--
-- Indices de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proyecto_cliente_fk` (`cliente`),
  ADD KEY `proyecto_usuario_enc_fk` (`usuario_encargado`),
  ADD KEY `proyecto_estado_fk` (`estado`);

--
-- Indices de la tabla `proyecto_usuario`
--
ALTER TABLE `proyecto_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proyecto_usuario_proyecto_fk` (`proyecto`),
  ADD KEY `proyecto_usuario_usuario_fk` (`usuario`);

--
-- Indices de la tabla `proyecto_linea_presupuesto`
--
ALTER TABLE `proyecto_linea_presupuesto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proyecto_linea` (`proyecto`, `numero_linea`),
  ADD KEY `proyecto_linea_proyecto_fk` (`proyecto`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rubro_extra_salario`
--
ALTER TABLE `rubro_extra_salario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sis_clase`
--
ALTER TABLE `sis_clase`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sis_estado`
--
ALTER TABLE `sis_estado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sis_estado_fk01` (`clase`);

--
-- Indices de la tabla `sis_parametro`
--
ALTER TABLE `sis_parametro`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sis_tipo`
--
ALTER TABLE `sis_tipo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sis_tipo_fk01` (`clase`);

--
-- Indices de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usu_sucursal_fk1` (`sucursal`),
  ADD KEY `usu_rol_fk1` (`rol`),
  ADD KEY `usuario_fk01` (`estado`);

--
-- Indices de la tabla `vista`
--
ALTER TABLE `vista`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora_inicio_sesion`
--
ALTER TABLE `bitacora_inicio_sesion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bit_usuario_proyecto`
--
ALTER TABLE `bit_usuario_proyecto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente_fe_info`
--
ALTER TABLE `cliente_fe_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gasto`
--
ALTER TABLE `gasto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `impuesto`
--
ALTER TABLE `impuesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2240;

--
-- AUTO_INCREMENT de la tabla `panel_configuraciones`
--
ALTER TABLE `panel_configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proyecto_usuario`
--
ALTER TABLE `proyecto_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proyecto_linea_presupuesto`
--
ALTER TABLE `proyecto_linea_presupuesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `rubro_extra_salario`
--
ALTER TABLE `rubro_extra_salario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `sis_clase`
--
ALTER TABLE `sis_clase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `sis_estado`
--
ALTER TABLE `sis_estado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `sis_parametro`
--
ALTER TABLE `sis_parametro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `sis_tipo`
--
ALTER TABLE `sis_tipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `vista`
--
ALTER TABLE `vista`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bit_usuario_proyecto`
--
ALTER TABLE `bit_usuario_proyecto`
  ADD CONSTRAINT `bit_usuario_proyecto_proyecto_fk` FOREIGN KEY (`proyecto`) REFERENCES `proyecto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bit_usuario_proyecto_usuario_fk` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bit_usuario_proyecto_usuario_reg_fk` FOREIGN KEY (`usuario_registro`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bit_usuario_proyecto_estado_fk` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bit_usuario_proyecto_usuario_autoriza_fk` FOREIGN KEY (`usuario_autoriza`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `bit_usuario_proyecto_rubro_fk` FOREIGN KEY (`rubro_extra_salario`) REFERENCES `rubro_extra_salario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `bit_usuario_proyecto_linea_fk` FOREIGN KEY (`linea_presupuesto`) REFERENCES `proyecto_linea_presupuesto` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_estado_fk` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `cliente_fe_info`
--
ALTER TABLE `cliente_fe_info`
  ADD CONSTRAINT `cliente_fe_info_cliente_fk` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `gasto`
--
ALTER TABLE `gasto`
  ADD CONSTRAINT `gas_usuario_fk1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `gas_proveedor_fk1` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `ingreso`
--
ALTER TABLE `ingreso`
  ADD CONSTRAINT `ingreso_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `mnu_rol_fk01` FOREIGN KEY (`rol`) REFERENCES `rol` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `mnu_vista_fk01` FOREIGN KEY (`vista`) REFERENCES `vista` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `panel_configuraciones`
--
ALTER TABLE `panel_configuraciones`
  ADD CONSTRAINT `pc_usuario_fk01` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `sis_estado`
--
ALTER TABLE `sis_estado`
  ADD CONSTRAINT `sis_estado_fk01` FOREIGN KEY (`clase`) REFERENCES `sis_clase` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `sis_tipo`
--
ALTER TABLE `sis_tipo`
  ADD CONSTRAINT `sis_tipo_fk01` FOREIGN KEY (`clase`) REFERENCES `sis_clase` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `proyecto`
--
ALTER TABLE `proyecto`
  ADD CONSTRAINT `proyecto_cliente_fk` FOREIGN KEY (`cliente`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proyecto_usuario_enc_fk` FOREIGN KEY (`usuario_encargado`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proyecto_estado_fk` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `proyecto_usuario`
--
ALTER TABLE `proyecto_usuario`
  ADD CONSTRAINT `proyecto_usuario_proyecto_fk` FOREIGN KEY (`proyecto`) REFERENCES `proyecto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proyecto_usuario_usuario_fk` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `proyecto_linea_presupuesto`
--
ALTER TABLE `proyecto_linea_presupuesto`
  ADD CONSTRAINT `proyecto_linea_proyecto_fk` FOREIGN KEY (`proyecto`) REFERENCES `proyecto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usu_sucursal_fk1` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `usu_rol_fk1` FOREIGN KEY (`rol`) REFERENCES `rol` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `usuario_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
