-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-08-2025 a las 20:23:48
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `labora_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id_admin` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `clave` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id_admin`, `usuario`, `clave`) VALUES
(1, 'enzo', '1234'),
(2, 'jose', '1234'),
(3, 'alan', '1234'),
(4, 'santiago', '1234');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `educacion`
--

CREATE TABLE `educacion` (
  `id_educacion` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `institucion` varchar(255) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `educacion`
--

INSERT INTO `educacion` (`id_educacion`, `id_empleado`, `titulo`, `institucion`, `fecha_inicio`, `fecha_fin`) VALUES
(1, 29, 'no', 'Escuela Tecnica número 5', '2022-03-07', '2025-11-23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `id_empleado` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `profesion` varchar(100) DEFAULT NULL,
  `experiencia_años` int(11) DEFAULT NULL,
  `descripcion_servicios` text DEFAULT NULL,
  `disponibilidad` varchar(100) DEFAULT NULL,
  `precio_hora` decimal(10,2) DEFAULT NULL,
  `zona_trabajo` varchar(100) DEFAULT NULL,
  `dni` varchar(25) DEFAULT NULL,
  `fecha_nacimiento` varchar(25) DEFAULT NULL,
  `nacionalidad` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `titulo_profesional` varchar(255) DEFAULT NULL,
  `habilidades` varchar(255) DEFAULT NULL,
  `educacion` varchar(255) DEFAULT NULL,
  `experiencia` varchar(255) DEFAULT NULL,
  `portafolio` varchar(255) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `portafolio_link` varchar(255) DEFAULT NULL,
  `reset_token_hash` char(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`id_empleado`, `nombre`, `correo`, `clave`, `profesion`, `experiencia_años`, `descripcion_servicios`, `disponibilidad`, `precio_hora`, `zona_trabajo`, `dni`, `fecha_nacimiento`, `nacionalidad`, `telefono`, `titulo_profesional`, `habilidades`, `educacion`, `experiencia`, `portafolio`, `foto_perfil`, `portafolio_link`, `reset_token_hash`, `reset_expires`) VALUES
(19, 'Francisco tortelli', 'salchichamancpm@gmail.com', '$2y$10$sereWJRy/ETz/Rx79wqTO.zHrZ0C75ifdr4GQn6DPTWXDAsLZNIP.', 'maquina del mal, actor doble de riesgo', 18, 'Fachero', 'Full time', 10000.00, 'Merlo, Libertad', '48170252', '2007-06-02', 'Bolivia', '1130408554', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'Lautaro German Ramirez', 'lauuramirez777@gmail.com', '$2y$10$1.23UiJAsBCS.Xc1tseWIOkL.pNqvB6sRsdnYJs635GM/zH6KfQg.', 'Educación', 5, 'Soy excelente', 'Full time', 3000.00, 'Merlo, el parque', '23211489', '2006-01-26', 'Argentina', '1164718626', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 'Enzo Santino ', 'santinomamanicuba@gmail.com', '$2y$10$9RhqQ1rUofSQI7gYtb.hqO5rXfFpj1s.50xlLbw3HYzx5E/q0DKTm', '', 5, '', '', 0.00, 'COSTA', '47161648', '2006-01-26', 'Argentina', '1164718626', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 'Enzo Santino ', 'santinomam@gmail.com', '$2y$10$kLpyHfR6s0.ja9EsqcVN4ea3.vjH6kePSxapONSEkPsz6Brn6pB/6', 'Estudiante', 6, 'soy un estudiante de escuela secundaria tecnia', 'Full time', 350.00, 'Merlo, el parque', '47161648', '2006-01-26', 'Argentina', '1164718626', 'Desarrollador full stack', 'Programador', 'Escuela secundaria tecnica numero 5 ', 'lalalala', 'Enzo portafolio', '68af781222983.jpg', 'EnzosPortafolio.com', 'ee33b86e03cd8b7adea4ab757eaf082cbdad84565ff5126aaa58ea75fd7afd86', '2025-08-28 16:51:02'),
(32, 'Enzo Santino', 'labora1357@gmail.com', '$2y$10$xMROqT/4ES1F/EvhSJtWe.9ZBsS/1yUCGtjqOL2FOOGwR32J4tvai', 'educacion', 5, NULL, NULL, NULL, 'Merlo', '47161648', '2006-01-26', 'Argentina', '1164718626', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 'Enzo Santino', 'enzosantinomamanicuba@gmail.com', '$2y$10$bv3HS2Zqyxmwq0sso9po0eGn4v1XkKRMwykzEs2l/YkGKQkZPiVJm', 'plomeria', 4, NULL, NULL, NULL, 'Merlo', '47161684', '2006-01-26', 'Argentina', '1164718626', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `experiencia_laboral`
--

CREATE TABLE `experiencia_laboral` (
  `id_experiencia` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `puesto` varchar(100) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `contacto_referencia` varchar(100) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `experiencia_laboral`
--

INSERT INTO `experiencia_laboral` (`id_experiencia`, `id_empleado`, `puesto`, `empresa`, `contacto_referencia`, `fecha_inicio`, `fecha_fin`, `descripcion`) VALUES
(1, 29, 'Gerente', 'Coca Cola', '11 64718626', '2006-01-26', '2006-01-27', 'Trabaja como gerente de Coca Cola en el turno noche y cargaba camiones con las manos.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_pendiente_empleados`
--

CREATE TABLE `registro_pendiente_empleados` (
  `id_empleado` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `clave` varchar(255) DEFAULT NULL,
  `profesion` text DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `nacionalidad` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `zona_trabajo` varchar(100) DEFAULT NULL,
  `experiencia_años` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_pendiente_usuarios`
--

CREATE TABLE `registro_pendiente_usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `clave` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `direccion` varchar(250) DEFAULT NULL,
  `dni` varchar(50) DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `localidad` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `correo` varchar(200) DEFAULT NULL,
  `clave` varchar(250) DEFAULT NULL,
  `nombre` varchar(50) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `tipo_usuario` enum('','','','') NOT NULL,
  `dni` varchar(25) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` varchar(30) DEFAULT NULL,
  `localidad` varchar(250) DEFAULT NULL,
  `reset_token_hash` char(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `correo`, `clave`, `nombre`, `telefono`, `fecha_registro`, `tipo_usuario`, `dni`, `direccion`, `fecha_nacimiento`, `localidad`, `reset_token_hash`, `reset_expires`) VALUES
(7, 'santinomam@gmail.com', '$2y$10$ASCsNW2CbjWDX9AYk8FbXuByWnwi9dWcRE.CALwoJGOh3yn/dGfpy', 'Enzo Santino ', '01164718626', '2025-07-24 18:18:43', '', '47161648', 'Constitución 858', '2006-01-26', NULL, 'cafac5b2f1aabfb1afd54c3f4a4956dda4064757358bdff5bede91a644b67555', '2025-08-28 14:57:41'),
(10, 'enzosantinomamanicuba@gmail.com', '$2y$10$1mxjFRTXXduQPDeIZM7iDOf9ybhIrZwqwMtl1WLE62F7p6XQ/UF6a', 'Enzo Santino ', '1164718626', '2025-08-27 20:20:04', '', '47161648', 'Constitución 858', '2006-01-26', 'Merlo', 'e381cd84e1f43d0504667bb4eef483c81d63979ac2034301318185c4f4bb793b', '2025-08-28 21:18:19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `educacion`
--
ALTER TABLE `educacion`
  ADD PRIMARY KEY (`id_educacion`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `reset_token_hash` (`reset_token_hash`);

--
-- Indices de la tabla `experiencia_laboral`
--
ALTER TABLE `experiencia_laboral`
  ADD PRIMARY KEY (`id_experiencia`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indices de la tabla `registro_pendiente_empleados`
--
ALTER TABLE `registro_pendiente_empleados`
  ADD PRIMARY KEY (`id_empleado`);

--
-- Indices de la tabla `registro_pendiente_usuarios`
--
ALTER TABLE `registro_pendiente_usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`correo`),
  ADD KEY `reset_token_hash` (`reset_token_hash`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `educacion`
--
ALTER TABLE `educacion`
  MODIFY `id_educacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `experiencia_laboral`
--
ALTER TABLE `experiencia_laboral`
  MODIFY `id_experiencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `registro_pendiente_empleados`
--
ALTER TABLE `registro_pendiente_empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `registro_pendiente_usuarios`
--
ALTER TABLE `registro_pendiente_usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `educacion`
--
ALTER TABLE `educacion`
  ADD CONSTRAINT `educacion_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`) ON DELETE CASCADE;

--
-- Filtros para la tabla `experiencia_laboral`
--
ALTER TABLE `experiencia_laboral`
  ADD CONSTRAINT `experiencia_laboral_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
