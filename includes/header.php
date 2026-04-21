<?php
/**
 * =============================================================================
 * HEADER & LAYOUT — Bootstrap 5 (CDN)
 * =============================================================================
 * Variabel yang HARUS sudah di-set sebelum include file ini:
 * - $judulHalaman (string) — judul di tab browser & di heading halaman
 *
 * Catatan untuk mahasiswa:
 * - Navbar di bawah memuat link ke setiap modul CRUD agar navigasi konsisten.
 * - Bootstrap di-load dari CDN: tidak perlu download manual, selalu versi terbaru stabil.
 * =============================================================================
 */

// Aktifkan pengecekan tipe ketat di file ini juga
declare(strict_types=1);

// Muat fungsi h() dan alert — harus sebelum pemakaian h() di bawah
require_once __DIR__ . '/fungsi.php';

// Jika halaman lupa set judul, pakai judul default supaya tidak kosong
if (!isset($judulHalaman)) {
    $judulHalaman = 'Aplikasi Perkuliahan';
}

// Nama file skrip saat ini (misalnya dosen.php) dipakai untuk menandai menu aktif
$basename = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
?>
<!-- Deklarasi HTML5; lang=id untuk aksesibilitas & mesin pencari -->
<!DOCTYPE html>
<!-- Bahasa halaman Indonesia -->
<html lang="id">
<head>
    <!-- Encoding UTF-8 agar huruf Indonesia tampil benar -->
    <meta charset="utf-8">
    <!-- Agar tampilan responsif di HP: lebar mengikuti layar -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Judul tab browser: dinamis + nama aplikasi -->
    <title><?= h($judulHalaman) ?> — Perkuliahan</title>
    <!-- CSS Bootstrap 5.3.3 dari CDN (integrity = cek file tidak diubah orang lain) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Ikon Bootstrap Icons (untuk ikon topi wisuda, orang, buku, dll.) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<!-- bg-light = latar abu-abu sangat muda (tema bersih) -->
<body class="bg-light">
<!-- Navbar: menu utama; navbar-expand-lg = menu jadi hamburger di layar kecil -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
    <!-- container = konten tidak mepet ke pinggir layar lebar -->
    <div class="container">
        <!-- Brand / logo teks + ikon; fw-semibold = font agak tebal -->
        <a class="navbar-brand fw-semibold" href="index.php"><i class="bi bi-mortarboard-fill me-1"></i>Perkuliahan</a>
        <!-- Tombol tiga garis untuk buka/tutup menu di mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navUtama"
                aria-controls="navUtama" aria-expanded="false" aria-label="Toggle navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Daftar link menu; collapse = disembunyikan di layar kecil sampai diklik -->
        <div class="collapse navbar-collapse" id="navUtama">
            <!-- ms-auto = dorong menu ke kanan; mb-2 = margin bawah di mobile -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <!-- Setiap item: class active jika file PHP sama dengan nama file sekarang -->
                <li class="nav-item"><a class="nav-link <?= $basename === 'index.php' ? 'active' : '' ?>" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link <?= $basename === 'prodi.php' ? 'active' : '' ?>" href="prodi.php">Prodi</a></li>
                <li class="nav-item"><a class="nav-link <?= $basename === 'dosen.php' ? 'active' : '' ?>" href="dosen.php">Dosen</a></li>
                <li class="nav-item"><a class="nav-link <?= $basename === 'mahasiswa.php' ? 'active' : '' ?>" href="mahasiswa.php">Mahasiswa</a></li>
                <li class="nav-item"><a class="nav-link <?= $basename === 'matakuliah.php' ? 'active' : '' ?>" href="matakuliah.php">Mata kuliah</a></li>
                <li class="nav-item"><a class="nav-link <?= $basename === 'krs.php' ? 'active' : '' ?>" href="krs.php">KRS</a></li>
                <li class="nav-item"><a class="nav-link <?= $basename === 'nilai.php' ? 'active' : '' ?>" href="nilai.php">Nilai</a></li>
            </ul>
        </div>
    </div>
</nav>
<!-- Konten utama halaman; pb-5 = padding bawah supaya tidak mentok footer -->
<main class="container pb-5">
    <!-- Judul besar halaman (h3 = lebih kecil dari h1 klasik, tetap jelas) -->
    <h1 class="h3 mb-3 text-primary"><?= h($judulHalaman) ?></h1>
    <?php
    // Panggil fungsi: jika URL punya ?status=..., tampilkan alert hijau/merah/dll.
    tampilkan_alert_dari_url();
    ?>
