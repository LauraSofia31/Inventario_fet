-- ============================================
-- SISTEMA DE INVENTARIO FET
-- Fundación Escuela Tecnológica de Neiva
-- ============================================

CREATE DATABASE IF NOT EXISTS inventario_fet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventario_fet;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','bodeguero') NOT NULL DEFAULT 'bodeguero',
    pregunta_seguridad VARCHAR(100),
    respuesta_seguridad VARCHAR(150),
    activo TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de categorías
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de proveedores
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    contacto VARCHAR(100),
    telefono VARCHAR(20),
    correo VARCHAR(150),
    direccion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    categoria_id INT,
    proveedor_id INT,
    stock_actual INT DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    precio_unitario DECIMAL(10,2) DEFAULT 0.00,
    unidad_medida VARCHAR(50) DEFAULT 'unidad',
    activo TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL
);

-- Tabla de movimientos (entradas y salidas)
CREATE TABLE movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    tipo ENUM('entrada','salida') NOT NULL,
    cantidad INT NOT NULL,
    motivo VARCHAR(255),
    usuario_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de devoluciones
CREATE TABLE devoluciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    motivo TEXT NOT NULL,
    tipo ENUM('entrada','salida') NOT NULL DEFAULT 'entrada',
    usuario_id INT NOT NULL,
    estado ENUM('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Datos iniciales
INSERT INTO categorias (nombre, descripcion) VALUES
('Papelería', 'Útiles de oficina y papelería'),
('Equipos', 'Equipos electrónicos y tecnológicos'),
('Limpieza', 'Productos de aseo y limpieza'),
('Mobiliario', 'Muebles y enseres'),
('Herramientas', 'Herramientas y equipos de mantenimiento');

INSERT INTO proveedores (nombre, contacto, telefono, correo) VALUES
('Papelería La Estrella', 'Carlos Rodríguez', '3001234567', 'ventas@estrella.com'),
('Tecnología y Más', 'Ana López', '3109876543', 'info@tecmas.com'),
('Distribuidora Aseo', 'Pedro Gómez', '3157654321', 'pedidos@aseo.com');

-- Usuario admin por defecto (password: Admin2024*)
-- Hash generado con password_hash('Admin2024*', PASSWORD_DEFAULT)
INSERT INTO usuarios (nombre, correo, password, rol, pregunta_seguridad, respuesta_seguridad) VALUES
('Administrador FET', 'admin@fet.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '¿Nombre de tu primera mascota?', 'felix');

-- Nota: El hash de arriba es para la contraseña 'password'
-- Para producción genera tu propio hash con:
-- php -r "echo password_hash('TuContraseña', PASSWORD_DEFAULT);"
