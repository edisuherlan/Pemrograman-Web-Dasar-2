-- Database: perkuliahan
-- Praktikum Pemrograman Web 2 — skema akademik (6 tabel: prodi + 5 tabel lain berelasi)
-- Impor via phpMyAdmin / mysql CLI: mysql -u root < database/perkuliahan.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS perkuliahan;
CREATE DATABASE perkuliahan
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE perkuliahan;

-- 0. Program studi (master: dosen, mahasiswa, dan mata kuliah merujuk ke prodi)
CREATE TABLE prodi (
  id_prodi INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_prodi VARCHAR(20) NOT NULL,
  nama_prodi VARCHAR(160) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_prodi),
  UNIQUE KEY uq_prodi_kode (kode_prodi)
) ENGINE=InnoDB;

-- 1. Dosen (bertugas pada satu program studi; pengampu mata kuliah)
CREATE TABLE dosen (
  id_dosen INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nidn VARCHAR(20) NOT NULL,
  nama VARCHAR(120) NOT NULL,
  email VARCHAR(120) DEFAULT NULL,
  id_prodi INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_dosen),
  UNIQUE KEY uq_dosen_nidn (nidn),
  KEY idx_dosen_prodi (id_prodi),
  CONSTRAINT fk_dosen_prodi
    FOREIGN KEY (id_prodi) REFERENCES prodi (id_prodi)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 2. Mahasiswa (terdaftar pada satu program studi)
CREATE TABLE mahasiswa (
  id_mahasiswa INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nim VARCHAR(20) NOT NULL,
  nama VARCHAR(120) NOT NULL,
  email VARCHAR(120) DEFAULT NULL,
  angkatan YEAR NOT NULL,
  id_prodi INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_mahasiswa),
  UNIQUE KEY uq_mahasiswa_nim (nim),
  KEY idx_mahasiswa_prodi (id_prodi),
  CONSTRAINT fk_mahasiswa_prodi
    FOREIGN KEY (id_prodi) REFERENCES prodi (id_prodi)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. Mata kuliah (milik satu prodi; satu dosen pengampu dari prodi yang sama — dicek di aplikasi)
CREATE TABLE matakuliah (
  id_mk INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_mk VARCHAR(20) NOT NULL,
  nama_mk VARCHAR(160) NOT NULL,
  sks TINYINT UNSIGNED NOT NULL DEFAULT 3,
  id_dosen INT UNSIGNED NOT NULL,
  id_prodi INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_mk),
  UNIQUE KEY uq_matakuliah_kode (kode_mk),
  KEY idx_matakuliah_dosen (id_dosen),
  KEY idx_matakuliah_prodi (id_prodi),
  CONSTRAINT fk_matakuliah_dosen
    FOREIGN KEY (id_dosen) REFERENCES dosen (id_dosen)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_matakuliah_prodi
    FOREIGN KEY (id_prodi) REFERENCES prodi (id_prodi)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. KRS — mahasiswa mengambil MK (harus se-prodi: divalidasi di aplikasi)
CREATE TABLE krs (
  id_krs INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_mahasiswa INT UNSIGNED NOT NULL,
  id_mk INT UNSIGNED NOT NULL,
  semester ENUM('gasal', 'genap') NOT NULL,
  tahun_ajaran VARCHAR(9) NOT NULL COMMENT 'Format: YYYY/YYYY',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_krs),
  UNIQUE KEY uq_krs_ambil (id_mahasiswa, id_mk, semester, tahun_ajaran),
  KEY idx_krs_mahasiswa (id_mahasiswa),
  KEY idx_krs_mk (id_mk),
  CONSTRAINT fk_krs_mahasiswa
    FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa (id_mahasiswa)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_krs_matakuliah
    FOREIGN KEY (id_mk) REFERENCES matakuliah (id_mk)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. Nilai — satu baris per KRS (1:1)
CREATE TABLE nilai (
  id_nilai INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_krs INT UNSIGNED NOT NULL,
  nilai_angka DECIMAL(5,2) NOT NULL,
  nilai_huruf CHAR(2) NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_nilai),
  UNIQUE KEY uq_nilai_krs (id_krs),
  CONSTRAINT fk_nilai_krs
    FOREIGN KEY (id_krs) REFERENCES krs (id_krs)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- Seeder
INSERT INTO prodi (kode_prodi, nama_prodi) VALUES
  ('TI', 'Teknik Informatika'),
  ('SI', 'Sistem Informasi');

INSERT INTO dosen (nidn, nama, email, id_prodi) VALUES
  ('0012345678', 'Edi Suherlan', 'edi.suherlan@kampus.ac.id', 1),
  ('0012345679', 'Budi Santoso', 'budi.santoso@kampus.ac.id', 1),
  ('0012345680', 'Citra Lestari', 'citra.lestari@kampus.ac.id', 1),
  ('0012345681', 'Dodi Firmansyah', 'dodi.firmansyah@kampus.ac.id', 2),
  ('0012345682', 'Eka Putri Wulandari', 'eka.putri@kampus.ac.id', 2);

INSERT INTO matakuliah (kode_mk, nama_mk, sks, id_dosen, id_prodi) VALUES
  ('PW2', 'Pemrograman Web 2', 3, 1, 1),
  ('BD', 'Basis Data', 4, 2, 1),
  ('PBO', 'Pemrograman Berorientasi Objek', 3, 1, 1),
  ('SO', 'Sistem Operasi', 3, 3, 1),
  ('JARKOM', 'Jaringan Komputer', 3, 4, 2),
  ('PAD', 'Pengantar Analisis Data', 3, 5, 2);

INSERT INTO mahasiswa (nim, nama, email, angkatan, id_prodi) VALUES
  ('2024001', 'Andi Pratama', 'm2024001@student.ac.id', 2024, 1),
  ('2024002', 'Siti Rahma', 'm2024002@student.ac.id', 2024, 1),
  ('2024003', 'Bima Saskara', 'm2024003@student.ac.id', 2024, 1),
  ('2024004', 'Dewi Lestari', 'm2024004@student.ac.id', 2024, 1),
  ('2024005', 'Eko Wijaya', 'm2024005@student.ac.id', 2024, 1),
  ('2024006', 'Fitri Handayani', 'm2024006@student.ac.id', 2024, 1),
  ('2024007', 'Gilang Ramadhan', 'm2024007@student.ac.id', 2024, 1),
  ('2024008', 'Hani Kartika', 'm2024008@student.ac.id', 2024, 1),
  ('2024009', 'Indra Kusuma', 'm2024009@student.ac.id', 2024, 1),
  ('2024010', 'Jihan Safitri', 'm2024010@student.ac.id', 2024, 1),
  ('2024011', 'Kurniawan Adi', 'm2024011@student.ac.id', 2024, 1),
  ('2024012', 'Lia Permatasari', 'm2024012@student.ac.id', 2024, 1),
  ('2024013', 'Miko Pratama', 'm2024013@student.ac.id', 2024, 1),
  ('2024014', 'Nadia Salsabila', 'm2024014@student.ac.id', 2024, 1),
  ('2024015', 'Omar Fauzi', 'm2024015@student.ac.id', 2024, 1),
  ('2024016', 'Putri Amelia', 'm2024016@student.ac.id', 2024, 1),
  ('2024017', 'Qori Sandria', 'm2024017@student.ac.id', 2024, 1),
  ('2024018', 'Raka Mahendra', 'm2024018@student.ac.id', 2024, 1),
  ('2024019', 'Salsa Nabila', 'm2024019@student.ac.id', 2024, 1),
  ('2024020', 'Taufik Hidayat', 'm2024020@student.ac.id', 2024, 1),
  ('2024021', 'Umi Kalsum', 'm2024021@student.ac.id', 2024, 1),
  ('2024022', 'Vino Bastian', 'm2024022@student.ac.id', 2024, 1),
  ('2024023', 'Winda Ayu', 'm2024023@student.ac.id', 2024, 1),
  ('2024024', 'Yoga Pratama', 'm2024024@student.ac.id', 2024, 1),
  ('2024025', 'Zahra Maulida', 'm2024025@student.ac.id', 2024, 1);

INSERT INTO krs (id_mahasiswa, id_mk, semester, tahun_ajaran) VALUES
  (1, 1, 'gasal', '2025/2026'),
  (2, 1, 'gasal', '2025/2026'),
  (3, 1, 'gasal', '2025/2026'),
  (4, 1, 'gasal', '2025/2026'),
  (5, 1, 'gasal', '2025/2026'),
  (6, 1, 'gasal', '2025/2026'),
  (7, 1, 'gasal', '2025/2026'),
  (8, 1, 'gasal', '2025/2026'),
  (9, 1, 'gasal', '2025/2026'),
  (10, 1, 'gasal', '2025/2026'),
  (11, 1, 'gasal', '2025/2026'),
  (12, 1, 'gasal', '2025/2026'),
  (13, 1, 'gasal', '2025/2026'),
  (14, 1, 'gasal', '2025/2026'),
  (15, 1, 'gasal', '2025/2026'),
  (16, 1, 'gasal', '2025/2026'),
  (17, 1, 'gasal', '2025/2026'),
  (18, 1, 'gasal', '2025/2026'),
  (19, 1, 'gasal', '2025/2026'),
  (20, 1, 'gasal', '2025/2026'),
  (21, 1, 'gasal', '2025/2026'),
  (22, 1, 'gasal', '2025/2026'),
  (23, 1, 'gasal', '2025/2026'),
  (24, 1, 'gasal', '2025/2026'),
  (25, 1, 'gasal', '2025/2026');

INSERT INTO nilai (id_krs, nilai_angka, nilai_huruf) VALUES
  (1, 88.00, 'A'),
  (2, 82.50, 'B'),
  (3, 76.00, 'B'),
  (4, 91.25, 'A'),
  (5, 79.00, 'B'),
  (6, 85.75, 'A'),
  (7, 73.50, 'C'),
  (8, 68.00, 'C'),
  (9, 94.00, 'A'),
  (10, 81.00, 'B'),
  (11, 77.25, 'B'),
  (12, 86.50, 'A'),
  (13, 70.00, 'C'),
  (14, 83.00, 'B'),
  (15, 75.00, 'B'),
  (16, 89.00, 'A'),
  (17, 72.00, 'C'),
  (18, 80.50, 'B'),
  (19, 78.75, 'B'),
  (20, 67.25, 'C'),
  (21, 92.00, 'A'),
  (22, 74.00, 'C'),
  (23, 87.00, 'A'),
  (24, 69.50, 'C'),
  (25, 84.25, 'B');
