-- Tambahan studi kasus login untuk database perkuliahan yang SUDAH ADA
-- (tanpa DROP database). Jalankan sekali di phpMyAdmin atau:
--   mysql -u root perkuliahan < database/migration_tabel_pengguna.sql

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS pengguna (
  id_pengguna INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  nama_tampilan VARCHAR(120) NOT NULL,
  peran ENUM('admin', 'operator') NOT NULL DEFAULT 'operator',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_pengguna),
  UNIQUE KEY uq_pengguna_username (username)
) ENGINE=InnoDB;

-- INSERT IGNORE: jika username sudah ada, baris dilewati (aman dijalankan ulang)
INSERT IGNORE INTO pengguna (username, password_hash, nama_tampilan, peran) VALUES
  ('admin', '$2y$10$HUvx.PsXzlXxwBAntK8HzeaIKQMnjn4xJ5QkG8nRl9yWovWnmHza6', 'Administrator', 'admin'),
  ('operator', '$2y$10$9muX38gQQtMdgkNW8RLcqOtZpfABczMmbYVDjZu/ncw/ylUiV3dv2', 'Operator Demo', 'operator');
