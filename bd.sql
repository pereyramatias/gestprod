-- Base de datos: `gestprod`
CREATE DATABASE IF NOT EXISTS `gestprod` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gestprod`;

-- Tabla: `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar un usuario de prueba
-- Contraseña: password123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Admin User', 'admin@gestprod.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');


-- Tabla: `clientes`
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cuit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_fantasia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_cliente` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `localidad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provincia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto_nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto_puesto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuit` (`cuit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar clientes de ejemplo
INSERT INTO `clientes` (`cuit`, `razon_social`, `nombre_fantasia`, `tipo_cliente`, `email`, `telefono`, `direccion`, `localidad`, `provincia`, `contacto_nombre`, `contacto_puesto`) VALUES
('30-12345678-9', 'AGRICOLA SANTA ANA S.A.', 'Santa Ana Agro', 'ESPECIAL', 'ventas@santaana.com.ar', '+54 11 1234-5678', 'Ruta 8 KM 125', 'Pergamino', 'Buenos Aires', 'Carlos López', 'Gerente Comercial'),
('30-98765432-1', 'AGROS DEL SUR S.A.', 'Agros del Sur', 'REGULAR', 'compras@agrosdelsur.com.ar', '+54 341 8765-4321', 'Av. San Martin 500', 'Rosario', 'Santa Fe', 'Maria Rodriguez', 'Jefa de Compras');


-- Tabla: `contratos`
CREATE TABLE `contratos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `fecha_contrato` date NOT NULL,
  `producto` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cosecha` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `toneladas` decimal(10,2) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `lugar_entrega` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_entrega` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `año` int(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar contratos de ejemplo
INSERT INTO `contratos` (`cliente_id`, `fecha_contrato`, `producto`, `cosecha`, `toneladas`, `precio`, `lugar_entrega`, `fecha_entrega`, `año`) VALUES
(2, '2025-10-12', 'Maiz duro', '2024-25', 2000.00, 350.00, 'Rosario', 'Marzo - Abril', 2025),
(1, '2025-11-05', 'Soja', '2024-25', 1500.00, 520.00, 'Quequén', 'Mayo', 2025);
