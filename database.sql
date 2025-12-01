CREATE DATABASE IF NOT EXISTS perpustakaan_db;
USE perpustakaan_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('Admin', 'Petugas', 'Member') NOT NULL DEFAULT 'Member',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  author VARCHAR(100) NOT NULL,
  category VARCHAR(100),
  year INT,
  stock INT DEFAULT 0,
  cover VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  loan_date DATE NOT NULL,
  due_date DATE NOT NULL,
  return_date DATE DEFAULT NULL,
  status ENUM('ongoing','returned','late') DEFAULT 'ongoing',
  fine INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_loans_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_loans_book FOREIGN KEY (book_id) REFERENCES books(id)
);

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  booking_date DATE NOT NULL,
  expire_date DATE NOT NULL,
  status ENUM('pending','approved','picked_up','cancelled','expired') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_bookings_book FOREIGN KEY (book_id) REFERENCES books(id)
);

INSERT INTO users (name, email, password, role) VALUES
('Admin Perpus', 'admin@perpus.test',  SHA2('123456',256), 'Admin'),
('Petugas Perpus', 'petugas@perpus.test', SHA2('123456',256), 'Petugas'),
('Member Contoh', 'member@perpus.test', SHA2('123456',256), 'Member');


INSERT INTO books (title, author, category, year, stock, cover)
VALUES
('Dune', 'Frank Herbert', 'Sci-Fi', 1965, 4, 'dune.jpg'),
('The Martian', 'Andy Weir', 'Sci-Fi', 2011, 2, 'martian.jpg'),
('The Alchemist', 'Paulo Coelho', 'Fiction', 1988, 7, 'alchemist.jpg');

-- bikin tabel untuk booking via API
CREATE TABLE IF NOT EXISTS api_bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id VARCHAR(50) NOT NULL,
  book_title VARCHAR(255),
  book_author VARCHAR(255),
  book_cover VARCHAR(500),
  booking_date DATE NOT NULL,
  expire_date DATE NOT NULL,
  status ENUM('active', 'expired', 'collected', 'cancelled') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);