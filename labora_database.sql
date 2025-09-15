-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-09-2025 a las 21:21:39
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
(1, 'enzo', '$2y$10$JUIPX0fd4WED0np9tiqsOu/lVp3aerHRUWcnUiLT.kbjC1LLfGDMq'),
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
  `reset_expires` datetime DEFAULT NULL,
  `estado_verificacion` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  `verificado_por` int(11) DEFAULT NULL,
  `fecha_verificacion` datetime DEFAULT NULL,
  `observaciones_verificacion` text DEFAULT NULL,
  `dni_frente_path` varchar(255) DEFAULT NULL,
  `dni_dorso_path` varchar(255) DEFAULT NULL,
  `matricula_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`id_empleado`, `nombre`, `correo`, `clave`, `profesion`, `experiencia_años`, `descripcion_servicios`, `disponibilidad`, `precio_hora`, `zona_trabajo`, `dni`, `fecha_nacimiento`, `nacionalidad`, `telefono`, `titulo_profesional`, `habilidades`, `educacion`, `experiencia`, `portafolio`, `foto_perfil`, `portafolio_link`, `reset_token_hash`, `reset_expires`, `estado_verificacion`, `verificado_por`, `fecha_verificacion`, `observaciones_verificacion`, `dni_frente_path`, `dni_dorso_path`, `matricula_path`) VALUES
(39, 'enzo santino mamani cuba', 'enzosantinomamanicuba@gmail.com', '$2y$10$olnPTJwRLHYiQLVuNIh.AOJTUOK2rB.6TX/w0hJgCiozbeYeB09pi', 'carpinteria', 5, NULL, NULL, NULL, 'Merlo', '47161648', '2006-01-26', 'Argentina', '1164718626', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'aprobado', 1, '2025-09-15 15:40:02', '', 'uploads/verificaciones/empleado_39/doc_68c85385c05480.74623229.jpg', 'uploads/verificaciones/empleado_39/doc_68c85385c0a027.98148134.jpg', 'uploads/verificaciones/empleado_39/doc_68c85385c0d2d5.10723924.jpg'),
(40, 'Mario Gabriel Mamani', 'santinomam@gmail.com', '$2y$10$WuYWoxIyu5r65uxjnfF/6eERGyNWqmWJ8mcBMzePw7EjeU6TfX3PC', 'cerrajero', 5, NULL, NULL, NULL, 'Merlo', '23211489', '2006-01-26', 'Argentina', '1164718626', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pendiente', NULL, NULL, NULL, 'uploads/verificaciones/empleado_40/doc_68c862c9788142.72084822.jpg', 'uploads/verificaciones/empleado_40/doc_68c862c978e302.07821426.jpg', 'uploads/verificaciones/empleado_40/doc_68c862c9793182.18876329.jpg');

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
  `creado_en` datetime DEFAULT current_timestamp(),
  `dni_frente_tmp` varchar(255) DEFAULT NULL,
  `dni_dorso_tmp` varchar(255) DEFAULT NULL,
  `matricula_tmp` varchar(255) DEFAULT NULL
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
  `localidad` varchar(250) DEFAULT NULL,
  `dni_frente_tmp` varchar(255) DEFAULT NULL,
  `dni_dorso_tmp` varchar(255) DEFAULT NULL,
  `matricula_tmp` varchar(255) DEFAULT NULL
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
  `reset_expires` datetime DEFAULT NULL,
  `estado_verificacion` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  `verificado_por` int(11) DEFAULT NULL,
  `fecha_verificacion` datetime DEFAULT NULL,
  `observaciones_verificacion` text DEFAULT NULL,
  `dni_frente_path` varchar(255) DEFAULT NULL,
  `dni_dorso_path` varchar(255) DEFAULT NULL,
  `matricula_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `correo`, `clave`, `nombre`, `telefono`, `fecha_registro`, `tipo_usuario`, `dni`, `direccion`, `fecha_nacimiento`, `localidad`, `reset_token_hash`, `reset_expires`, `estado_verificacion`, `verificado_por`, `fecha_verificacion`, `observaciones_verificacion`, `dni_frente_path`, `dni_dorso_path`, `matricula_path`) VALUES
(12, 'santinomam@gmail.com', '$2y$10$1l0LISEPWpODmPPYHni3F.Xc4A.FkrkjNC9PEShkZotRkT4MQOuv6', 'Mario Gabriel Mamani', '1164718626', '2025-09-15 14:56:06', '', '23211489', 'constitucion 858', '1979-06-29', 'Merlo', NULL, NULL, 'aprobado', 1, '2025-09-15 15:39:15', '', 'uploads/verificaciones/usuario_12/doc_68c8532b88ada2.60790696.jpg', 'uploads/verificaciones/usuario_12/doc_68c8532b890c36.92471876.jpg', 'uploads/verificaciones/usuario_12/doc_68c8532b895883.55976684.jpg'),
(13, 'enzosantinomamanicuba@gmail.com', '$2y$10$LIXZc6/V2rZqT5P0b0sEiOm7qJIslFQgeME0CFTAnwlw0KfzKUTsK', 'enzo santino mamani cuba', '1164718626', '2025-09-15 15:56:00', '', '47161648', 'constitucion 858', '2006-01-26', 'Merlo', NULL, NULL, 'pendiente', NULL, NULL, NULL, 'uploads/verificaciones/usuario_13/doc_68c86111113541.92507834.jpg', 'uploads/verificaciones/usuario_13/doc_68c8611111cc09.49066319.jpg', 'uploads/verificaciones/usuario_13/doc_68c86111121b30.41576349.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valoraciones`
--

CREATE TABLE `valoraciones` (
  `id_valoracion` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `puntuacion` tinyint(4) NOT NULL CHECK (`puntuacion` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD KEY `reset_token_hash` (`reset_token_hash`),
  ADD KEY `idx_empleado_estado` (`estado_verificacion`);

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
  ADD KEY `reset_token_hash` (`reset_token_hash`),
  ADD KEY `idx_usuarios_estado` (`estado_verificacion`),
  ADD KEY `fk_usuarios_verificador` (`verificado_por`);

--
-- Indices de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD PRIMARY KEY (`id_valoracion`),
  ADD KEY `id_empleado` (`id_empleado`),
  ADD KEY `id_usuario` (`id_usuario`);

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
  MODIFY `id_educacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `experiencia_laboral`
--
ALTER TABLE `experiencia_laboral`
  MODIFY `id_experiencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `registro_pendiente_empleados`
--
ALTER TABLE `registro_pendiente_empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `registro_pendiente_usuarios`
--
ALTER TABLE `registro_pendiente_usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  MODIFY `id_valoracion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_verificador` FOREIGN KEY (`verificado_por`) REFERENCES `administradores` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD CONSTRAINT `valoraciones_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`),
  ADD CONSTRAINT `valoraciones_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
