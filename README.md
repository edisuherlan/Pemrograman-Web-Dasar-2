# Pemrograman Web Dasar 2 — Aplikasi Pengelolaan Akademik (`perkuliahan`)

Repositori ini berisi **proyek praktikum** berupa aplikasi web berbasis **PHP** dan **MySQL** untuk mengelola data akademik sederhana: dosen, mahasiswa, mata kuliah, KRS (kartu rencana studi), dan nilai. Antarmuka memakai **Bootstrap 5** (CDN) agar tampilan responsif dan konsisten.

**Mata kuliah:** Pemrograman Web 2  
**Topik utama:** CRUD (Create, Read, Update, Delete), koneksi database dengan **PDO**, relasi antar tabel, dan pola **Post/Redirect/Get (PRG)** pada form.

---

## Daftar isi

- [Fitur](#fitur)
- [Teknologi yang dipakai](#teknologi-yang-dipakai)
- [Struktur folder](#struktur-folder)
- [Prasyarat](#prasyarat)
- [Instalasi lokal (Laragon)](#instalasi-lokal-laragon)
- [Konfigurasi database](#konfigurasi-database)
- [Menjalankan aplikasi](#menjalankan-aplikasi)
- [Skema database](#skema-database)
- [Keamanan (catatan pembelajaran)](#keamanan-catatan-pembelajaran)
- [Referensi](#referensi)

---

## Fitur

| Modul        | Deskripsi singkat |
|-------------|-------------------|
| **Beranda** | Pintasan ke semua modul + ringkasan alur pengisian data |
| **Dosen**   | CRUD data dosen (NIDN, nama, email) |
| **Mahasiswa** | CRUD data mahasiswa (NIM, nama, email, angkatan) |
| **Mata kuliah** | CRUD mata kuliah; memilih **dosen pengampu** dari dropdown |
| **KRS**     | CRUD pengambilan MK per mahasiswa, semester (gasal/genap), dan tahun ajaran |
| **Nilai**   | CRUD nilai angka & huruf per **KRS** (satu KRS satu nilai); dropdown KRS menampilkan NIM, nama, MK, semester |

Setiap halaman modul dilengkapi **komentar kode berbahasa Indonesia** untuk membantu pemahaman mahasiswa pemula.

---

## Teknologi yang dipakai

| Komponen | Versi / catatan |
|----------|------------------|
| PHP | 7.4+ disarankan (kode memakai `declare(strict_types=1)`) |
| MySQL / MariaDB | Sesuai instalasi Laragon |
| PDO | Driver `mysql`, prepared statement (`?`) |
| Bootstrap | 5.3.x (CSS + JS bundle dari CDN) |
| Bootstrap Icons | 1.11.x (CDN) |

---

## Struktur folder

```
mk_web/
├── config/
│   └── database.php      # Koneksi PDO ke MySQL
├── includes/
│   ├── header.php          # Layout atas + navbar + alert
│   ├── footer.php        # Layout bawah + script Bootstrap
│   └── fungsi.php         # Helper: h() escape HTML, alert dari URL
├── database/
│   ├── perkuliahan.sql     # Skema + data contoh (seeder)
│   └── perkuliahan.dbml    # Diagram DBML untuk dbdiagram.io (opsional)
├── index.php               # Halaman beranda
├── dosen.php
├── mahasiswa.php
├── matakuliah.php
├── krs.php
├── nilai.php
└── README.md
```

---

## Prasyarat

- [Laragon](https://laragon.org/) (atau stack serupa: Apache/Nginx + PHP + MySQL) **menyala**
- Ekstensi PHP: `pdo`, `pdo_mysql` (biasanya sudah aktif di Laragon)
- Akun untuk mengkloning/mengunggah ke GitHub (opsional, untuk kolaborasi)

---

## Instalasi lokal (Laragon)

1. **Letakkan folder proyek**  
   Salin folder `mk_web` ke direktori web Laragon, misalnya:  
   `C:\laragon\www\mk_web`

2. **Buat database**  
   - Buka **phpMyAdmin** atau **HeidiSQL**, atau gunakan CLI MySQL.  
   - Impor file **`database/perkuliahan.sql`** (berisi `CREATE DATABASE`, tabel, dan data contoh).  
   - Pastikan nama database **`perkuliahan`** ada (sesuai skrip SQL).

3. **Sesuaikan kredensial** (jika perlu)  
   Edit `config/database.php`:

   - `$host` — biasanya `127.0.0.1`
   - `$namaDatabase` — `perkuliahan`
   - `$user` / `$password` — misalnya `root` dan password kosong (default Laragon)

---

## Konfigurasi database

File utama: **`config/database.php`**

- Variabel **`$pdo`** dipakai di seluruh halaman CRUD untuk menjalankan query.
- Jika koneksi gagal, aplikasi menampilkan halaman error singkat (bukan layar putih kosong).

---

## Menjalankan aplikasi

1. Start **Apache** dan **MySQL** dari Laragon.  
2. Buka browser ke salah satu URL berikut (sesuaikan nama folder):

   - `http://localhost/mk_web/`
   - atau virtual host Laragon jika Anda mengatur domain khusus

3. Navigasi memakai **menu atas**: Beranda → Dosen / Mahasiswa / Mata kuliah / KRS / Nilai.

**Urutan pengisian data yang disarankan:** Dosen → Mahasiswa → Mata kuliah → KRS → Nilai (karena ada foreign key antar tabel).

---

## Skema database

Database **`perkuliahan`** memiliki **5 tabel** yang saling berelasi:

| Tabel | Peran singkat |
|-------|----------------|
| `dosen` | Data pengajar |
| `mahasiswa` | Data mahasiswa |
| `matakuliah` | Mata kuliah; relasi ke `dosen` (pengampu) |
| `krs` | Mahasiswa mengambil MK per semester & tahun ajaran |
| `nilai` | Nilai per baris KRS (relasi 1:1 dengan `krs`) |

Untuk **diagram ERD** dalam format DBML, buka file **`database/perkuliahan.dbml`** dan tempel ke [dbdiagram.io](https://dbdiagram.io/).

---

## Keamanan (catatan pembelajaran)

- Query memakai **`prepare()` + `execute()`** dengan placeholder `?` untuk mengurangi risiko **SQL injection**.
- Output HTML memakai fungsi **`h()`** (`htmlspecialchars`) untuk mengurangi risiko **XSS**.
- Untuk produksi sungguhan, pertimbangkan tambahan: validasi server lebih ketat, CSRF token pada form, penyimpanan kredensial di environment (bukan hardcode), dan HTTPS.

---

## Referensi

- Repositori kursus / pengumpulan tugas: **[Pemrograman-Web-Dasar-2](https://github.com/edisuherlan/Pemrograman-Web-Dasar-2)**  
- Bootstrap: [getbootstrap.com](https://getbootstrap.com/)  
- Diagram database: [dbdiagram.io](https://dbdiagram.io/)

---

## Lisensi & penggunaan

Proyek ini dibuat untuk **keperluan pembelajaran** (praktikum pemrograman web). Silakan dimodifikasi untuk latihan di kelas; untuk penggunaan di luar konteks akademik, sesuaikan lisensi dengan kebijakan institusi Anda.

---

*README ini menjelaskan isi repositori secara teknis agar mudah diikuti oleh mahasiswa dan penguji.*
