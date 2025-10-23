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
-- Base de datos: `promag`
--
use promag;
-- =========================================================
-- TABLA: bitacora_inicio_sesion
-- =========================================================

CREATE TABLE `bitacora_inicio_sesion` (
  `id` int(11) NOT NULL,
  `usuario` varchar(25) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLA: sucursal
-- =========================================================

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

INSERT INTO `sucursal` (`id`, `descripcion`, `estado`, `cod_general`, `cont_ordenes`, `nombre_factura`, `cedula_factura`, `correo_factura`) VALUES
(1, 'PROMAG', 'A', 'P', 34, 'PROMAG CR', '116390363', 'PROMAGCR@GMAIL.com');

-- =========================================================
-- TABLA: sis_clase
-- =========================================================

CREATE TABLE `sis_clase` (
  `id` int(11) NOT NULL,
  `nombre` varchar(1000) NOT NULL,
  `cod_general` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sis_clase` (`id`, `nombre`, `cod_general`) VALUES
(1, 'Tipos Ingreso', 'GEN_INGRESOS'),
(2, 'Estado usuario cliente', 'CLI_EST_USUARIO'),
(3, 'Estado de usuario', 'est_user'),
(4, 'Estados de Gastos', 'EST_GASTOS_GEN'),
(5, 'Estados de Ingresos Contables', 'INGRESOS_EST'),
(6, 'Tipos Gastos', 'GEN_GASTOS'),
(7, 'Tipos Pagos', 'GEN_TIPOS_PAGOS'),
(8, 'Estados de Proyectos', 'EST_PROYECTOS'),
(9, 'Estados de Bitácora Proyecto', 'EST_BITACORA_PROY'),
(10, 'Estados de Cuentas por Pagar', 'EST_CXP'),
(11, 'Tipos de Cuentas por Pagar', 'TIPOS_CXP');

-- =========================================================
-- TABLA: sis_estado
-- =========================================================

CREATE TABLE `sis_estado` (
  `id` int(11) NOT NULL,
  `nombre` varchar(5000) NOT NULL,
  `clase` int(11) NOT NULL,
  `cod_general` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
(15, 'Bitácora Rechazada', 9, 'BIT_PROY_RECHAZADA'),
(16, 'CxP Pendiente', 10, 'CXP_PENDIENTE'),
(17, 'CxP Aprobada', 10, 'CXP_APROBADA'),
(18, 'CxP Pagada', 10, 'CXP_PAGADA'),
(19, 'CxP Cancelada', 10, 'CXP_CANCELADA');

-- =========================================================
-- TABLA: sis_tipo
-- =========================================================

CREATE TABLE `sis_tipo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(1500) NOT NULL,
  `clase` int(11) NOT NULL,
  `cod_general` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sis_tipo` (`id`, `nombre`, `clase`, `cod_general`) VALUES
(1, 'Efectivo', 1, 'TIPO_ING_EFEC'),
(2, 'Tarjeta', 1, 'TIPO_ING_TARJ'),
(3, 'Sinpe', 1, 'TIPO_ING_SINPE'),
(4, 'Administracion', 6, 'TIPO_GASTO_ADMIN'),
(5, 'Tarjeta', 7, 'TIPO_PAGO_TARJ'),
(6, 'Sinpe', 7, 'TIPO_PAGO_SINPE'),
(7, 'Efectivo', 7, 'TIPO_PAGO_EFEC'),
(10, 'Pago de horas trabajadas', 11, 'CXP_PAGO_HORAS'),
(11, 'CxP por Servicios', 11, 'CXP_SERVICIOS'),
(12, 'CxP por Materiales', 11, 'CXP_MATERIALES'),
(13, 'CxP por Gastos', 11, 'CXP_GASTOS'),
(14, 'CxP por Honorarios', 11, 'CXP_HONORARIOS');

-- =========================================================
-- TABLA: sis_parametro
-- =========================================================

CREATE TABLE `sis_parametro` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(1500) NOT NULL,
  `valor` varchar(1500) NOT NULL,
  `cod_general` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLA: rol
-- =========================================================

CREATE TABLE `rol` (
  `id` int(11) NOT NULL,
  `rol` varchar(50) NOT NULL,
  `codigo` varchar(25) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rol` (`id`, `rol`, `codigo`, `estado`) VALUES
(1, 'Super Administrador', 'SA', 'A'),
(2, 'Administrador', 'admin', 'A'),
(3, 'Supervisor', 'supervisor', 'A'),
(4, 'Maestro de Obras', 'MAESTRO_OBRAS', 'A'),
(5, 'Chofer', 'CHOFER', 'A'),
(6, 'Ayudante de Chofer', 'AYUD_CHOFER', 'A'),
(7, 'Operario Construcción', 'OPER_CONSTRUCCION', 'A'),
(8, 'Operario Gypsum', 'OPER_GYPSUM', 'A'),
(9, 'Ayudante de Operario', 'AYUD_OPERARIO', 'A'),
(10, 'Operario Pintura', 'OPER_PINTURA', 'A'),
(11, 'Operario Eléctrico', 'OPER_ELECTRICO', 'A'),
(12, 'Ayudante Eléctrico', 'AYUD_ELECTRICO', 'A'),
(13, 'Operario Fontanero', 'OPER_FONTANERO', 'A'),
(14, 'Operario Soldador', 'OPER_SOLDADOR', 'A');

-- =========================================================
-- TABLA: rubro_extra_salario
-- =========================================================

CREATE TABLE `rubro_extra_salario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `multiplicador` decimal(10,2) NOT NULL DEFAULT 1.00,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rubro_extra_salario` (`id`, `nombre`, `descripcion`, `multiplicador`, `estado`) VALUES
(1, 'Hora Normal', 'Pago por hora regular de trabajo', 1.00, 'A'),
(2, 'Hora Extra', 'Horas trabajadas fuera del horario normal (50% adicional)', 1.50, 'A'),
(3, 'Hora Extra Doble', 'Horas trabajadas en días feriados o nocturnas', 2.00, 'A'),
(4, 'Día Feriado', 'Trabajo en días feriados (pago doble)', 2.00, 'A'),
(5, 'Turno Nocturno', 'Trabajo en horario nocturno (25% adicional)', 1.25, 'A');

-- =========================================================
-- TABLA: usuario
-- =========================================================

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
  `precio_hora` double DEFAULT 0,
  `nombre_beneficiario` varchar(200) DEFAULT NULL COMMENT 'Nombre del beneficiario para pagos',
  `numero_cuenta` varchar(50) DEFAULT NULL COMMENT 'Número de cuenta bancaria',
  `nombre_banco` varchar(100) DEFAULT NULL COMMENT 'Nombre del banco del beneficiario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `usuario` (`id`, `nombre`, `ape1`, `ape2`, `cedula`, `fecha_nacimiento`, `fecha_ingreso`, `correo`, `telefono`, `usuario`, `contra`, 
`sucursal`, `rol`, `estado`, `token_auth`) VALUES
(1, 'Mario', 'Flores', 'Solis', '116990433', '1998-01-25', '2020-09-13 05:31:34', 'mario.flores251998@gmail.com', 
'7056418', 'mflores', '81dc9bdb52d04dc20036dbd8313ed055', 1, 1, 1, 'RxnBo0E67MBCeipxnoCeWP6gwEhBtDMEwdoAAvnf6YtHhXYWye');

-- =========================================================
-- TABLA: vista
-- =========================================================

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

INSERT INTO `vista` (`id`, `titulo`, `ruta`, `tipo`, `codigo_grupo`, `orden`, `peso_general`, `codigo_pantalla`, `icon`, `inactivo`) VALUES
(1, 'Mantenimientos', '', 'G', 'mant', 0, 1, 'mant', 'fas fa-cogs', 0),
(2, 'Usuarios', 'mant/usuarios', 'M', 'mant', 1, 1, 'mantUsu', '', 0),
(3, 'Roles', 'mant/roles', 'M', 'mant', 2, 1, 'mantRol', '', 0),
(4, 'Sucursales', 'mant/sucursales', 'M', 'mant', 3, 1, 'mantSuc', '', 0),
(5, 'Proveedores', 'mant/proveedores', 'M', 'mant', 4, 1, 'mantPro', '', 0),
(6, 'Impuestos', 'mant/impuestos', 'M', 'mant', 7, 1, 'mantImp', '', 0),
(10, 'Gastos', '', 'G', 'gastos', 0, 3, 'gastos', 'fas fa-file-export', 0),
(11, 'Registrar', 'gastos/nuevo', 'M', 'gastos', 1, 3, 'gastNue', '', 0),
(12, 'Todos los gastos', 'gastos/administracion', 'M', 'gastos', 3, 3, 'gastTodos', '', 0),
(13, 'Ingresos', '', 'G', 'ingresos', 0, 4, 'ingresos', 'fas fa-file-import', 0),
(14, 'Registrar', 'ingresos/nuevo', 'M', 'ingresos', 1, 4, 'ingNue', '', 0),
(15, 'Todos los ingresos', 'ingresos/administracion', 'M', 'ingresos', 3, 4, 'ingTodos', '', 0),
(16, 'Parámetros Generales', 'mant/parametrosgenerales', 'M', 'mant', 12, 1, 'mantParGen', '', 0),
(-1, 'Informes', '', 'G', 'informes', 0, 6, 'informes', 'fas fa-chart-line', 0),
(17, 'Resumen Contable', 'informes/resumencontable', 'M', 'informes', 1, 6, 'resCont', '', 0),
(18, 'Gestión Proyectos', '', 'G', 'proyectos', 0, 5, 'proyectos', 'fas fa-project-diagram', 0),
(19, 'Proyectos', 'proyectos/proyectos', 'M', 'proyectos', 1, 5, 'proyGen', '', 0),
(20, 'Proyectos Asignados', 'proyectos/proyectos_asignados', 'M', 'proyectos', 2, 5, 'proyAsig', '', 0),
(21, 'Autorizar horas', 'proyectos/autorizar_horas', 'M', 'proyectos', 3, 5, 'proyAut', '', 0),
(22, 'Rubros Extra Salario', 'mant/rubrosextrasalario', 'M', 'mant', 13, 1, 'mantRubExtSal', '', 0),
(23, 'Clientes', 'mant/clientes', 'M', 'mant', 14, 1, 'mantClientes', '', 0),
(24, 'Rubros Deduccion Salario', 'mant/rubrosdeduccionsalario', 'M', 'mant', 15, 1, 'mantRubDedSal', '', 0),
(25, 'Cuentas por Pagar', '', 'G', 'cxp', 0, 16, 'cxp', 'fas fa-file-invoice-dollar', 0),
(26, 'Cuentas por Pagar', 'cxp/index', 'M', 'cxp', 1, 16, 'cxpIndex', '', 0),
(27, 'Monedas', 'mant/monedas', 'M', 'mant', 17, 1, 'mantMonedas', '', 0),
(28, 'Historial Pagos Proyectos', 'cxp/historial_pagos_proyectos', 'M', 'cxp', 2, 16, 'cxpHistPagpProy', '', 0);

-- =========================================================
-- TABLA: cliente_fe_info
-- =========================================================

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

-- =========================================================
-- TABLA: proyecto
-- =========================================================

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

-- =========================================================
-- TABLA: proyecto_usuario
-- =========================================================

CREATE TABLE `proyecto_usuario` (
  `id` int(11) NOT NULL,
  `proyecto` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLA: proyecto_linea_presupuesto
-- =========================================================

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

-- =========================================================
-- TABLA: bit_usuario_proyecto
-- =========================================================

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
  `cxp` int(11) DEFAULT NULL COMMENT 'CxP asociada (opcional)',
  `usuario_registro` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` int(11) NOT NULL,
  `usuario_autoriza` int(11) DEFAULT NULL,
  `fecha_autorizacion` datetime DEFAULT NULL,
  `observacion_rechazo` text DEFAULT NULL,
  UNIQUE KEY `unique_usuario_proyecto_fecha_rubro` (`proyecto`, `usuario`, `fecha`, `rubro_extra_salario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLA: cliente
-- =========================================================

CREATE TABLE `cliente` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(500) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `estado` int(11) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLA: gasto
-- =========================================================

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
  `estado` int(11) NOT NULL,
  `codigo_moneda` varchar(10) DEFAULT 'CRC' COMMENT 'Código de la moneda utilizada',
  `tipo_cambio` decimal(10,4) DEFAULT 1.0000 COMMENT 'Tipo de cambio al momento de la transacción'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLA: impuesto
-- =========================================================

CREATE TABLE `impuesto` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `impuesto` float NOT NULL DEFAULT 0,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLA: ingreso
-- =========================================================

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
  `estado` int(11) NOT NULL,
  `codigo_moneda` varchar(10) DEFAULT 'CRC' COMMENT 'Código de la moneda utilizada',
  `tipo_cambio` decimal(10,4) DEFAULT 1.0000 COMMENT 'Tipo de cambio al momento de la transacción'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLA: menu
-- =========================================================

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `rol` int(11) NOT NULL,
  `vista` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `menu` (`id`, `rol`, `vista`) VALUES
(1, 1, 1),
(2, 1, 3);

-- =========================================================
-- TABLA: panel_configuraciones
-- =========================================================

CREATE TABLE `panel_configuraciones` (
  `id` int(11) NOT NULL,
  `color_fondo` int(11) NOT NULL DEFAULT 1,
  `color_sidebar` int(11) NOT NULL DEFAULT 1,
  `color_tema` varchar(15) NOT NULL DEFAULT 'white',
  `mini_sidebar` int(11) NOT NULL DEFAULT 1,
  `sticky_topbar` int(11) NOT NULL DEFAULT 1,
  `usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `panel_configuraciones` (`id`, `color_fondo`, `color_sidebar`, `color_tema`, `mini_sidebar`, `sticky_topbar`, `usuario`) VALUES
(1, 1, 1, 'white', 1, 1, 1);

-- =========================================================
-- TABLA: proveedor
-- =========================================================

CREATE TABLE `proveedor` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `cedula` varchar(15) DEFAULT NULL,
  `telefono` varchar(14) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLA: rubro_deduccion_salario
-- =========================================================

CREATE TABLE `rubro_deduccion_salario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `porcentaje_deduccion` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje de deducción (0.00 a 100.00)',
  `estado` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `rubro_deduccion_salario_estado_fk` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Rubros de deducción salarial';

INSERT INTO `rubro_deduccion_salario` (`nombre`, `descripcion`, `porcentaje_deduccion`, `estado`) VALUES
('Seguro Social', 'Deducción por seguro social (CCSS)', 9.25, 1),
('Impuesto sobre la Renta', 'Deducción por impuesto sobre la renta', 10.00, 1),
('Pensión Complementaria', 'Deducción por pensión complementaria', 2.00, 1),
('Seguro de Vida', 'Deducción por seguro de vida', 1.50, 1),
('Ahorro Voluntario', 'Deducción por ahorro voluntario', 5.00, 1),
('Préstamo Personal', 'Deducción por préstamo personal', 8.00, 1),
('Otros Descuentos', 'Otros descuentos varios', 0.00, 1);

-- =========================================================
-- TABLA: sis_moneda
-- =========================================================

CREATE TABLE `sis_moneda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL COMMENT 'Código de la moneda (CRC, USD, EUR, etc.)',
  `descripcion` varchar(100) NOT NULL COMMENT 'Descripción de la moneda',
  `tipo_cambio` decimal(10,4) NOT NULL DEFAULT 1.0000 COMMENT 'Tipo de cambio respecto a CRC',
  `estado` int(11) NOT NULL DEFAULT 1 COMMENT 'Estado de la moneda',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_codigo_moneda` (`codigo`),
  KEY `sis_moneda_estado_fk` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Gestión de monedas y tipos de cambio';

INSERT INTO `sis_moneda` (`codigo`, `descripcion`, `tipo_cambio`, `estado`) VALUES
('CRC', 'Colón Costarricense', 1.0000, 1),
('USD', 'Dólar Estadounidense', 520.0000, 1),
('EUR', 'Euro', 560.0000, 1);

-- =========================================================
-- TABLA: cxp (Cuentas por Pagar)
-- =========================================================

CREATE TABLE `cxp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_cxp` varchar(50) NOT NULL COMMENT 'Número único de CxP',
  `tipo_cxp` int(11) NOT NULL COMMENT 'Tipo de CxP',
  `beneficiario` varchar(200) NOT NULL COMMENT 'Nombre del beneficiario',
  `numero_cuenta` varchar(50) DEFAULT NULL COMMENT 'Número de cuenta bancaria',
  `moneda` varchar(10) DEFAULT 'CRC' COMMENT 'Moneda de la CxP (CRC, USD, etc.)',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_vencimiento` date DEFAULT NULL COMMENT 'Fecha límite de pago',
  `monto_total` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto total de la CxP',
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto ya pagado',
  `observaciones` text DEFAULT NULL COMMENT 'Observaciones adicionales',
  `estado` int(11) NOT NULL DEFAULT 1 COMMENT 'Estado de la CxP',
  `usuario_creacion` int(11) NOT NULL COMMENT 'Usuario que creó la CxP',
  `usuario_aprobacion` int(11) DEFAULT NULL COMMENT 'Usuario que aprobó/rechazó la CxP',
  `fecha_aprobacion` datetime DEFAULT NULL COMMENT 'Fecha de aprobación/rechazo',
  `gasto` int(11) DEFAULT NULL COMMENT 'Gasto asociado al pago de la CxP',
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_numero_cxp` (`numero_cxp`),
  KEY `cxp_tipo_fk` (`tipo_cxp`),
  KEY `cxp_estado_fk` (`estado`),
  KEY `cxp_usuario_creacion_fk` (`usuario_creacion`),
  KEY `cxp_usuario_aprobacion_fk` (`usuario_aprobacion`),
  KEY `cxp_gasto_fk` (`gasto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Cuentas por Pagar';

-- =========================================================
-- TABLA: cxp_detalle
-- =========================================================

CREATE TABLE `cxp_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cxp` int(11) NOT NULL COMMENT 'CxP asociada',
  `descripcion` varchar(500) NOT NULL COMMENT 'Descripción del concepto a pagar',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto del detalle',
  `cantidad` decimal(10,2) DEFAULT 1.00 COMMENT 'Cantidad (opcional)',
  `precio_unitario` decimal(12,2) DEFAULT NULL COMMENT 'Precio unitario (opcional)',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cxp_detalle_cxp_fk` (`cxp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Detalles de Cuentas por Pagar';

-- =========================================================
-- TABLA: cxp_deduccion
-- =========================================================

CREATE TABLE `cxp_deduccion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cxp` int(11) NOT NULL COMMENT 'CxP asociada',
  `rubro_deduccion` int(11) NOT NULL COMMENT 'Rubro de deducción aplicado',
  `monto_base` decimal(12,2) NOT NULL COMMENT 'Monto base sobre el cual se calcula la deducción',
  `porcentaje` decimal(5,2) NOT NULL COMMENT 'Porcentaje aplicado',
  `monto_deduccion` decimal(12,2) NOT NULL COMMENT 'Monto deducido',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cxp_deduccion_cxp_fk` (`cxp`),
  KEY `cxp_deduccion_rubro_fk` (`rubro_deduccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Deducciones aplicadas a Cuentas por Pagar';

-- =========================================================
-- ÍNDICES Y AUTO_INCREMENT
-- =========================================================

ALTER TABLE `bitacora_inicio_sesion`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `bit_usuario_proyecto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bit_usuario_proyecto_proyecto_fk` (`proyecto`),
  ADD KEY `bit_usuario_proyecto_usuario_fk` (`usuario`),
  ADD KEY `bit_usuario_proyecto_usuario_reg_fk` (`usuario_registro`),
  ADD KEY `bit_usuario_proyecto_estado_fk` (`estado`),
  ADD KEY `bit_usuario_proyecto_usuario_autoriza_fk` (`usuario_autoriza`),
  ADD KEY `bit_usuario_proyecto_rubro_fk` (`rubro_extra_salario`),
  ADD KEY `bit_usuario_proyecto_linea_fk` (`linea_presupuesto`),
  ADD KEY `bitacora_cxp_fk` (`cxp`);

ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_estado_fk` (`estado`);

ALTER TABLE `cliente_fe_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_fe_info_cliente_fk` (`cliente_id`);

ALTER TABLE `gasto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gas_usuario_fk1` (`usuario`),
  ADD KEY `gas_proveedor_fk1` (`proveedor`);

ALTER TABLE `impuesto`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ingreso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ing_cliente_fk1` (`cliente`),
  ADD KEY `ingreso_fk01` (`estado`);

ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mnu_rol_fk01` (`rol`),
  ADD KEY `mnu_vista_fk01` (`vista`);

ALTER TABLE `panel_configuraciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pc_usuario_fk01` (`usuario`);

ALTER TABLE `proyecto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proyecto_cliente_fk` (`cliente`),
  ADD KEY `proyecto_usuario_enc_fk` (`usuario_encargado`),
  ADD KEY `proyecto_estado_fk` (`estado`);

ALTER TABLE `proyecto_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proyecto_usuario_proyecto_fk` (`proyecto`),
  ADD KEY `proyecto_usuario_usuario_fk` (`usuario`);

ALTER TABLE `proyecto_linea_presupuesto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proyecto_linea` (`proyecto`, `numero_linea`),
  ADD KEY `proyecto_linea_proyecto_fk` (`proyecto`);

ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rubro_extra_salario`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sis_clase`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sis_estado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sis_estado_fk01` (`clase`);

ALTER TABLE `sis_parametro`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sis_tipo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sis_tipo_fk01` (`clase`);

ALTER TABLE `sucursal`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usu_sucursal_fk1` (`sucursal`),
  ADD KEY `usu_rol_fk1` (`rol`),
  ADD KEY `usuario_fk01` (`estado`);

ALTER TABLE `vista`
  ADD PRIMARY KEY (`id`);

-- =========================================================
-- AUTO_INCREMENT
-- =========================================================

ALTER TABLE `bitacora_inicio_sesion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bit_usuario_proyecto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `cliente_fe_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `gasto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `impuesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ingreso`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2240;

ALTER TABLE `panel_configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

ALTER TABLE `proyecto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `proyecto_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `proyecto_linea_presupuesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `proveedor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `rol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `rubro_extra_salario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `rubro_deduccion_salario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `sis_clase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `sis_estado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

ALTER TABLE `sis_parametro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `sis_tipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `sucursal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

ALTER TABLE `vista`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

-- =========================================================
-- FOREIGN KEYS (Restricciones)
-- =========================================================

ALTER TABLE `bit_usuario_proyecto`
  ADD CONSTRAINT `bit_usuario_proyecto_proyecto_fk` FOREIGN KEY (`proyecto`) REFERENCES `proyecto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bit_usuario_proyecto_usuario_fk` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bit_usuario_proyecto_usuario_reg_fk` FOREIGN KEY (`usuario_registro`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bit_usuario_proyecto_estado_fk` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bit_usuario_proyecto_usuario_autoriza_fk` FOREIGN KEY (`usuario_autoriza`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `bit_usuario_proyecto_rubro_fk` FOREIGN KEY (`rubro_extra_salario`) REFERENCES `rubro_extra_salario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `bit_usuario_proyecto_linea_fk` FOREIGN KEY (`linea_presupuesto`) REFERENCES `proyecto_linea_presupuesto` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `bitacora_cxp_fk` FOREIGN KEY (`cxp`) REFERENCES `cxp` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_estado_fk` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `cliente_fe_info`
  ADD CONSTRAINT `cliente_fe_info_cliente_fk` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `gasto`
  ADD CONSTRAINT `gas_usuario_fk1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `gas_proveedor_fk1` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `ingreso`
  ADD CONSTRAINT `ingreso_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `menu`
  ADD CONSTRAINT `mnu_rol_fk01` FOREIGN KEY (`rol`) REFERENCES `rol` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `mnu_vista_fk01` FOREIGN KEY (`vista`) REFERENCES `vista` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `panel_configuraciones`
  ADD CONSTRAINT `pc_usuario_fk01` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `sis_estado`
  ADD CONSTRAINT `sis_estado_fk01` FOREIGN KEY (`clase`) REFERENCES `sis_clase` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `sis_tipo`
  ADD CONSTRAINT `sis_tipo_fk01` FOREIGN KEY (`clase`) REFERENCES `sis_clase` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `proyecto`
  ADD CONSTRAINT `proyecto_cliente_fk` FOREIGN KEY (`cliente`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proyecto_usuario_enc_fk` FOREIGN KEY (`usuario_encargado`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proyecto_estado_fk` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `proyecto_usuario`
  ADD CONSTRAINT `proyecto_usuario_proyecto_fk` FOREIGN KEY (`proyecto`) REFERENCES `proyecto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proyecto_usuario_usuario_fk` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `proyecto_linea_presupuesto`
  ADD CONSTRAINT `proyecto_linea_proyecto_fk` FOREIGN KEY (`proyecto`) REFERENCES `proyecto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `usuario`
  ADD CONSTRAINT `usu_sucursal_fk1` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `usu_rol_fk1` FOREIGN KEY (`rol`) REFERENCES `rol` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `usuario_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `rubro_deduccion_salario`
  ADD CONSTRAINT `rubro_deduccion_salario_estado_fk` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `sis_moneda`
  ADD CONSTRAINT `sis_moneda_estado_fk` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `cxp`
  ADD CONSTRAINT `cxp_tipo_fk` FOREIGN KEY (`tipo_cxp`) REFERENCES `sis_tipo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cxp_estado_fk` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cxp_usuario_creacion_fk` FOREIGN KEY (`usuario_creacion`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cxp_usuario_aprobacion_fk` FOREIGN KEY (`usuario_aprobacion`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `cxp_gasto_fk` FOREIGN KEY (`gasto`) REFERENCES `gasto` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `cxp_detalle`
  ADD CONSTRAINT `cxp_detalle_cxp_fk` FOREIGN KEY (`cxp`) REFERENCES `cxp` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `cxp_deduccion`
  ADD CONSTRAINT `cxp_deduccion_cxp_fk` FOREIGN KEY (`cxp`) REFERENCES `cxp` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `cxp_deduccion_rubro_fk` FOREIGN KEY (`rubro_deduccion`) REFERENCES `rubro_deduccion_salario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
