<?php
/**
 * =============================================================================
 * PANEL TERLINDUNGI — contoh halaman yang hanya untuk pengguna sudah login
 * =============================================================================
 * Memanggil wajib_sudah_login(): jika tidak ada sesi valid, pengunjung diarahkan
 * ke login.php?status=login_perlu
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/fungsi.php';
require_once __DIR__ . '/includes/auth.php';

wajib_sudah_login();

$pengguna = ambil_pengguna_dari_sesi();
if ($pengguna === null) {
    header('Location: login.php?status=login_perlu');
    exit;
}

$judulHalaman = 'Panel aman (studi kasus)';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 card-title">Selamat datang, <?= h($pengguna['nama_tampilan']) ?></h2>
                <p class="card-text text-secondary mb-2">
                    Anda masuk sebagai <span class="badge text-bg-primary"><?= h($pengguna['peran']) ?></span>
                    dengan username <code><?= h($pengguna['username']) ?></code>.
                </p>
                <p class="card-text small text-secondary mb-0">
                    Di aplikasi nyata, halaman seperti ini dipakai untuk menu admin, laporan, atau aksi yang
                    hanya boleh dilakukan pengguna terverifikasi. Modul CRUD akademik di menu atas tetap
                    terbuka untuk praktikum; studi kasus login menunjukkan pola <strong>gatekeeping</strong>
                    berbasis sesi.
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-primary border-2 shadow-sm">
            <div class="card-body">
                <h2 class="h6 card-title text-primary">Langkah lanjut (eksplorasi)</h2>
                <ul class="small text-secondary ps-3 mb-0">
                    <li>Tambah kolom <code>last_login</code> di tabel pengguna.</li>
                    <li>Batasi percobaan login (rate limit) sederhana lewat sesi.</li>
                    <li>Pisahkan route admin vs mahasiswa lewat kolom <code>peran</code>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<p class="mt-4 mb-0">
    <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    <a href="index.php" class="btn btn-outline-secondary btn-sm ms-2">Beranda</a>
</p>

<?php
require_once __DIR__ . '/includes/footer.php';
