CREATE DATABASE IF NOT EXISTS bdbibliotech_mvc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bdbibliotech_mvc;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(80) NOT NULL,
  apellidos VARCHAR(80) NOT NULL,
  documento VARCHAR(15) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  telefono VARCHAR(20) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('estudiante','bibliotecario','administrador') NOT NULL DEFAULT 'estudiante',
  estado ENUM('activo','inactivo','suspendido') NOT NULL DEFAULT 'activo',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT NOT NULL DEFAULT 0,
  INDEX (token_hash),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE categories (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(80) NOT NULL UNIQUE);
CREATE TABLE authors (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120) NOT NULL UNIQUE);
CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(200) NOT NULL,
  author_id INT NULL,
  category_id INT NULL,
  editorial VARCHAR(120) NULL,
  anio SMALLINT NULL,
  isbn VARCHAR(20) NULL,
  descripcion TEXT NULL,
  portada VARCHAR(255) NULL,
  copies_total INT NOT NULL DEFAULT 1,
  copies_available INT NOT NULL DEFAULT 1,
  stock_min INT NOT NULL DEFAULT 1,
  activo TINYINT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE SET NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  FULLTEXT KEY ft_books (titulo, descripcion)
);
CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  estado ENUM('pendiente','confirmada','recogida','vencida','cancelada') NOT NULL DEFAULT 'pendiente',
  created_at DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  aviso TINYINT NOT NULL DEFAULT 0,
  INDEX (user_id), INDEX (book_id), INDEX (estado, expires_at),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (book_id) REFERENCES books(id)
);
CREATE TABLE waitlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  notified TINYINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (user_id, book_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);
CREATE TABLE loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  reservation_id INT NULL,
  librarian_id INT NULL,
  loaned_at DATETIME NOT NULL,
  due_date DATE NOT NULL,
  returned_at DATETIME NULL,
  estado_devolucion ENUM('bueno','danado','perdido') NULL,
  renovaciones INT NOT NULL DEFAULT 0,
  recordado TINYINT NOT NULL DEFAULT 0,
  INDEX (user_id), INDEX (book_id), INDEX (returned_at, due_date),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (book_id) REFERENCES books(id)
);
CREATE TABLE fines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  loan_id INT NULL,
  motivo ENUM('retraso','dano','perdida') NOT NULL,
  dias_retraso INT NOT NULL DEFAULT 0,
  monto DECIMAL(10,2) NOT NULL,
  estado ENUM('pendiente','pagada','condonada','anulada') NOT NULL DEFAULT 'pendiente',
  comprobante VARCHAR(30) NULL,
  justificacion VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  paid_by INT NULL,
  INDEX (user_id), INDEX (estado),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (loan_id) REFERENCES loans(id)
);
CREATE TABLE settings (clave VARCHAR(50) PRIMARY KEY, valor VARCHAR(100) NOT NULL, descripcion VARCHAR(200) NOT NULL);
CREATE TABLE stock_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  tipo ENUM('alta','baja','prestamo','devolucion','reserva','liberacion') NOT NULL,
  cantidad INT NOT NULL,
  user_id INT NULL,
  nota VARCHAR(200) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (book_id),
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);
CREATE TABLE audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  accion VARCHAR(60) NOT NULL,
  detalle VARCHAR(255) NULL,
  ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO settings VALUES
 ('dias_prestamo','7','Días de préstamo'),
 ('max_prestamos','3','Máximo de préstamos/reservas activos por usuario'),
 ('plazo_recojo_horas','48','Plazo de recojo de reservas (horas)'),
 ('max_renovaciones','2','Renovaciones permitidas por préstamo'),
 ('tarifa_diaria','1.50','Tarifa diaria de multa por retraso (S/)'),
 ('multa_dano','20.00','Multa por daño de un ejemplar (S/)'),
 ('multa_perdida','60.00','Multa por pérdida de un ejemplar (S/)'),
 ('dias_recordatorio','2','Días de anticipación del recordatorio de devolución');
