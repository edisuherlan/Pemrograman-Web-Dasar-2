<?php
/**
 * =============================================================================
 * KONEKSI KE DATABASE MySQL (PDO)
 * =============================================================================
 * File ini dipanggil di awal setiap halaman yang perlu mengakses database.
 *
 * Apa itu PDO?
 * - PDO (PHP Data Objects) adalah cara standar di PHP untuk berbicara dengan
 *   database. Keuntungannya: aman dari SQL Injection jika kita pakai
 *   "prepared statement" (placeholder ? pada query).
 *
 * Cara pakai di Laragon:
 * - Pastikan database `perkuliahan` sudah diimpor dari file database/perkuliahan.sql
 * - User default Laragon biasanya root tanpa password (sesuaikan jika beda).
 *
 * Jika koneksi gagal, halaman akan menampilkan pesan error agar mudah dilacak.
 * =============================================================================
 */

// strict_types=1 artinya PHP akan tegas soal tipe data (integer vs string), mengurangi bug halus
declare(strict_types=1);

// Alamat server MySQL; 127.0.0.1 sama dengan "localhost" di Windows/Laragon
$host = '127.0.0.1';

// Nama database harus sama dengan yang Anda buat di phpMyAdmin / file SQL
$namaDatabase = 'perkuliahan';

// Username MySQL — default Laragon: root
$user = 'root';

// Password MySQL — default Laragon sering kosong
$password = '';

// Charset utf8mb4 mendukung huruf Indonesia dan emoji dengan benar
$charset = 'utf8mb4';

// DSN = "Data Source Name": rangkuman cara PHP menghubungi MySQL
$dsn = "mysql:host={$host};dbname={$namaDatabase};charset={$charset}";

// Opsi PDO: lempar exception saat error, kembalikan array asosiatif, jangan tiru prepare di PHP (lebih aman)
$pilihan = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Coba buat koneksi; jika gagal, tangkap error dan tampilkan pesan sederhana (bukan layar putih kosong)
try {
    // Variabel $pdo dipakai di semua file CRUD untuk query
    $pdo = new PDO($dsn, $user, $password, $pilihan);
} catch (PDOException $e) {
    // HTTP 500 = kesalahan server; membantu debugging
    http_response_code(500);
    // Tampilkan HTML minimal + Bootstrap dari CDN agar pesan tetap terbaca
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Koneksi gagal</title>'
        . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="p-4">';
    // Pesan untuk user + detail teknis kecil (pesan error database)
    echo '<div class="alert alert-danger"><strong>Koneksi database gagal.</strong><br>'
        . 'Periksa: (1) MySQL Laragon sudah jalan, (2) database <code>perkuliahan</code> sudah diimpor, '
        . '(3) user/password di <code>config/database.php</code> benar.<br><small>'
        . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</small></div></body></html>';
    // Hentikan skrip: tidak boleh lanjut query tanpa $pdo
    exit;
}
