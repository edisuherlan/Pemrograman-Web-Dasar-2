<?php
/**
 * =============================================================================
 * CONTOH PEMBELAJARAN: menampilkan data tabel MAHASISWA (PHP + HTML saja)
 * =============================================================================
 * File ini SENGAJA dibuat sederhana: tidak pakai Bootstrap/CSS eksternal,
 * hanya HTML biasa supaya fokus ke: koneksi → query → loop → tampilkan.
 *
 * Dua versi tampilan:
 * - TANPA JOIN: hanya membaca tabel mahasiswa → kolom prodi yang tampil adalah
 *   id_prodi (angka ID), persis seperti disimpan di database ("apa adanya").
 * - DENGAN JOIN: menggabungkan tabel mahasiswa + prodi → bisa menampilkan
 *   kode_prodi dan nama_prodi yang manusiawi dibaca.
 *
 * Prasyarat: database `perkuliahan` sudah diimpor (lihat database/perkuliahan.sql)
 * Akses: http://localhost/mk_web/belajar_tampil_mahasiswa.php
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/fungsi.php';

// =============================================================================
// VERSI A — TANPA JOIN: hanya SELECT dari tabel mahasiswa
// =============================================================================
// Di tabel mahasiswa yang tersimpan untuk prodi hanyalah id_prodi (bilangan).
// Tanpa JOIN ke tabel prodi, di layar Anda hanya bisa menampilkan angka itu.
$sqlTanpaJoin = 'SELECT id_mahasiswa, nim, nama, email, angkatan, id_prodi, created_at
                 FROM mahasiswa
                 ORDER BY nim ASC';
$stmtA = $pdo->query($sqlTanpaJoin);
/** @var array<int, array<string, mixed>> $dataTanpaJoin */
$dataTanpaJoin = $stmtA->fetchAll(PDO::FETCH_ASSOC);

// =============================================================================
// VERSI B — DENGAN JOIN: mahasiswa + prodi (relasi id_prodi)
// =============================================================================
// JOIN memungkinkan mengambil kode_prodi & nama_prodi sekaligus dalam satu query.
$sqlDenganJoin = 'SELECT m.id_mahasiswa, m.nim, m.nama, m.email, m.angkatan, m.created_at,
                         p.kode_prodi, p.nama_prodi
                  FROM mahasiswa m
                  INNER JOIN prodi p ON p.id_prodi = m.id_prodi
                  ORDER BY m.nim ASC';
$stmtB = $pdo->query($sqlDenganJoin);
/** @var array<int, array<string, mixed>> $dataDenganJoin */
$dataDenganJoin = $stmtB->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Belajar: Data Mahasiswa (tanpa vs dengan JOIN)</title>
</head>
<body>
    <h1>Daftar Mahasiswa — dua cara query</h1>

    <p><a href="index.php">← Kembali ke beranda aplikasi</a></p>

    <hr>

    <h2>1) Tanpa JOIN — data prodi &quot;apa adanya&quot; dari tabel mahasiswa</h2>
    <p>
        Query hanya ke <code>mahasiswa</code>. Kolom yang ada untuk program studi adalah
        <strong>id_prodi</strong> (foreign key): berupa <em>angka</em> (1, 2, …), bukan teks &quot;TI&quot; atau &quot;Teknik Informatika&quot;.
        Itulah yang tersimpan di baris mahasiswa.
    </p>

    <?php if ($dataTanpaJoin === []) : ?>
        <p><strong>Belum ada data mahasiswa.</strong></p>
    <?php else : ?>
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
            <tr>
                <th>ID</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>id_prodi (angka, FK — apa adanya)</th>
                <th>Email</th>
                <th>Angkatan</th>
                <th>created_at</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($dataTanpaJoin as $baris) : ?>
                <tr>
                    <td><?= h((string) $baris['id_mahasiswa']) ?></td>
                    <td><?= h((string) $baris['nim']) ?></td>
                    <td><?= h((string) $baris['nama']) ?></td>
                    <!-- Hanya ID: untuk nama prodi perlu query ke tabel prodi atau pakai JOIN -->
                    <td><?= h((string) $baris['id_prodi']) ?></td>
                    <td><?= h((string) ($baris['email'] ?? '')) ?></td>
                    <td><?= h((string) $baris['angkatan']) ?></td>
                    <td><?= h((string) $baris['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p>Total: <strong><?= count($dataTanpaJoin) ?></strong> baris.</p>
    <?php endif; ?>

    <hr>

    <h2>2) Dengan JOIN — kode &amp; nama program studi terbaca</h2>
    <p>
        Query yang sama ke <code>mahasiswa</code>, ditambah
        <code>INNER JOIN prodi ON prodi.id_prodi = mahasiswa.id_prodi</code>.
        Baris hasil query memuat <code>kode_prodi</code> dan <code>nama_prodi</code> sehingga bisa ditampilkan seperti di aplikasi utama.
    </p>

    <?php if ($dataDenganJoin === []) : ?>
        <p><strong>Tidak ada data</strong> (atau tidak ada yang cocok dengan INNER JOIN).</p>
    <?php else : ?>
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
            <tr>
                <th>ID</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Program studi (kode — nama)</th>
                <th>Email</th>
                <th>Angkatan</th>
                <th>created_at</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($dataDenganJoin as $baris) : ?>
                <tr>
                    <td><?= h((string) $baris['id_mahasiswa']) ?></td>
                    <td><?= h((string) $baris['nim']) ?></td>
                    <td><?= h((string) $baris['nama']) ?></td>
                    <td><?= h((string) $baris['kode_prodi']) ?> — <?= h((string) $baris['nama_prodi']) ?></td>
                    <td><?= h((string) ($baris['email'] ?? '')) ?></td>
                    <td><?= h((string) $baris['angkatan']) ?></td>
                    <td><?= h((string) $baris['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p>Total: <strong><?= count($dataDenganJoin) ?></strong> baris.</p>
    <?php endif; ?>

</body>
</html>
