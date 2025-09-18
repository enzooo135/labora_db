-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-09-2025 a las 13:27:29
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
(2, 'jose', '$2y$10$G5AhOTkmX2h9QRXFpW0ukeqD9m0q20yoKkTWjMf9B2FtcAL8SpllC'),
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
(5, 40, 'tecnico en informatica personal y profesional', 'Escuela Tecnica Numero 5', '2022-03-20', '2025-03-20'),
(6, 39, 'tecnico en informatica personal y profesional', 'Escuela Tecnica Numero 5', '2022-03-22', '2025-11-24');

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
(39, 'enzo santino mamani cuba', 'enzosantinomamanicuba@gmail.com', '$2y$10$olnPTJwRLHYiQLVuNIh.AOJTUOK2rB.6TX/w0hJgCiozbeYeB09pi', 'carpinteria', 5, 'soy buenisimo en todo lo que hago', 'Full time', 2500.00, 'Merlo', '47161648', '2006-01-26', 'Argentina', '1164718626', NULL, 'Resolución de problemas, Licencia de conducir', NULL, NULL, '', 'pf_68c9ae637f5350.62357190.jpg', '', NULL, NULL, 'aprobado', 1, '2025-09-15 15:40:02', '', 'uploads/verificaciones/empleado_39/doc_68c85385c05480.74623229.jpg', 'uploads/verificaciones/empleado_39/doc_68c85385c0a027.98148134.jpg', 'uploads/verificaciones/empleado_39/doc_68c85385c0d2d5.10723924.jpg'),
(40, 'Mario Gabriel Mamani', 'santinomam@gmail.com', '$2y$10$WuYWoxIyu5r65uxjnfF/6eERGyNWqmWJ8mcBMzePw7EjeU6TfX3PC', 'cerrajero', 5, 'Soy excelente carpintero', 'Part time', 3500.00, 'Merlo', '23211489', '2006-01-26', 'Argentina', '1164718626', NULL, 'Comunicación, Herramientas propias', NULL, NULL, '', 'pf_68c9a069d03b80.44058042.jpg', '', NULL, NULL, 'pendiente', NULL, NULL, NULL, 'uploads/verificaciones/empleado_40/doc_68c862c9788142.72084822.jpg', 'uploads/verificaciones/empleado_40/doc_68c862c978e302.07821426.jpg', 'uploads/verificaciones/empleado_40/doc_68c862c9793182.18876329.jpg');

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
(5, 40, 'Encargado', 'Coca Cola', '1133029014', '2022-10-20', '2025-10-20', 'Estuve encargado de bastante gente'),
(6, 39, 'encargado', 'coca cola', '1131232012', '2022-01-22', '2023-02-22', 'estaba encargado de todo tipo de servicios.');

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
  `matricula_path` varchar(255) DEFAULT NULL,
  `zona_busqueda` varchar(120) DEFAULT NULL,
  `rubros_interes` varchar(255) DEFAULT NULL,
  `presupuesto_max` decimal(10,2) DEFAULT NULL,
  `medio_contacto` enum('telefono','whatsapp','email') DEFAULT 'whatsapp',
  `horario_contacto` varchar(120) DEFAULT NULL,
  `descripcion_usuario` text DEFAULT NULL,
  `foto_perfil_usuario` varchar(255) DEFAULT NULL,
  `visibilidad` enum('publico','oculto') DEFAULT 'publico'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `correo`, `clave`, `nombre`, `telefono`, `fecha_registro`, `tipo_usuario`, `dni`, `direccion`, `fecha_nacimiento`, `localidad`, `reset_token_hash`, `reset_expires`, `estado_verificacion`, `verificado_por`, `fecha_verificacion`, `observaciones_verificacion`, `dni_frente_path`, `dni_dorso_path`, `matricula_path`, `zona_busqueda`, `rubros_interes`, `presupuesto_max`, `medio_contacto`, `horario_contacto`, `descripcion_usuario`, `foto_perfil_usuario`, `visibilidad`) VALUES
(12, 'santinomam@gmail.com', '$2y$10$1l0LISEPWpODmPPYHni3F.Xc4A.FkrkjNC9PEShkZotRkT4MQOuv6', 'Mario Gabriel Mamani', '1164718626', '2025-09-15 14:56:06', '', '23211489', 'constitucion 858', '1979-06-29', 'Merlo', NULL, NULL, 'aprobado', 1, '2025-09-15 15:39:15', '', 'uploads/verificaciones/usuario_12/doc_68c8532b88ada2.60790696.jpg', 'uploads/verificaciones/usuario_12/doc_68c8532b890c36.92471876.jpg', 'uploads/verificaciones/usuario_12/doc_68c8532b895883.55976684.jpg', 'Moreno', 'Carpinteria', 25000.00, 'whatsapp', 'Lun - Vie 11:00 hs - 12:00 hs', 'Busco carpintero mensualmente para proyectos, el presupuesto maximo se puede extender depende del desempeño.', 'u_68cae8ac65c151.85820626.jpg', 'publico'),
(13, 'enzosantinomamanicuba@gmail.com', '$2y$10$LIXZc6/V2rZqT5P0b0sEiOm7qJIslFQgeME0CFTAnwlw0KfzKUTsK', 'enzo santino mamani cuba', '1164718626', '2025-09-15 15:56:00', '', '47161648', 'constitucion 858', '2006-01-26', 'Merlo', NULL, NULL, 'pendiente', NULL, NULL, NULL, 'uploads/verificaciones/usuario_13/doc_68c86111113541.92507834.jpg', 'uploads/verificaciones/usuario_13/doc_68c8611111cc09.49066319.jpg', 'uploads/verificaciones/usuario_13/doc_68c86111121b30.41576349.jpg', NULL, NULL, NULL, 'whatsapp', NULL, NULL, NULL, 'publico');

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
-- Volcado de datos para la tabla `valoraciones`
--

INSERT INTO `valoraciones` (`id_valoracion`, `id_empleado`, `id_usuario`, `puntuacion`, `comentario`, `fecha`) VALUES
(7, 39, 12, 5, 'La verdad que el mejor trabajador que pude contratar', '2025-09-16 23:40:25'),
(8, 39, 13, 5, 'aaaa', '2025-09-18 16:15:41');

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
  MODIFY `id_educacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `experiencia_laboral`
--
ALTER TABLE `experiencia_laboral`
  MODIFY `id_experiencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id_valoracion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
