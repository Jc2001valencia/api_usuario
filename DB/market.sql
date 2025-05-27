-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS market;
USE market;

-- Tabla: Rol
CREATE TABLE rol (
  id_rol INT(11) NOT NULL AUTO_INCREMENT,
  rol VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: Usuario
CREATE TABLE usuario (
  id_usuario INT(11) NOT NULL AUTO_INCREMENT,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  token VARCHAR(100) NOT NULL,
  id_rol INT(11) NOT NULL,
  PRIMARY KEY (id_usuario),
  FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: Ubicación
CREATE TABLE ubicacion (
  id_ubicacion INT(11) NOT NULL AUTO_INCREMENT,
  ubicacion VARCHAR(255) NOT NULL,
  token_ubicacion VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_ubicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: Vendedor
CREATE TABLE vendedor (
  id_vendedor INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  id_ubicacion INT(11),
  telefono VARCHAR(20),
  id_usu INT(11) NOT NULL,
  PRIMARY KEY (id_vendedor),
  UNIQUE KEY (id_usu),
  FOREIGN KEY (id_ubicacion) REFERENCES ubicacion(id_ubicacion),
  FOREIGN KEY (id_usu) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: Categoría
CREATE TABLE categoria (
  id_categoria INT(11) NOT NULL AUTO_INCREMENT,
  categoria VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: Estado
CREATE TABLE estado (
  id_estado INT(11) NOT NULL AUTO_INCREMENT,
  estado VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: Producto
CREATE TABLE producto (
  id_producto INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  precio DOUBLE NOT NULL,
  id_categoria INT(11),
  id_estado INT(11),
  id_vendedor INT(11),
  imagen VARCHAR(255),
  PRIMARY KEY (id_producto),
  FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE,
  FOREIGN KEY (id_estado) REFERENCES estado(id_estado) ON DELETE CASCADE,
  FOREIGN KEY (id_vendedor) REFERENCES vendedor(id_vendedor) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
