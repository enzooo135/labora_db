-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS labora_db;
USE labora_db;

-- Tabla de usuarios (información común para ambos tipos de usuarios)
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    telefono VARCHAR(20),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipo_usuario ENUM('empleador', 'empleado') NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    ultima_conexion DATETIME
);

-- Tabla para empleadores
CREATE TABLE empleadores (
    id_empleador INT PRIMARY KEY,
    empresa VARCHAR(100),
    rubro VARCHAR(50),
    direccion_empresa VARCHAR(200),
    sitio_web VARCHAR(200),
    descripcion_empresa TEXT,
    FOREIGN KEY (id_empleador) REFERENCES usuarios(id_usuario)
);

-- Tabla para empleados
CREATE TABLE empleados (
    id_empleado INT PRIMARY KEY,
    profesion VARCHAR(100) NOT NULL,
    experiencia_años INT,
    descripcion_servicios TEXT,
    disponibilidad ENUM('tiempo_completo', 'medio_tiempo', 'por_horas') NOT NULL,
    precio_hora DECIMAL(10,2),
    zona_trabajo VARCHAR(200),
    FOREIGN KEY (id_empleado) REFERENCES usuarios(id_usuario)
);

-- Tabla de habilidades
CREATE TABLE habilidades (
    id_habilidad INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    categoria VARCHAR(50)
);

-- Tabla de relación empleados-habilidades
CREATE TABLE empleado_habilidades (
    id_empleado INT,
    id_habilidad INT,
    nivel ENUM('básico', 'intermedio', 'avanzado', 'experto'),
    PRIMARY KEY (id_empleado, id_habilidad),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    FOREIGN KEY (id_habilidad) REFERENCES habilidades(id_habilidad)
);

-- Tabla de proyectos/trabajos
CREATE TABLE proyectos (
    id_proyecto INT PRIMARY KEY AUTO_INCREMENT,
    id_empleador INT,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    presupuesto DECIMAL(10,2),
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_limite DATE,
    estado ENUM('abierto', 'en_proceso', 'completado', 'cancelado') DEFAULT 'abierto',
    FOREIGN KEY (id_empleador) REFERENCES empleadores(id_empleador)
);

-- Tabla de postulaciones
CREATE TABLE postulaciones (
    id_postulacion INT PRIMARY KEY AUTO_INCREMENT,
    id_proyecto INT,
    id_empleado INT,
    fecha_postulacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    propuesta TEXT,
    precio_propuesto DECIMAL(10,2),
    estado ENUM('pendiente', 'aceptada', 'rechazada') DEFAULT 'pendiente',
    FOREIGN KEY (id_proyecto) REFERENCES proyectos(id_proyecto),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
);

-- Tabla de valoraciones
CREATE TABLE valoraciones (
    id_valoracion INT PRIMARY KEY AUTO_INCREMENT,
    id_proyecto INT,
    id_evaluador INT,
    id_evaluado INT,
    puntuacion INT CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT,
    fecha_valoracion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_proyecto) REFERENCES proyectos(id_proyecto),
    FOREIGN KEY (id_evaluador) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_evaluado) REFERENCES usuarios(id_usuario)
);

-- Tabla de mensajes
CREATE TABLE mensajes (
    id_mensaje INT PRIMARY KEY AUTO_INCREMENT,
    id_emisor INT,
    id_receptor INT,
    contenido TEXT NOT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_emisor) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_receptor) REFERENCES usuarios(id_usuario)
);

-- Insertar algunas habilidades predefinidas
INSERT INTO habilidades (nombre, categoria) VALUES
('Carpintería', 'Oficios'),
('Plomería', 'Oficios'),
('Electricidad', 'Oficios'),
('Jardinería', 'Oficios'),
('Pintura', 'Oficios'),
('Matemáticas', 'Educación'),
('Física', 'Educación'),
('Química', 'Educación'),
('Inglés', 'Idiomas'),
('Programación', 'Tecnología'); 