-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 19-11-2025 a las 16:58:31
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bd_hotel`
--
CREATE DATABASE IF NOT EXISTS `bd_hotel` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `bd_hotel`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

DROP TABLE IF EXISTS `actividades`;
CREATE TABLE IF NOT EXISTS `actividades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `entidad` varchar(50) DEFAULT NULL,
  `entidad_id` int DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

DROP TABLE IF EXISTS `habitaciones`;
CREATE TABLE IF NOT EXISTS `habitaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) NOT NULL,
  `tipo_id` int NOT NULL,
  `capacidad` int NOT NULL,
  `precio_noche` decimal(10,2) NOT NULL,
  `estado` enum('disponible','limpieza','mantenimiento','ocupado','reservado') NOT NULL DEFAULT 'disponible',
  PRIMARY KEY (`id`),
  KEY `tipo_id` (`tipo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id`, `numero`, `tipo_id`, `capacidad`, `precio_noche`, `estado`) VALUES
(1, '101', 1, 2, 840.00, 'ocupado'),
(2, '102', 1, 2, 840.00, 'ocupado'),
(3, '103', 1, 2, 840.00, 'disponible'),
(4, '104', 1, 2, 840.00, 'disponible'),
(5, '105', 1, 2, 840.00, 'disponible'),
(6, '106', 1, 2, 840.00, 'disponible'),
(7, '201', 3, 2, 990.00, 'disponible'),
(8, '202', 1, 2, 840.00, 'disponible'),
(9, '203', 1, 2, 840.00, 'disponible'),
(10, '204', 1, 2, 840.00, 'disponible'),
(11, '205', 1, 2, 840.00, 'disponible'),
(12, '206', 3, 2, 990.00, 'disponible'),
(13, '301', 1, 2, 840.00, 'disponible'),
(14, '302', 1, 2, 840.00, 'disponible'),
(15, '303', 1, 2, 840.00, 'disponible'),
(16, '304', 1, 2, 840.00, 'disponible'),
(17, '305', 1, 2, 840.00, 'disponible'),
(18, '306', 1, 2, 840.00, 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `huespedes`
--

DROP TABLE IF EXISTS `huespedes`;
CREATE TABLE IF NOT EXISTS `huespedes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `huespedes`
--

INSERT INTO `huespedes` (`id`, `nombre_completo`, `email`, `telefono`, `fecha_registro`) VALUES
(1, 'Abimael Méndez', 'abimael@example.com', '6691234567', '2025-11-15 04:06:10'),
(6, 'Sara', 'sara@gmail.com', '9821086734', '2025-11-18 21:14:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reserva_id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `estado` enum('Completado','Pendiente','Cancelado') DEFAULT 'Completado',
  `fecha_pago` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

DROP TABLE IF EXISTS `reservas`;
CREATE TABLE IF NOT EXISTS `reservas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `huesped_id` int NOT NULL,
  `habitacion_id` varchar(10) NOT NULL,
  `fecha_check_in` date NOT NULL,
  `fecha_check_out` date NOT NULL,
  `numero_huespedes` int NOT NULL,
  `personas_extra` int NOT NULL DEFAULT '0',
  `estado` varchar(20) NOT NULL DEFAULT 'Reservada',
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `tipo_ocupacion` varchar(20) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `descuento_aplicado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_habitacion`
--

DROP TABLE IF EXISTS `tipos_habitacion`;
CREATE TABLE IF NOT EXISTS `tipos_habitacion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `tipos_habitacion`
--

INSERT INTO `tipos_habitacion` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Habitación Sencilla', 'Una habitación estándar para una persona.'),
(2, 'Habitación Doble', 'Una habitación estándar para dos personas.'),
(3, 'King Size', 'Una habitación más grande con una cama King Size.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_precios`
--

DROP TABLE IF EXISTS `tipos_precios`;
CREATE TABLE IF NOT EXISTS `tipos_precios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_id` int NOT NULL,
  `ocupacion` enum('sencilla','doble','king') DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_id` (`tipo_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `tipos_precios`
--

INSERT INTO `tipos_precios` (`id`, `tipo_id`, `ocupacion`, `precio`) VALUES
(6, 1, 'sencilla', 840.00),
(7, 1, 'doble', 890.00),
(8, 3, 'king', 990.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','personal') NOT NULL DEFAULT 'personal',
  `avatar_base64` longtext,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password_hash`, `rol`, `avatar_base64`, `fecha_creacion`) VALUES
(1, 'admin', 'admin@rp.com', '$2y$10$rCJWYgBsXsxhAfPJabl3LO8Kmh7wWySWgAVuerOSEedvMxXlLVLNm', 'admin', NULL, '2025-11-13 18:49:50'),
(4, 'personal', 'personal@rp.com', '$2y$10$s.PMQPqXLtXAdTkxV.r12.30f1D/Tx3KNRy.vZyEGDQLacpPW502K', 'personal', NULL, '2025-11-16 18:20:47');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD CONSTRAINT `habitaciones_ibfk_1` FOREIGN KEY (`tipo_id`) REFERENCES `tipos_habitacion` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
