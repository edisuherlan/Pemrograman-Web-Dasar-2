<?php
/**
 * =============================================================================
 * TEST LOGIN — tampil data mahasiswa (HTML biasa, tanpa Bootstrap/header)
 * =============================================================================
 * Dipakai untuk melihat bahwa halaman tanpa `includes/header.php` tetap bisa
 * dilindungi lewat `pastikan_login_atau_redirect()` di awal skrip.
 *
 * Akses: http://localhost/mk_web/test_login.php
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/fungsi.php';
require_once __DIR__ . '/includes/auth.php';
pastikan_login_atau_redirect();

$sql = 'SELECT m.nim, m.nama, m.email, m.angkatan, p.kode_prodi, p.nama_prodi
        FROM mahasiswa m
        INNER JOIN prodi p ON p.id_prodi = m.id_prodi
        ORDER BY m.nim ASC';
$daftar = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test login — Data mahasiswa</title>
</head>
<body>
    <h1>Data mahasiswa (test_login.php)</h1>
    <p>Halaman ini memakai HTML biasa saja. Aksesnya tetap mengikuti aturan login aplikasi.</p>
    <p>
        <a href="index.php">Beranda</a> |
        <a href="logout.php">Logout</a>
    </p>

    <?php if ($daftar === []) { ?>
        <p><strong>Belum ada data mahasiswa.</strong></p>
    <?php } else { ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Angkatan</th>
                <th>Kode prodi</th>
                <th>Nama prodi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($daftar as $row) { ?>
                <tr>
                    <td><?= h((string) $row['nim']) ?></td>
                    <td><?= h((string) $row['nama']) ?></td>
                    <td><?= h($row['email'] !== null ? (string) $row['email'] : '') ?></td>
                    <td><?= h((string) $row['angkatan']) ?></td>
                    <td><?= h((string) $row['kode_prodi']) ?></td>
                    <td><?= h((string) $row['nama_prodi']) ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <p>Total: <?= count($daftar) ?> mahasiswa.</p>
    <?php } ?>
</body>
</html>
