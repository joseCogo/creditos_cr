SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

SET FOREIGN_KEY_CHECKS = 0;

USE `bcoyhnvaiydt6al37nzh`;

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `fecha_registro` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `clientes` (`id`, `nombre`, `cedula`, `telefono`, `direccion`, `correo`, `fecha_registro`) VALUES
(3, 'camilo vargas', '45651238', '3124568997', 'carrera 24 # 21-30', 'camilo@gmail.com', '2025-11-19');

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prestamo_id` int(11) NOT NULL,
  `fecha_pago` date NOT NULL,
  `monto_pagado` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','transferencia') DEFAULT 'efectivo',
  `observacion` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prestamo_id` (`prestamo_id`),
  KEY `fk_usuario_pago` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pagos` (`id`, `prestamo_id`, `fecha_pago`, `monto_pagado`, `metodo_pago`, `observacion`, `usuario_id`) VALUES
(1, 1, '2025-11-22', 20000.00, 'efectivo', '', 3),
(2, 1, '2025-11-22', 50000.00, 'efectivo', '', 3),
(3, 1, '2025-11-22', 20000.00, 'efectivo', '', 3),
(4, 1, '2025-11-22', 20000.00, 'efectivo', '', 3),
(5, 1, '2025-11-22', 20000.00, 'efectivo', '', 3),
(6, 1, '2025-11-22', 20000.00, 'efectivo', '', 4),
(7, 1, '2025-11-23', 20000.00, 'efectivo', '', 4),
(8, 1, '2025-11-23', 30000.00, 'efectivo', '', 3);

DROP TABLE IF EXISTS `prestamos`;
CREATE TABLE IF NOT EXISTS `prestamos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `interes` decimal(5,2) NOT NULL,
  `cuotas` int(11) NOT NULL,
  `cuota_diaria` decimal(10,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('activo','cancelado','moroso') DEFAULT 'activo',
  `monto_total` decimal(10,2) DEFAULT 0.00,
  `saldo_pendiente` decimal(10,2) DEFAULT 0.00,
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `prestamos` (`id`, `cliente_id`, `monto`, `interes`, `cuotas`, `cuota_diaria`, `fecha_inicio`, `fecha_fin`, `estado`, `monto_total`, `saldo_pendiente`, `usuario_id`) VALUES
(1, 3, 500000.00, 20.00, 30, 20000.00, '2025-11-23', '2025-12-23', 'activo', 600000.00, 400000.00, 3);

DROP TABLE IF EXISTS `reportes`;
CREATE TABLE IF NOT EXISTS `reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `total_prestado` decimal(10,2) DEFAULT NULL,
  `total_recuperado` decimal(10,2) DEFAULT NULL,
  `clientes_morosos` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `correo` varchar(100) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `rol` enum('admin','empleado') DEFAULT 'empleado',
  `token_recuperacion` varchar(64) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` (`id`, `correo`, `clave`, `nombre`, `rol`, `token_recuperacion`, `token_expira`) VALUES
(1, 'juan@gmail.com', '$2y$10$yiUw7GnEkAigfDasGlBnkONUPoRkpAzGGiGRIHQ9Cnty7ZjQnbpSq', 'juan', 'empleado', NULL, NULL),
(2, 'jco@gmail.com', '$2y$10$UrfTUtr9M/HRtPg2oD9oteouYef1cW3npi0bdhdD9N/6PJBipog5O', 'jose', 'empleado', NULL, NULL),
(3, 'carlos.ricardoreiino@gmail.com', '$2y$10$fnHvqcCCbJIJQqFpu6eJV.Uahds2z3cLS.5EG/c6R7/PbHwyd9x7.', 'Carlos', 'admin', NULL, NULL),
(4, 'david@gmail.com', '$2y$10$Ctd.xmRTMj5YziqGCU13IeMJoi3A8PZB7afLA8wQhtHosnNNXngda', 'David Manuel', 'empleado', NULL, NULL);

ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_usuario_pago` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`prestamo_id`) REFERENCES `prestamos` (`id`);

ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

SET FOREIGN_KEY_CHECKS = 1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;