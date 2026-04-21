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
├── prodi.php               # CRUD program studi
├── dosen.php
├── mahasiswa.php
├── matakuliah.php
├── krs.php
├── nilai.php
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

3. Navigasi memakai **menu atas**: Beranda → Prodi / Dosen / Mahasiswa / Mata kuliah / KRS / Nilai.

**Urutan pengisian data yang disarankan:** Prodi → Dosen → Mahasiswa → Mata kuliah → KRS → Nilai (foreign key antar tabel). Di **KRS**, mahasiswa hanya boleh mengambil MK dari **program studi yang sama** (dicek di aplikasi).

---

## File pembelajaran (query tanpa vs dengan JOIN)

Halaman **`belajar_tampil_mahasiswa.php`** adalah contoh sengaja **tanpa Bootstrap/CSS** agar fokus ke alur PHP + SQL:

1. **Tanpa JOIN** — `SELECT` hanya dari tabel `mahasiswa`. Kolom program studi yang tampil adalah **`id_prodi`** (angka foreign key), persis seperti tersimpan di database.
2. **Dengan JOIN** — `mahasiswa` di-`INNER JOIN` ke `prodi` sehingga bisa menampilkan **kode dan nama** program studi yang mudah dibaca.

Akses: `http://localhost/mk_web/belajar_tampil_mahasiswa.php` (sesuaikan path jika folder proyek berbeda).

---

## Skema database

Database **`perkuliahan`** memiliki **6 tabel** yang saling berelasi:

| Tabel | Peran singkat |
|-------|----------------|
| `prodi` | Program studi (master); dirujuk `dosen`, `mahasiswa`, `matakuliah` |
| `dosen` | Data pengajar; bertugas pada satu `prodi` |
| `mahasiswa` | Data mahasiswa; terdaftar pada satu `prodi` |
| `matakuliah` | Mata kuliah milik satu `prodi`; satu dosen pengampu (dosen se-prodi) |
| `krs` | Mahasiswa mengambil MK per semester & tahun ajaran (MK & mhs harus se-prodi) |
| `nilai` | Nilai per baris KRS (relasi 1:1 dengan `krs`) |

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
