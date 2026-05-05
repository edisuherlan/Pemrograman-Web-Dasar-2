# Pemrograman Web Dasar 2 — Aplikasi Pengelolaan Akademik (`perkuliahan`)

Repositori ini berisi **proyek praktikum** berupa aplikasi web berbasis **PHP** dan **MySQL** untuk mengelola data akademik sederhana: dosen, mahasiswa, mata kuliah, KRS (kartu rencana studi), dan nilai. Antarmuka memakai **Bootstrap 5** (CDN) agar tampilan responsif dan konsisten.

**Mata kuliah:** Pemrograman Web 2  
**Topik utama:** CRUD (Create, Read, Update, Delete), koneksi database dengan **PDO**, relasi antar tabel, pola **Post/Redirect/Get (PRG)** pada form, serta **studi kasus login** (sesi PHP, `password_hash` / `password_verify`, dan pembatasan akses halaman).

---

## Daftar isi

- [Fitur](#fitur)
- [Login & autentikasi (studi kasus)](#login--autentikasi-studi-kasus)
- [Teknologi yang dipakai](#teknologi-yang-dipakai)
- [Struktur folder](#struktur-folder)
- [Prasyarat](#prasyarat)
- [Instalasi lokal (Laragon)](#instalasi-lokal-laragon)
- [Konfigurasi database](#konfigurasi-database)
- [Menjalankan aplikasi](#menjalankan-aplikasi)
- [File pembelajaran (query tanpa vs dengan JOIN)](#file-pembelajaran-query-tanpa-vs-dengan-join)
- [Skema database](#skema-database)
- [Desain ERD (diagram)](#desain-erd-diagram)
- [Keamanan (catatan pembelajaran)](#keamanan-catatan-pembelajaran)
- [Referensi](#referensi)

---

## Fitur

| Modul        | Deskripsi singkat |
|-------------|-------------------|
| **Beranda** | Pintasan ke semua modul + ringkasan alur pengisian data |
| **Prodi** | CRUD program studi (kode & nama); master untuk dosen/mahasiswa/MK |
| **Dosen**   | CRUD data dosen (NIDN, nama, email, **prodi**) |
| **Mahasiswa** | CRUD data mahasiswa (NIM, nama, email, angkatan, **prodi**) |
| **Mata kuliah** | CRUD mata kuliah; **prodi** MK + **dosen pengampu se-prodi** |
| **KRS**     | CRUD pengambilan MK (**mhs & MK harus satu prodi**), semester, tahun ajaran |
| **Nilai**   | CRUD nilai angka & huruf per **KRS**; tampilan menyertakan kode prodi |
| **Login** | Form login, logout, dan **panel aman** (halaman contoh setelah sesi aktif) |
| **Menu navbar** | Setelah login: nama pengguna, tautan **Panel aman**, **Keluar**; sebelum login: **Login** |

Setiap halaman modul dilengkapi **komentar kode berbahasa Indonesia** untuk membantu pemahaman mahasiswa pemula.

---

## Login & autentikasi (studi kasus)

- **Akses umum:** membuka beranda atau modul CRUD tanpa sesi akan mengarahkan ke **`login.php`** (kecuali halaman yang sengaja dibuka tanpa login; lihat di bawah).
- **Tabel `pengguna`:** menyimpan `username`, `password_hash` (bcrypt), `nama_tampilan`, dan `peran` (`admin` / `operator`). Sandi **tidak** disimpan teks polos di database.
- **Akun demo** (ada setelah impor `database/perkuliahan.sql` atau migrasi):
  - `admin` / `admin123`
  - `operator` / `operator123`
- **Database yang sudah lama dipakai:** jalankan sekali **`database/migration_tabel_pengguna.sql`** pada database `perkuliahan` agar tabel `pengguna` dan akun demo terbentuk (tanpa menghapus data akademik).
- **File inti:**
  - `includes/auth.php` — `pastikan_sesi()`, `pengguna_sudah_login()`, `pastikan_login_atau_redirect()`, `daftar_halaman_tanpa_login()`, dll.
  - `includes/header.php` — memanggil `pastikan_login_atau_redirect($basename)` sebelum HTML dikirim, sehingga semua halaman yang memakai layout terlindungi secara konsisten.
  - `login.php` / `logout.php` — masuk dan keluar aplikasi.
  - `panel_aman.php` — contoh halaman yang hanya bermakna setelah login (bisa dipelajari bersama `wajib_sudah_login()` di `auth.php`).
- **Halaman tanpa login (contoh simulasi):** di `includes/auth.php`, fungsi `daftar_halaman_tanpa_login()` mengembalikan daftar nama file yang boleh diakses tanpa sesi. Secara default **`login.php`** selalu di sana; **`dosen.php`** ditambahkan sebagai **contoh** halaman “publik” untuk praktik (silakan hapus dari daftar jika ingin semua modul wajib login).
- **Halaman HTML biasa tanpa Bootstrap:** `test_login.php` menampilkan data mahasiswa dengan HTML sederhana, tetapi tetap memanggil `pastikan_login_atau_redirect()` di awal file (pol yang sama dengan `belajar_tampil_mahasiswa.php`).

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
│   ├── header.php        # Layout atas + navbar + alert + guard login global
│   ├── footer.php        # Layout bawah + script Bootstrap
│   ├── fungsi.php        # Helper: h() escape HTML, alert dari URL
│   └── auth.php          # Studi kasus login: sesi, redirect, daftar halaman publik
├── database/
│   ├── perkuliahan.sql           # Skema + data contoh (termasuk tabel pengguna)
│   ├── migration_tabel_pengguna.sql  # Tambah tabel pengguna pada DB yang sudah ada
│   └── perkuliahan.dbml      # Diagram DBML untuk dbdiagram.io (opsional)
├── index.php               # Halaman beranda
├── prodi.php               # CRUD program studi
├── dosen.php
├── mahasiswa.php
├── matakuliah.php
├── krs.php
├── nilai.php
├── login.php               # Form login
├── logout.php              # Hapus sesi
├── panel_aman.php          # Contoh halaman setelah login
├── test_login.php          # Contoh tampil mahasiswa (HTML biasa + guard login)
├── belajar_tampil_mahasiswa.php  # Contoh: tampil mahasiswa tanpa JOIN vs dengan JOIN (tanpa Bootstrap)
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
   - Impor file **`database/perkuliahan.sql`** (berisi `CREATE DATABASE`, tabel akademik + **`pengguna`**, dan data contoh).  
   - Pastikan nama database **`perkuliahan`** ada (sesuai skrip SQL).  
   - Jika Anda **tidak** ingin mengulang impor penuh: jalankan **`database/migration_tabel_pengguna.sql`** pada database `perkuliahan` supaya fitur login berfungsi.

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
2. Buka browser ke aplikasi, misalnya **`http://localhost/mk_web/`** (sesuaikan folder atau virtual host Laragon). Jika sesi login belum aktif, Anda akan diarahkan ke **`login.php`**. Gunakan akun demo (**`admin` / `admin123`** atau **`operator` / `operator123`**), lalu lanjutkan ke beranda.  
3. Navigasi memakai **menu atas**: Beranda → Prodi / Dosen / Mahasiswa / Mata kuliah / KRS / Nilai, serta **Panel aman** / **Keluar** setelah login.

**Urutan pengisian data yang disarankan:** Prodi → Dosen → Mahasiswa → Mata kuliah → KRS → Nilai (foreign key antar tabel). Di **KRS**, mahasiswa hanya boleh mengambil MK dari **program studi yang sama** (dicek di aplikasi).

---

## File pembelajaran (query tanpa vs dengan JOIN)

Halaman **`belajar_tampil_mahasiswa.php`** adalah contoh sengaja **tanpa Bootstrap/CSS** agar fokus ke alur PHP + SQL:

1. **Tanpa JOIN** — `SELECT` hanya dari tabel `mahasiswa`. Kolom program studi yang tampil adalah **`id_prodi`** (angka foreign key), persis seperti tersimpan di database.
2. **Dengan JOIN** — `mahasiswa` di-`INNER JOIN` ke `prodi` sehingga bisa menampilkan **kode dan nama** program studi yang mudah dibaca.

Akses: `http://localhost/mk_web/belajar_tampil_mahasiswa.php` (sesuaikan path jika folder proyek berbeda).

Halaman **`test_login.php`** adalah contoh lain: satu tabel mahasiswa + prodi (JOIN), **HTML biasa** tanpa Bootstrap, tetap memakai guard login di awal skrip.

---

## Skema database

Database **`perkuliahan`** memiliki **6 tabel akademik** yang saling berelasi, plus **1 tabel** untuk studi kasus login:

| Tabel | Peran singkat |
|-------|----------------|
| `prodi` | Program studi (master); dirujuk `dosen`, `mahasiswa`, `matakuliah` |
| `dosen` | Data pengajar; bertugas pada satu `prodi` |
| `mahasiswa` | Data mahasiswa; terdaftar pada satu `prodi` |
| `matakuliah` | Mata kuliah milik satu `prodi`; satu dosen pengampu (dosen se-prodi) |
| `krs` | Mahasiswa mengambil MK per semester & tahun ajaran (MK & mhs harus se-prodi) |
| `nilai` | Nilai per baris KRS (relasi 1:1 dengan `krs`) |
| `pengguna` | Akun login (`username`, `password_hash`, `nama_tampilan`, `peran`); **tidak** berelasi ke tabel akademik |

### Desain ERD (diagram)

**ERD (Entity Relationship Diagram)** adalah gambaran visual **entitas** (tabel), **atribut** (kolom), dan **relasi** antar tabel (satu-ke-banyak, satu-ke-satu, dll.). Membaca ERD membantu memahami alur data sebelum membuat form dan query di aplikasi.

**Link desain ERD database proyek ini (dbdiagram.io):**

- **[ERD Perkuliahan — dbdiagram.io](https://dbdiagram.io/d/ERD-Perkuliahan-69e7f5411bbca0331205788c)**

Di halaman tersebut Anda dapat melihat diagram interaktif (perbarui impor DBML jika skema berubah). Skema terkini ada di **`database/perkuliahan.sql`** dan **`database/perkuliahan.dbml`** (termasuk tabel **`prodi`**).

**Cara lain (offline / edit):** buka file **`database/perkuliahan.dbml`** di editor, salin isinya, lalu tempel di [dbdiagram.io](https://dbdiagram.io/) jika ingin mengubah desain atau mengekspor gambar (PNG/PDF) dari sana.

---

## Keamanan (catatan pembelajaran)

- Query memakai **`prepare()` + `execute()`** dengan placeholder `?` untuk mengurangi risiko **SQL injection**.
- Output HTML memakai fungsi **`h()`** (`htmlspecialchars`) untuk mengurangi risiko **XSS**.
- Login: sandi disimpan dengan **`password_hash`** dan dicek dengan **`password_verify`**; setelah sukses, **`session_regenerate_id(true)`** mengurangi risiko session fixation.
- Akses halaman: **`pastikan_login_atau_redirect()`** di `header.php` (dan di skrip tanpa layout) membatasi siapa yang boleh membuka URL tertentu; daftar pengecualian dikelola di **`daftar_halaman_tanpa_login()`** (`includes/auth.php`).
- Untuk produksi sungguhan, pertimbangkan tambahan: validasi server lebih ketat, CSRF token pada form, penyimpanan kredensial di environment (bukan hardcode), dan HTTPS.

---

## Referensi

- Repositori kursus / pengumpulan tugas: **[Pemrograman-Web-Dasar-2](https://github.com/edisuherlan/Pemrograman-Web-Dasar-2)**  
- Bootstrap: [getbootstrap.com](https://getbootstrap.com/)  
- Desain ERD database (`perkuliahan`): [ERD Perkuliahan di dbdiagram.io](https://dbdiagram.io/d/ERD-Perkuliahan-69e7f5411bbca0331205788c)
- Alat diagram database: [dbdiagram.io](https://dbdiagram.io/)

---

## Lisensi & penggunaan

Proyek ini dibuat untuk **keperluan pembelajaran** (praktikum pemrograman web). Silakan dimodifikasi untuk latihan di kelas; untuk penggunaan di luar konteks akademik, sesuaikan lisensi dengan kebijakan institusi Anda.

---

*README ini menjelaskan isi repositori secara teknis agar mudah diikuti oleh mahasiswa dan penguji.*
